<template>
    <v-container>
        <workshop-card 
            class="mb-2"
            hover
            v-for="workshop in myWorkshops.upcoming" 
            :workshop="workshop"
            :key="workshop.id" 
        />
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