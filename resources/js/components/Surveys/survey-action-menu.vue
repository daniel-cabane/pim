<template>
    <v-menu v-if="survey.editable">
        <template v-slot:activator="{ props }">
            <v-btn icon="mdi-dots-vertical" variant="text" size="small" v-bind="props"></v-btn>
        </template>
        <v-list>
            <v-list-item @click="emit('surveyAction', { action: 'send', survey})" v-if="survey.status == 'draft'">
                <template v-slot:prepend>
                    <v-icon icon="mdi-send" color="success" />
                </template>
                <v-list-item-title>
                    {{ $t('Send') }}
                </v-list-item-title>
            </v-list-item>
            <v-list-item @click="emit('surveyAction', { action: 'close', survey })" v-if="survey.status == 'open'">
                <template v-slot:prepend>
                    <v-icon icon="mdi-close-box" color="error" />
                </template>
                <v-list-item-title>
                    {{ $t('Close') }}
                </v-list-item-title>
            </v-list-item>
            <v-list-item @click="emit('surveyAction', { action: 'reopen', survey })" v-if="survey.status == 'closed'">
                <template v-slot:prepend>
                    <v-icon icon="mdi-reload" color="success" />
                </template>
                <v-list-item-title>
                    {{ $t('Reopen') }}
                </v-list-item-title>
            </v-list-item>
            <v-list-item @click="emit('surveyAction', { action: 'preview', survey })">
                <template v-slot:prepend>
                    <v-icon icon="mdi-eye" color="secondary" />
                </template>
                <v-list-item-title>
                    {{ $t('Preview') }}
                </v-list-item-title>
            </v-list-item>
            <v-list-item @click="emit('surveyAction', { action: 'edit', survey })">
                <template v-slot:prepend>
                    <v-icon icon="mdi-pencil" color="primary" />
                </template>
                <v-list-item-title>
                    {{ $t('Edit') }}
                </v-list-item-title>
            </v-list-item>
            <v-list-item @click="emit('surveyAction', { action: 'delete', survey })">
                <template v-slot:prepend>
                    <v-icon icon="mdi-delete" color="error" />
                </template>
                <v-list-item-title>
                    {{ $t('Delete') }}
                </v-list-item-title>
            </v-list-item>
        </v-list>
    </v-menu>
    <v-icon icon="mdi-eye" color="primary" @click="emit('surveyAction', { action: 'preview', survey })" v-else />
</template>
<script setup>
    const props = defineProps({ survey: Object });
    const emit = defineEmits(['surveyAction'])
</script>