<template>
    <v-container>
        <div v-if="isReady">
            {{ workshop.title }}
        </div>
    </v-container>
</template>
<script setup>
    import { useRoute } from 'vue-router';
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    import { storeToRefs } from 'pinia';
import { isReactive } from 'vue';

    const route = useRoute();

    const workshopStore = useWorkshopStore();
    const { getWorkshop } = workshopStore;
    const { workshop, isReady } = storeToRefs(workshopStore);

    getWorkshop(route.params.id);
</script>