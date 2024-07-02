<template>
    <div class="d-flex text-center justify-space-between">
        <div class="dayBox" :class="{ holiday: day.isHoliday }" v-for="day in days" :key="day.date">
            <v-btn :variant="day.button.variant" class="my-1" :class="day.button.class" :color="day.button.color" icon @click="emit('seeWeek', week)">
                {{ day.date.substr(8, 2) }}
            </v-btn>
            <div v-if="day.events.length">
                <event-chip v-for="event in day.events.slice(0, 3)" :key="`${event.type}-${event.id}`" :event="event"
                    :showInfo="focusedId == event.id" :style="focusedId == event.id ? 'z-index:10' : ''"
                    @click.stop="showInfo(event.id)" @hideMe="emit('updateFocusedId', '0');" />
                <v-btn variant="text" size="x-small" block v-if="day.events.length > 3" @click="showDayDialog(day)">
                    {{ day.events.length -3 }} {{ $t('more') }}
                </v-btn>
            </div>
        </div>
        <v-dialog v-model="dayDialog" width="450px">
            <v-card :title="focusedDay.date">
                <v-card-text>
                    <v-expansion-panels>
                        <event-panel v-for="event in focusedDay.events" :key="`${event.type}-${event.id}`" class="my-1" :event="event" />
                    </v-expansion-panels>
                </v-card-text>
                <template v-slot:actions>
                    <v-btn class="ms-auto" text="CLose" @click="dayDialog = false"></v-btn>
                </template>
            </v-card>
        </v-dialog>
    </div>
</template>
<script setup>
    import { ref, computed } from "vue";

    const props = defineProps({ week: Object, currentMonth: Number, focusedId: String });

    const days = computed(() => {
        return props.week.days.map(d => ({...d, button: buttonType(d)}));
    });

    const buttonType = day => {
        const today = new Date();
        const inputDate = new Date(day.date);
        const sameDay = (
            inputDate.getDate() === today.getDate() &&
            inputDate.getMonth() === today.getMonth() &&
            inputDate.getFullYear() === today.getFullYear()
        );
        if(sameDay){
            return { variant: 'outlined', color: 'red', class: 'font-weight-bold' }
        }

        const currentMonth = day.date.substr(5, 2) == props.currentMonth;
        if(currentMonth){
            return { variant: 'tonal', color: 'primary', class: 'font-weight-bold' }
        }

        return { variant: 'text', color: 'grey', class: '' }
    }

    const showInfo = id => {
        emit('updateFocusedId', id);
    }

    const emit = defineEmits(['updateFocusedId', 'seeWeek']);

    const dayDialog = ref(false);
    const focusedDay = ref(null);
    const showDayDialog = day => {
        focusedDay.value = day;
        dayDialog.value = true;
    }
</script>
<style scoped>
    .dayBox {
        width: 200px;
        max-width: 14.2857%;
        height: 150px;
        border: 1px solid #cccccc;
    }
    .holiday {
        background-color: #dddddd;
    }
</style>