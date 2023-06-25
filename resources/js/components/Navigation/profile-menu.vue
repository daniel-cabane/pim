<template>
    <v-menu :close-on-content-click="false">
        <template v-slot:activator="{ props }">
        <v-btn append-icon="mdi-menu-down" v-bind="props">
            {{ user.name }}
        </v-btn>
        </template>
        <v-list>
            <profile-dialog :user="user"/>
            <v-divider/>
            <theme-and-language-picker/>
            <v-divider/>
            <v-list-item @click="logout">
                <template v-slot:prepend>
                    <v-icon icon="mdi-logout"></v-icon>
                </template>
                <v-list-item-title>{{ $t("Sign out") }}</v-list-item-title>
            </v-list-item>
        </v-list>
    </v-menu>
</template>
<script setup>
    import { useAuthStore } from '@/stores/useAuthStore';
    import { storeToRefs } from 'pinia';

    const authStore = useAuthStore();
    const { logout } = authStore;
    const { user } = storeToRefs(authStore);
</script>