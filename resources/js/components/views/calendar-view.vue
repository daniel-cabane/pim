<template>
    <v-container>
        <div class="d-flex justify-space-between align-center">
            <span class="d-flex align-center" style="gap:10px;">
                <v-btn variant="outlined" @click="displayToday">
                    {{ $t('Today') }}
                </v-btn>
                <v-btn icon="mdi-chevron-left" flat @click="previous" :disabled="noBackward" />
                <v-btn icon="mdi-chevron-right" flat @click="next" :disabled="noForward" />
                <span class="text-h5" v-if="viewing == 'month'">
                    {{ $t(displayMonth.name) }} {{ displayMonth.year }}
                </span>
                <span class="text-h5" v-if="viewing == 'week'">
                    <span v-if="displayWeek.monthDisplay.over == 'month'">
                        {{ $t(displayWeek.monthDisplay.name1) }} / {{ $t(displayWeek.monthDisplay.name2) }} {{
                        displayWeek.monthDisplay.year }}
                    </span>
                    <span v-else-if="displayWeek.monthDisplay.over == 'year'">
                        {{ $t(displayWeek.monthDisplay.name1) }} {{ displayWeek.monthDisplay.year1 }} / {{
                        $t(displayWeek.monthDisplay.name2) }} {{ displayWeek.monthDisplay.year2 }}
                    </span>
                    <span v-else>
                        {{ $t(displayWeek.monthDisplay.name) }} {{ displayWeek.monthDisplay.year }}
                    </span>
                </span>
                <v-btn variant="outlined" class="ml-3" @click="backToMonthView" v-if="viewing == 'week'">
                    {{ $t('Month view') }}
                </v-btn>
            </span>
            <span class="d-flex align-center">
                <v-progress-circular indeterminate :width="2" v-if="isLoading" />
            </span>
        </div>
        <div class="mb-16" v-if="isReady">
            <calendar-month :weeks="displayMonth.weeks" :currentMonth="currentMonthNb" @seeWeek="seeWeek"
                v-if="viewing == 'month'" />
            <calendar-week :week="displayWeek" v-else />
        </div>
        <div v-else>
            <calendar-squeleton />
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
    
    const viewing = ref('month');
    const currentWeekNb = ref(null);
    const displayWeek = computed(() => {
        if (currentWeekNb.value){
            return weeks.value.find(w => w.nb == currentWeekNb.value && w.year == currentYear.value);
        }
        return null;
    });
    const seeWeek = week => {
        currentWeekNb.value = week.nb;
        viewing.value = 'week';
    }
    const backToMonthView = () => {
        viewing.value = 'month'   
    }
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
        if(viewing.value == 'month'){
            if(currentMonthNb.value == 1){
                currentMonthNb.value = 12;
                currentYear.value--;
            } else {
                currentMonthNb.value--;
            }
        } else {
            if (currentWeekNb.value == 1){
                currentWeekNb.value = 52;
                currentYear.value--;
            } else {
                currentWeekNb.value--;
            }
            currentMonthNb.value = parseInt(displayWeek.value.monthNb);
        }
        if(noBackward.value){
            const nb = currentMonthNb.value;
            const newMonthNb = nb == 1 ? 12 : nb - 1;
            const newYearNb = nb == 1 ? currentYear.value - 1 : currentYear.value;
            getMoredMonths(-2, `${newYearNb}-${newMonthNb}-01`);
        }
    }
    const next = () => {
        if (viewing.value == 'month') {
            if (currentMonthNb.value == 12) {
                currentMonthNb.value = 1;
                currentYear.value++;
            } else {
                currentMonthNb.value++;
            }
        } else {
            if (currentWeekNb.value == 52) {
                currentWeekNb.value = 1;
                currentYear.value++;
            } else {
                currentWeekNb.value++;
            }
            currentMonthNb.value = parseInt(displayWeek.value.monthNb);
        }
        if (noForward.value) {
            const nb = currentMonthNb.value;
            const newMonthNb = nb == 12 ? 1 : nb + 1;
            const newYearNb = nb == 12 ? currentYear.value + 1 : currentYear.value;
            getMoredMonths(2, `${newYearNb}-${newMonthNb}-01`);
        }
    }

    const displayToday = () => {
        const currentDate = new Date();
        currentMonthNb.value = currentDate.getMonth() + 1;
        currentYear.value = currentDate.getFullYear();
        const dayOfWeek = currentDate.getDay() || 7;
        const firstDayOfYear = new Date(currentYear.value, 0, 1);
        const dayOfYear = Math.floor((currentDate - firstDayOfYear) / 86400000);
        currentWeekNb.value = Math.ceil((dayOfYear + (7 - dayOfWeek)) / 7);
    }
    // console.log(weeks.value);
</script>