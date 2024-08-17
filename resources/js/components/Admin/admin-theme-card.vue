<template>
    <v-card width="300" class="pa-3">
        <v-text-field variant="outlined" label="English" v-model="theme.title_en"/>
        <v-text-field variant="outlined" label="Français" v-model="theme.title_fr"/>
        <div class="px-2">
            <v-switch hide-details density="compact" :label="`${$t('For workshops')} (${theme.stats.workshops})`" color="primary" v-model="theme.forWorkshop"/>
            <v-switch hide-details density="compact" :label="`${$t('For posts')} (${theme.stats.posts})`" color="primary" v-model="theme.forPost"/>
        </div>
        <div class="d-flex justify-space-between align-center">
            <v-btn variant="text" icon="mdi-delete" color="error" @click="emit('deleteTheme', theme)" :disabled="theme.stats.workshops > 0 || theme.stats.posts > 0"/>
            <v-btn :disabled="isLoading" color="primary" @click="emit('updateTheme', theme)">
                {{ $t('Save') }}
            </v-btn>
        </div>
    </v-card>
</template>
<script setup>
    const props = defineProps({ theme: Object, isLoading: Boolean });
    const emit = defineEmits(['updateTheme', 'deleteTheme']);
</script>