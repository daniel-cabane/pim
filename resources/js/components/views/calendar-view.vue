<template>
    <v-container v-if="isReady">
        <div class="d-flex justify-space-between align-center">
            <span class="d-flex align-center" style="gap:10px;">
                <v-btn variant="outlined">
                    {{ $t('Today') }}
                </v-btn>
                <v-btn icon="mdi-chevron-left" flat @click="previous" :disabled="noBackward"/>
                <v-btn icon="mdi-chevron-right" flat @click="next" :disabled="noForward"/>
                <span class="text-h5" v-if="displayMonth">
                    {{ $t(displayMonth.name) }} {{ displayMonth.year }}
                </span>
            </span>
            <span class="d-flex align-center">
                <!-- <span class="text-grey">
                    {{ $t('Week view') }}
                </span>
                <v-switch hide-details class="mx-2" />
                <span class="text-grey">
                    {{ $t('Month view') }}
                </span> -->
                <v-progress-circular indeterminate :width="2" v-if="isLoading"/>
            </span>
        </div>
        <div>
            <calendar-month :weeks="displayMonth.weeks" :currentMonth="currentMonthNb" />
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
            month.weekNbs.forEach(nb => {
                monthWeeks.push(weeks.value.find(w => w.nb == nb));
            });
            month.weeks = monthWeeks;
        }

        return month;
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
            // const firstMonth = months.value.reduce((prev, curr) => {
            //     return prev.year < curr.year || prev.nb < curr.nb ? prev : curr;
            // });
            getMoredMonths(-2, `${currentYear.value}-${(currentMonthNb.value+11)%12}-01`);
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
            getMoredMonths(2);
        }
    }
</script>