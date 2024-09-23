<template>
    <v-container>
        <div class="fullscreen">
            <div style="position:absolute;top:0px;left:0px;">
                <v-icon icon="mdi-circle" v-for="led in leds" :color="led ? 'success' : 'surface'"/>
            </div>
            <div v-if="message.length">
                Message : {{ message }}
            </div>
            <div v-if="serialNumber.length">
                Serial : {{ serialNumber }}
            </div>
            <v-btn style="width:200px" color="primary" :loading="loading" @click="scanTag">
                Scan
            </v-btn>
            <div v-if="error.length">
                Error : {{ error }}
            </div>
        </div>
    </v-container>
</template>
<script setup>
    import { ref, reactive } from "vue";

    const loading = ref(false);
    const message = ref('');
    const serialNumber = ref('');
    const error = ref('');

    let leds = reactive([false, false, false, false]);


    const scanTag = async () => {
        leds = [false, false, false, false];
        leds[0] = true;
        loading.value = true;
        try {
            const ndef = new NDEFReader();
            leds[1] = true;
            await ndef.scan();

            ndef.addEventListener("readingerror", () => {
                error.value = "Tag unreadable for some reason...";
            });

            ndef.addEventListener("reading", ({ msg, serial }) => {
                message.value = msg;
                serialNumber.value = serial;
                leds[2] = true;
            });
            loading.value = false;
        } catch (err) {
            leds[3] = true;
            console.error(err);
            error.value = err;
            loading.value = false;
        }
    }
</script>
<style scoped>
    .fullscreen {
        height: calc(100vh - 128px);
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
    }
</style>