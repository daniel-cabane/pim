<template>
    <v-card class="pa-3">
        <admin-message-card class="ma-2" v-for="message in messages" :message="message"/>
    </v-card>
</template>
<script setup>
    import { useAuthStore } from '@/stores/useAuthStore';
    import { storeToRefs } from 'pinia';

    const authStore = useAuthStore();
    const { getMessages } = authStore;
    const { messages, loading } = storeToRefs(authStore);
    getMessages();
</script>