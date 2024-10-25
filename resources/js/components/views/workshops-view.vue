<template>
    <v-container v-if="isReady">
        <v-tabs v-model="tab" color="primary" class="mb-2 align-center">
            <v-tab v-for="i in 3" :value="`term-${i}`">
                {{ $t('Term') }} {{ i }}
            </v-tab>
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
            <v-tabs-window-item v-for="i in 3" :value="`term-${i}`">
                <workshops-by-term-display :workshops="workshopsByTerm[i-1]" :filters="filters"/>
            </v-tabs-window-item>
        </v-tabs-window>
    </v-container>
</template>
<script setup>
    import { ref, reactive, watch } from "vue";
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    import { storeToRefs } from 'pinia';

    const workshopStore = useWorkshopStore();
    const { getWorkshops } = workshopStore;
    const { isReady, workshopsByTerm, currentTerm } = storeToRefs(workshopStore);

    getWorkshops();

    const tab = ref('term-1');
    watch(currentTerm, val => tab.value = `term-${val}`);

    const filters = reactive({ BPR: true, TKO: true });
    const toggleBPR = () => {
        filters.BPR = !filters.BPR;
    }
    const toggleTKO = () => {
        filters.TKO = !filters.TKO;
    }
    // const upcoming = computed(() => {
    //     const campus = Object.keys(filters).filter(key => filters[key]);
    //     return workshops.value.upcoming.filter(w => campus.includes(w.campus));
    // });
    // const enrollements = computed(() => {
    //     const campus = Object.keys(filters).filter(key => filters[key]);
    //     return workshops.value.enrollements.filter(w => campus.includes(w.campus));
    // });
    // const past = computed(() => {
    //     const campus = Object.keys(filters).filter(key => filters[key]);
    //     return workshops.value.past.filter(w => campus.includes(w.campus));
    // });
</script>