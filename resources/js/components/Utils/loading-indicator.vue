<template>
    <v-progress-linear color="grey-lighten-1" indeterminate class='ma-0' v-if="loadingStore.isLoading"></v-progress-linear>
</template>
<script setup>
    import { useLoadingStore } from '@/stores/useLoadingStore';

    const loadingStore = useLoadingStore();
</script>