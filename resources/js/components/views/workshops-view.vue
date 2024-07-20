<template>
    <v-container v-if="isReady">
        <v-tabs v-model="tab" color="primary" class="mb-2 align-center">
            <v-tab prepend-icon="mdi-calendar-alert-outline" :text="$t('Upcoming')" value="upcoming"></v-tab>
            <v-tab prepend-icon="mdi-puzzle" :text="$t('My workshops')" value="enrollements"
                v-if="user && user.is.student"></v-tab>
            <v-tab prepend-icon="mdi-calendar-clock-outline" :text="$t('Past workshops')" value="past"></v-tab>
            <!-- <v-spacer /> -->
             <span class="ml-8 mt-2">
                 <v-chip :variant="filters.TKO ? 'flat' : 'tonal'" :color="filters.TKO ? '#FF0000' : 'secondary'" class="mr-1"
                     @click="toggleTKO">
                     TKO
                 </v-chip>
                 <v-chip :variant="filters.BPR ? 'flat' : 'tonal'" :color="filters.BPR ? '#0017FF' : 'secondary'"
                     @click="toggleBPR">
                     BPR
                 </v-chip>
             </span>
        </v-tabs>

        <v-tabs-window v-model="tab">
            <v-tabs-window-item value="upcoming">
                <div class="d-flex flex-wrap">
                    <workshop-card class="ma-2" hover v-for="workshop in upcoming" :workshop="workshop"
                        :key="workshop.id" />
                </div>
            </v-tabs-window-item>
            <v-tabs-window-item value="enrollements">
                <div class="d-flex flex-wrap">
                    <workshop-card class="ma-2" hover v-for="workshop in enrollements" :workshop="workshop"
                        :key="workshop.id" />
                </div>
            </v-tabs-window-item>
            <v-tabs-window-item value="past">
                <div class="d-flex flex-wrap">
                    <workshop-card class="ma-2" hover v-for="workshop in past" :workshop="workshop"
                        :key="workshop.id" />
                </div>
            </v-tabs-window-item>
        </v-tabs-window>
    </v-container>
</template>
<script setup>
    import { ref, reactive, computed } from "vue";
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    import { useAuthStore } from '@/stores/useAuthStore';
    import { storeToRefs } from 'pinia';

    const workshopStore = useWorkshopStore();
    const { getWorkshops } = workshopStore;
    const { workshops, isReady } = storeToRefs(workshopStore);

    const { user } = storeToRefs(useAuthStore());


    getWorkshops();

    const tab = ref('upcoming');

    const filters = reactive({ BPR: true, TKO: true });
    const toggleBPR = () => {
        filters.BPR = !filters.BPR;
    }
    const toggleTKO = () => {
        filters.TKO = !filters.TKO;
    }
    const upcoming = computed(() => {
        const campus = Object.keys(filters).filter(key => filters[key]);
        return workshops.value.upcoming.filter(w => campus.includes(w.campus));
    });
    const enrollements = computed(() => {
        const campus = Object.keys(filters).filter(key => filters[key]);
        return workshops.value.enrollements.filter(w => campus.includes(w.campus));
    });
    const past = computed(() => {
        const campus = Object.keys(filters).filter(key => filters[key]);
        return workshops.value.past.filter(w => campus.includes(w.campus));
    });
</script>