<template>
    <v-container>
        <div class="fullscreen">
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
    import { ref } from "vue";

    const loading = ref(false);
    const message = ref('');
    const serialNumber = ref('');
    const error = ref('');


    const scanTag = async () => {
        console.log("Scanning");
        loading.value = true;
        try {
            const ndef = new NDEFReader();
            await ndef.scan();

            ndef.addEventListener("readingerror", () => {
                error.value = "Tag unreadable for some reason...";
            });

            ndef.addEventListener("reading", ({ msg, serial }) => {
                message.value = msg;
                serialNumber.value = serial;
            });
            loading.value = false;
        } catch (err) {
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
    }
</style>