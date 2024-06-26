<template>
    <v-container>
        <v-select v-model="type" :items="types" class="ma-2" label="View Mode" variant="outlined" dense hide-details />
        <v-calendar ref="calendar" v-model="value" :events="events" :view-mode="type" :weekdays="[1, 2, 3, 4, 5, 6, 0]"
            show-adjacent-months :interval-start="6" :intervals="14"/>
    </v-container>
</template>
<script setup>
    import { ref } from "vue";
    // import { useI18n } from 'vue-i18n';
    // const { t } = useI18n();

    const events = [];
    const types = ['month', 'week', 'day'];
    const type = ref('month');
    let value = [new Date()];
    
</script>