<template>
    <v-container>
        <div class="d-flex justify-space-between align-center">
            <span class="d-flex align-center" style="gap:10px;">
                <v-btn variant="outlined" @click="displayToday">
                    {{ $t('Today') }}
                </v-btn>
                <v-btn icon="mdi-chevron-left" flat @click="previous" :disabled="noBackward"/>
                <v-btn icon="mdi-chevron-right" flat @click="next" :disabled="noForward"/>
                <span class="text-h5" v-if="displayMonth">
                    {{ $t(displayMonth.name) }} {{ displayMonth.year }}
                </span>
            </span>
            <span class="d-flex align-center">
                <v-progress-circular indeterminate :width="2" v-if="isLoading"/>
            </span>
        </div>
        <div v-if="isReady">
            <calendar-month :weeks="displayMonth.weeks" :currentMonth="currentMonthNb" />
        </div>
        <div v-else>
            <calendar-squeleton/>
        </div>
    </v-container>
</template>
<script setup>
    import { ref, computed } from "vue";
    import { useEventStore } from '@/stores/useEventStore';
    import { storeToRefs } from 'pinia';
    // import { useI18n } from 'vue-i18n';

    // const { t } = useI18n();

    const eventStore = useEventStore();
    const { getCurrentMonth, getMoredMonths } = eventStore;
    const { weeks, months, isReady, isLoading } = storeToRefs(eventStore);
    getCurrentMonth();

    const currentDate = new Date();
    const currentMonthNb = ref(currentDate.getMonth() + 1);
    const currentYear = ref(currentDate.getFullYear());
    const displayMonth = computed(() => {
        const month = months.value.find(m => m.nb == currentMonthNb.value && m.year == currentYear.value);
        if(month){
            const monthWeeks = [];
            month.weekRefs.forEach(week => {
                monthWeeks.push(weeks.value.find(w => w.nb == week.nb && w.year == week.year));
            });
            month.weeks = monthWeeks;
            return month;
        }
        return {name: 'Loading', year: '...', weeks: []}
    });
    const noForward = computed(() => {
        if (currentMonthNb.value == 12){
            return !months.value.some(m => m.nb == 1 && m.year == currentYear.value + 1)
        }
        return !months.value.some(m => m.nb == currentMonthNb.value + 1 && m.year == currentYear.value);
    });
    const noBackward = computed(() => {
        if(currentMonthNb.value == 1){
            return !months.value.some(m => m.nb == 12 && m.year == currentYear.value - 1)
        }
        return !months.value.some(m => m.nb == currentMonthNb.value - 1 && m.year == currentYear.value);
    });

    const previous = () => {
        if(currentMonthNb.value == 1){
            currentMonthNb.value = 12;
            currentYear.value--;
        } else {
            currentMonthNb.value--;
        }
        if(noBackward.value){
            const newMonthNb = currentMonthNb.value == 1 ? 12 : currentMonthNb.value - 1;
            const newYearNb = currentMonthNb.value == 1 ? currentYear.value - 1 : currentYear.value;
            getMoredMonths(-2, `${newYearNb}-${newMonthNb}-01`);
        }
    }
    const next = () => {
        if (currentMonthNb.value == 12) {
            currentMonthNb.value = 1;
            currentYear.value++;
        } else {
            currentMonthNb.value++;
        }
        if (noForward.value) {
            const newMonthNb = currentMonthNb.value == 12 ? 1 : currentMonthNb.value + 1;
            const newYearNb = currentMonthNb.value == 12 ? currentYear.value + 1 : currentYear.value;
            getMoredMonths(2, `${newYearNb}-${newMonthNb}-01`);
        }
    }

    const displayToday = () => {
        const currentDate = new Date();
        currentMonthNb.value = currentDate.getMonth() + 1;
        currentYear.value = currentDate.getFullYear();
    }
</script>