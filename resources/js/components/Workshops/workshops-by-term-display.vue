<template>
    <v-container>
        <div class="text-grey text-h5">
            {{$t('Upcoming')}}
        </div>
        <div class="d-flex flex-wrap" v-if="confirmed.length">
            <workshop-card class="ma-2" hover v-for="workshop in confirmed" :workshop="workshop" :key="workshop.id" />
        </div>
        <div v-else class="d-flex justify-center align-end text-grey-lighten-2 text-h6">
            Nothing at the moment
            <v-img max-width='50px' min-width='50px' class='ml-3' src="/images/sad robot.png"/>
        </div>
        <div class="text-grey text-h5 mt-4" v-if="progress.length">
            {{$t('In progress')}}
        </div>
        <div class="d-flex flex-wrap">
            <workshop-card class="ma-2" hover v-for="workshop in progress" :workshop="workshop" :key="workshop.id" />
        </div>
        <div class="text-grey text-h5 mt-4" v-if="finished.length">
            {{$t('Finished')}}
        </div>
        <div class="d-flex flex-wrap">
            <workshop-card class="ma-2" hover v-for="workshop in finished" :workshop="workshop" :key="workshop.id" />
        </div>
    </v-container>
</template>
<script setup>
    import { computed } from "vue";

    const props = defineProps({ workshops: Object, filters: Object });

    const confirmed = computed(() => {
        const campus = Object.keys(props.filters).filter(key => props.filters[key]);
        return props.workshops.confirmed ? props.workshops.confirmed.filter(w => campus.includes(w.campus)) : [];
    });
    const progress = computed(() => {
        const campus = Object.keys(props.filters).filter(key => props.filters[key]);
        return props.workshops.progress ? props.workshops.progress.filter(w => campus.includes(w.campus)) : [];
    });
    const finished = computed(() => {
        const campus = Object.keys(props.filters).filter(key => props.filters[key]);
        return props.workshops.finished ? props.workshops.finished.filter(w => campus.includes(w.campus)) : [];
    });
</script>