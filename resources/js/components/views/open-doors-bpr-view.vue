<template>
    <v-container>
        <div class="fullscreen">
            <div style="position:absolute;top:0px;left:0px;">
                <v-icon icon="mdi-circle" v-for="led in leds" :color="led ? 'success' : 'surface'"/>
                <v-icon icon="mdi-circle" :color="errorLed ? 'error' : 'surface'"/>
            </div>
            <v-window v-model="window" direction="vertical">
                <v-window-item>
                    <v-card width="450" title="Please regsiter" subtitle="Unregistered card number" elevation="16">
                        <v-card-text class="pb-0">
                            <v-text-field label="Email" variant="outlined" suffix="@g.lfis.edu.hk" v-model="visitor.email"/>
                        </v-card-text>
                        <v-card-actions>
                            <v-btn variant="tonal" color="error" @click="window=1">
                                {{ $t('Skip') }}
                            </v-btn>
                            <v-spacer/>
                            <v-btn variant="elevated" color="primary" @click="handleRegister">
                                {{ $t('Submit') }}
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-window-item>
                <v-window-item>
                    <div>
                        {{ tag16 }}
                    </div>
                    <div>
                        {{ tag10 }}
                    </div>
                    <v-btn style="width:200px" color="primary" :loading="isLoading" @click="scanTag" v-if="window==1">
                        Scan
                    </v-btn>
                </v-window-item>
                <v-window-item>
                    <v-alert :text="$t('Enjoy your time in this room')" :title="`${$t('Welcome')} ${visitor.name}`" type="success"/>
                </v-window-item>
            </v-window>
        </div>
    </v-container>
</template>
<script setup>
    import { ref } from "vue";
    import { useOpenDoorStore } from '@/stores/useOpenDoorStore';
    import { storeToRefs } from 'pinia';

    const openDoorStore = useOpenDoorStore();
    const { visit, register } = openDoorStore;
    const { visitor, isLoading } = storeToRefs(openDoorStore);

    const window = ref(1);

    const tag16 = ref('');
    const tag10 = ref('');

    const leds = ref([false, false, false]);
    const errorLed = ref(false);

    const scanTag = async () => {
        leds.value = [false, false, false];
        leds.value[0] = true;
        // leds.value[1] = true;
        // const device = await navigator.usb.requestDevice({ filters: [] });
        // console.log(device);
        // await visit(337713548);
        // if(visitor.value.name){
        //     window.value = 2;
        //     setTimeout(() => window.value = 1, 2000)
        // } else {
        //     window.value = 0;
        // }

        try {
            const ndef = new NDEFReader();
            leds[1] = true;
            await ndef.scan();

            ndef.addEventListener("readingerror", () => {
                error.value = "Tag unreadable for some reason...";
            });

            ndef.addEventListener("reading", async ({ message, serialNumber }) => {
                const tagId = parseInt(serialNumber.split(":").reverse().join(""), 16);
                tag16.value = serialNumber;
                tag10.value = tagId;
                await visit(tagId);
                if(visitor.value.name){
                    window.value = 2;
                    setTimeout(() => window.value = 1, 2000)
                } else {
                    window.value = 0;
                }
                // axios.post('/api/pobpr', { message, serialNumber, id: tagId.value });
                leds[2] = true;
            });
        } catch (err) {
            errorLed.value = true;
            console.error(err);
            error.value = err;
            loading.value = false;
        }
    }

    const handleRegister = () => {
        register();
        window.value = 1;
    }
</script>
<style scoped>
    .fullscreen {
        height: calc(100vh - 128px);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        position: relative;
    }
</style>