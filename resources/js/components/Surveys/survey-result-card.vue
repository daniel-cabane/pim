<template>
    <v-card style="position:relative;">
        <v-toolbar color="secondary">
            <v-toolbar-title>Results - {{ survey.mainTitle }}</v-toolbar-title>
            <v-spacer/>
            <v-toolbar-items>
                <v-btn icon="mdi-close" dark @click="emit('closeDialog')"/>
            </v-toolbar-items>
        </v-toolbar>
        <v-progress-linear color="primary" style="position:absolute;top:60px;" indeterminate v-if="isLoading"/>
        <v-card-text>
            Coming soon...
        </v-card-text>
    </v-card>
</template>
<script setup>
    const emit = defineEmits(['closeDialog']);
    const props = defineProps({ survey: Object, isLoading: Boolean });
</script>