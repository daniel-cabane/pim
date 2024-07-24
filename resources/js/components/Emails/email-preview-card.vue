<template>
    <v-card :title="$t('Preview email')" :loading="isLoading">
        <v-card-text>
            <div v-html="preview"/>
        </v-card-text>
        <v-card-actions>
            <v-spacer />
            <v-btn variant="text" color="primary" @click="emit('closeDialog')">
                {{ $t('Close') }}
            </v-btn>
        </v-card-actions>
    </v-card>
</template>
<script setup>
    import { useEmailStore } from '@/stores/useEmailStore';
    import { storeToRefs } from 'pinia';

    const props = defineProps({ email: Object });
    const emit = defineEmits(['closeDialog']);

    const emailStore = useEmailStore();
    const { previewMail } = emailStore;
    const { preview, isLoading } = storeToRefs(emailStore);

    previewMail(props.email.id);
</script>