<template>
    <v-container>
        <div class="fullscreen">
            <div style="position:absolute;top:0px;left:0px;">
                <v-icon icon="mdi-circle" v-for="led in leds" :color="led ? 'success' : 'surface'"/>
                <v-icon icon="mdi-circle" :color="errorLed ? 'error' : 'surface'"/>
            </div>
            <!-- <pre v-for="record in tagRecords">
               {{ record }}
            </pre> -->
            <div>
                {{ strRecord }}
            </div>
            <v-btn style="width:200px" color="primary" :loading="loading" @click="scanTag">
                Scan
            </v-btn>
            <pre>
                {{ error }}
            </pre>
        </div>
    </v-container>
</template>
<script setup>
    import { ref, reactive } from "vue";

    const loading = ref(false);
    // const tagRecords = ref('');
    const strRecord = ref('');
    const errorLed = ref(false);
    const error = ref('');

    let leds = reactive([false, false, false]);


    const scanTag = async () => {
        leds = [false, false, false];
        leds[0] = true;
        loading.value = true;

        try {
            const ndef = new NDEFReader();
            leds[1] = true;
            await ndef.scan();

            ndef.addEventListener("readingerror", () => {
                error.value = "Tag unreadable for some reason...";
            });

            ndef.addEventListener("reading", ({ message, serialNumber }) => {
                axios.post('/api/pobpr', { message, serialNumber, records: message.records });
                leds[2] = true;
            });
            loading.value = false;
        } catch (err) {
            errorLed.value = true;
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
        flex-direction: column;
        justify-content: center;
        align-items: center;
        position: relative;
    }
</style>