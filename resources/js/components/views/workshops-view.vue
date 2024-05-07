<template>
    <v-container v-if="isReady">
        <workshop-card 
            class="mb-2"
            hover
            v-for="workshop in workshops" 
            :workshop="workshop"
            :key="workshop.id" 
        />
    </v-container>
</template>
<script setup>
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    import { storeToRefs } from 'pinia';

    const workshopStore = useWorkshopStore();
    const { getWorkshops } = workshopStore;
    const { workshops, isReady } = storeToRefs(workshopStore);

    getWorkshops();
</script>