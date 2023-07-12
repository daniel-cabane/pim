<template>
    <v-container>
        <div>
            {{ workshop.title }}
        </div>
    </v-container>
</template>
<script setup>
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    import { storeToRefs } from 'pinia';
    import { useRoute } from 'vue-router';

    const workshopStore = useWorkshopStore();
    const { getSingleWorkshops } = workshopStore;
    const { workshop } = storeToRefs(workshopStore);

    const route = useRoute();
    getSingleWorkshops(route.params.id);
</script>