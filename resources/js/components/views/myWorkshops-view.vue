<template>
    <v-container>
        <div class="text-h5 font-weight-thin text-grey mb-3">
            {{$t("Upcoming")}}
        </div>
        <div class="d-flex flex-wrap">
            <workshop-card class="ma-2" hover v-for="workshop in myWorkshops.upcoming" :workshop="workshop"
                :key="workshop.id" />
        </div>
        <v-divider class="my-5"></v-divider>
        <div class="text-h5 font-weight-thin text-grey mb-3">
            {{ $t("Past and current") }}
        </div>
        <div>
            <workshop-card class="mb-2" hover v-for="workshop in myWorkshops.pastAndCurrent" :workshop="workshop"
                :key="workshop.id" />
        </div>
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