<template>
    <v-container v-if="isReady">
        <v-tabs v-model="tab" color="primary" class="mb-2">
            <v-tab prepend-icon="mdi-calendar-alert-outline" :text="$t('Upcoming')" value="upcoming"></v-tab>
            <v-tab prepend-icon="mdi-puzzle" :text="$t('My workshops')" value="enrollements" v-if="user && user.is.student"></v-tab>
            <v-tab prepend-icon="mdi-calendar-clock-outline" :text="$t('Past workshops')" value="past"></v-tab>
        </v-tabs>

        <v-tabs-window v-model="tab">
            <v-tabs-window-item value="upcoming">
                <div class="d-flex flex-wrap">
                    <workshop-card class="ma-2" hover v-for="workshop in workshops.upcoming" :workshop="workshop"
                        :key="workshop.id" />
                </div>
            </v-tabs-window-item>
            <v-tabs-window-item value="enrollements">
                <div class="d-flex flex-wrap">
                    <workshop-card class="ma-2" hover v-for="workshop in workshops.enrollements" :workshop="workshop"
                        :key="workshop.id" />
                </div>
            </v-tabs-window-item>
            <v-tabs-window-item value="past">
                <div class="d-flex flex-wrap">
                    <workshop-card class="ma-2" hover v-for="workshop in workshops.past" :workshop="workshop"
                        :key="workshop.id" />
                </div>
            </v-tabs-window-item>
        </v-tabs-window>
    </v-container>
</template>
<script setup>
    import { ref } from "vue";
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    import { useAuthStore } from '@/stores/useAuthStore';
    import { storeToRefs } from 'pinia';

    const workshopStore = useWorkshopStore();
    const { getWorkshops } = workshopStore;
    const { workshops, isReady } = storeToRefs(workshopStore);

    const { user } = storeToRefs(useAuthStore());


    getWorkshops();

    const tab = ref('upcoming');
</script>