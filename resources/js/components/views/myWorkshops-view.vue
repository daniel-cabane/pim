<template>
    <v-container>
        <div>Workshops !</div>
        <div v-for="workshop in myWorkshops" :key="workshop.id">{{ workshop.title }}</div>
    </v-container>
</template>
<script setup>
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    import { storeToRefs } from 'pinia';

    const workshopStore = useWorkshopStore();
    const { getMyWorkshops } = workshopStore;
    const { myWorkshops } = storeToRefs(workshopStore);

    getMyWorkshops();
</script>