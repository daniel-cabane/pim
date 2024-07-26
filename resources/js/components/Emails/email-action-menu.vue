<template>
    <v-menu v-if="email.editable && !email.sent">
        <template v-slot:activator="{ props }">
            <v-btn icon="mdi-dots-vertical" variant="text" size="small" v-bind="props"></v-btn>
        </template>
        <v-list>
            <v-list-item @click="emit('emailAction', { action: 'schedule', email })" v-if="!email.sent">
                <template v-slot:prepend>
                    <v-icon icon="mdi-send-clock" color="success" />
                </template>
                <v-list-item-title>
                    {{ $t('Schedule|mail') }}
                </v-list-item-title>
            </v-list-item>
            <v-list-item @click="emit('emailAction', { action: 'preview', email })">
                <template v-slot:prepend>
                    <v-icon icon="mdi-eye" color="secondary" />
                </template>
                <v-list-item-title>
                    {{ $t('Preview') }}
                </v-list-item-title>
            </v-list-item>
            <v-list-item @click="emit('emailAction', { action: 'edit', email })">
                <template v-slot:prepend>
                    <v-icon icon="mdi-pencil" color="primary" />
                </template>
                <v-list-item-title>
                    {{ $t('Edit') }}
                </v-list-item-title>
            </v-list-item>
            <v-list-item @click="emit('emailAction', { action: 'delete', email })">
                <template v-slot:prepend>
                    <v-icon icon="mdi-delete" color="error" />
                </template>
                <v-list-item-title>
                    {{ $t('Delete') }}
                </v-list-item-title>
            </v-list-item>
        </v-list>
    </v-menu>
    <v-icon icon="mdi-eye" :color="email.sent ? 'success' : 'secondary'" @click="emit('emailAction', { action: 'preview', email })" v-else />
</template>
<script setup>
    const props = defineProps({ email: Object });
    const emit = defineEmits(['emailAction']);
</script>