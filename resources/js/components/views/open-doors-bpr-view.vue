<template>
    <v-container>
        <div class="fullscreen">
            <div style="position:absolute;top:0px;left:0px;">
                <!-- <v-icon icon="mdi-circle" v-for="led in leds" :color="led ? 'success' : 'surface'"/>
                <v-icon icon="mdi-circle" :color="errorLed ? 'error' : 'surface'"/> -->
                <v-icon icon="mdi-circle" :color="ledColor"/>
            </div>
            <v-window class="pa-3 pt-15 mt-10" v-model="window" direction="vertical">
                <v-window-item>
                    <v-card class="elevation-0" style="margin:auto" width="350" max-width="95%" :title="$t('Please regsiter')" :subtitle="$t('Unregistered card number')" elevation="16">
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
                    <div v-if="reading">
                        <div class="text-h5 d-flex align-center">
                            <v-spacer/>
                            <div class="d-flex flex-column align-center" style="white-space:nowrap;">
                                {{ $t('Scan badge behind') }}
                            </div>
                            <div class="arrowed" style="margin-left:-15px;margin-right:-10px;">
                                <div class="arrow" :class="theme.global.name.value == 'customDark' ? '' : 'inverted'"/>
                            </div>
                        </div>
                    </div>
                    <div class="text-h5 d-flex flex-column align-center justify-center" v-else>
                        <div>
                            {{ $t('Press Button') }}
                        </div>
                        <div class="arrowed mb-2" style="transform: rotate(90deg);">
                            <div class="arrow" :class="theme.global.name.value == 'customDark' ? '' : 'inverted'"/>
                        </div>
                        <v-btn style="width:200px" color="primary" :loading="isLoading" @click="scanTag" v-if="window==1">
                            Scan
                        </v-btn>
                    </div>
                </v-window-item>
                <v-window-item>
                    <v-alert class="mt-10" :text="$t('Enjoy your time in this room')" :title="`${$t('Welcome')} ${visitor.name}`" type="success"/>
                </v-window-item>
            </v-window>
        </div>
    </v-container>
</template>
<script setup>
    import { ref, computed } from "vue";
    import { useOpenDoorStore } from '@/stores/useOpenDoorStore';
    import { storeToRefs } from 'pinia';
    import { useTheme } from 'vuetify';

    const theme = useTheme();

    const openDoorStore = useOpenDoorStore();
    const { visit, register } = openDoorStore;
    const { visitor, isLoading } = storeToRefs(openDoorStore);

    const window = ref(1);
    const ndef = ref(null);
    const error = ref(null);
    const reading = computed(() => {
        return ndef.value != null;
    });
    const ledColor = computed(() => {
        if(error.value){
            return 'error';
        }
        if(reading.value){
            return 'success';
        }
        return 'surface';
    });


    const scanTag = async () => {
        // leds.value = [false, false, false];
        // leds.value[0] = true;
        // leds.value[1] = true;
        // // const device = await navigator.usb.requestDevice({ filters: [] });
        // // console.log(device);
        // await visit(337713548);
        // if(visitor.value.name){
        //     window.value = 2;
        //     setTimeout(init, 2000)
        // } else {
        //     window.value = 0;
        // }

        try {
            ndef.value = new NDEFReader();
            await ndef.value.scan();

            ndef.value.addEventListener("readingerror", () => {
                error.value = "Tag unreadable for some reason...";
            });

            ndef.addEventListener("reading", async ({ message, serialNumber }) => {
                const tagId = parseInt(serialNumber.split(":").reverse().join(""), 16);
                await visit(tagId);
                if(visitor.value.name){
                    window.value = 2;
                    setTimeout(init, 2000);
                } else {
                    window.value = 0;
                }
            });
        } catch (err) {
            error.value = err;
            console.error(err);
        }
    }

    const init = () => {
        window.value = 1;
    }

    const handleRegister = () => {
        register();
        init()
    }
</script>
<style scoped>
    .fullscreen {
        height: calc(100vh - 128px);
        width:100hh;
        display: flex;
        flex-direction: column;
        /* justify-content: center; */
        align-items: center;
        position: relative;
    }
    .arrowed {
        position: relative;
        height: 80px; width: 80px;
    }

    .arrowed div {
        position: absolute;
        top: 0; bottom: 0; left: 0; right: 0;
        margin: auto;
    }
    .arrow {
        height: 30px; width: 30px;
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-width: 3px 3px 0 0;
        transform: rotate(45deg);
    }

    .arrow:before, .arrow:after {
        content: '';
        position: absolute;
        display: block;
        height: 30px; width: 30px;
        border-width: 3px 3px 0 0;
    }

    .arrow:before {
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-width: 3px 3px 0 0;
        /* top - distance minus border */
        top: 7px; left: -10px;
    }

    .arrow:after {
        border: 1px solid rgba(255, 255, 255, 1);
        border-width: 3px 3px 0 0;
        /* top - distance plus border */
        top: -13px; left: 10px;
    }
    .inverted {
        filter: invert(100%);
    }
</style>