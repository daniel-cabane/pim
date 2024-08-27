<template>
    <v-dialog v-model="dialog" width="450">
        <template v-slot:activator="{ props }">
            <v-list-item v-bind="props">
                <template v-slot:prepend>
                    <v-icon icon="mdi-message"></v-icon>
                </template>
                <v-list-item-title>{{ $t("Contact PIM") }}</v-list-item-title>
            </v-list-item>
        </template>
        <v-card :title="$t('Contact PIM')">
            <v-card-text>
                <v-textarea label="Message" variant="outlined" v-model="message" maxlength="500" counter/>
            </v-card-text>
            <div class="d-flex pa-2">
                <v-spacer />
                <v-btn variant="text" class="mr-2" min-width="150" color="error" :disabled="loading" @click="closeDialog">
                    {{ $t('Close') }}
                </v-btn>
                <v-btn color="primary" min-width="150" :loading="loading" @click="submit">
                    {{ $t('Send') }}
                </v-btn>
            </div>
        </v-card>
    </v-dialog>
</template>
<script setup>
    import { ref } from 'vue';
    import { useAuthStore } from '@/stores/useAuthStore';
    import { storeToRefs } from 'pinia';

    const authStore = useAuthStore();
    const { msgToAdmin } = authStore;
    const { loading } = storeToRefs(authStore);

    const dialog = ref(false);
    const message = ref('');

    const submit = async () => {
        await msgToAdmin(message.value);
        message.value = '';
        dialog.value = false;
    }
    const closeDialog = () => {
        dialog.value = false;
    }
</script>