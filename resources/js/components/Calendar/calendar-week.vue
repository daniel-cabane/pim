<template>
    <div class="d-flex">
        <div style="min-width:60px;max-width:60px;">
            <div style="height: 80px;border-bottom: 1px solid #cccccc;" />
            <div style="display:flex;">
                <div style="min-width:40px;max-width:40px;margin-right:4px;">
                    <div style="height: 6px;" />
                    <div v-for="i in hoursInDay" style="height:60px;" class="text-right">
                        <span class="text-caption text-captionColor">
                            {{ i }}:00
                        </span>
                    </div>
                </div>
                <div style="min-width:20px;max-width:20px;" class="hourDashContainer">
                    <div style="height: 20px;" />
                    <div v-for="i in 12" />
                </div>
            </div>
        </div>
        <div v-for="(day, index) in days" style="min-width:14%;max-width:14%;" :key="day.date">
            <div class="topBox text-center">
                <div>
                    {{ $t(daysOfWeek[index]) }}
                </div>
                <div class="text-h5 font-weight-bold pa-2" :class="isToday(day.date) ? 'today' : ''">
                    {{ day.date.substr(8, 2) }}
                </div>
            </div>
            <div style="height:20px;border-left: 1px solid #cccccc;border-bottom: 1px solid #cccccc;" />
            <div v-for="hour in day.hours" :key="hour.nb" :class="hour.nb == 19 ? 'lastDayHourBox' : 'dayHourBox'"
                style="position: relative;">
                <event-block v-for="event in hour.events" :key="event.id" :event="event" :showInfo="focusedId == event.id"
                    @click.stop="updateFocusedId(event.id)" @hideMe="resetFocusedId"/>
            </div>
        </div>
    </div>
</template>
<script setup>
    import { ref, computed, onMounted, onUnmounted } from "vue";

    const props = defineProps({ week: Object });

    const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    const hoursInDay = [];
    for (let i = 7; i <= 19; i++) {
        hoursInDay.push(i)
    }

    const isToday = date => {
        const today = new Date();
        const inputDate = new Date(date);
        return (
            inputDate.getDate() === today.getDate() &&
            inputDate.getMonth() === today.getMonth() &&
            inputDate.getFullYear() === today.getFullYear()
        );
    }

    const days = computed(() => {
        const days = [];
        props.week.days.forEach(d => {
            const hours = [];
            const events = d.events.map(e => {
                [e.startHour, e.startMinute] = e.start.split(':');
                [e.endHour, e.endMinute] = e.end.split(':');
                e.height = (parseInt(e.endHour) * 60 + parseInt(e.endMinute)) - (parseInt(e.startHour) * 60 + parseInt(e.startMinute));
            })
            for(let i=7 ; i <= 19 ; i++){
                let dayEvents = [];
                d.events.forEach(e => {
                    if (e.startHour == i){
                        dayEvents.push(e);
                    }
                });
                hours.push({
                    nb: i,
                    events: dayEvents
                });
            }
            d.hours = hours;
            days.push(d);
        });
        return days;
    });

    const focusedId = ref('0');
    const updateFocusedId = id => {
        focusedId.value = id
    };
    onMounted(() => {
        document.addEventListener("click", resetFocusedId);
    });

    onUnmounted(() => {
        document.removeEventListener("click", resetFocusedId);
    });
    const resetFocusedId = () => focusedId.value = '0';
</script>
<style scoped>
    .hourDashContainer > div {
        height: 60px;
        border-bottom: 1px solid #cccccc;
    }
    .topBox {
        width: 100%;
        height: 80px;
        border-left: 1px solid #cccccc;
        border-bottom: 1px solid #cccccc;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .dayHourBox {
        height: 60px;
        border-bottom: 1px solid #cccccc;
        border-left: 1px solid #cccccc;
    }
    .lastDayHourBox {
        height: 60px;
        border-left: 1px solid #cccccc;
    }
    .today {
        color: red;
        border: 1px solid red;
        border-radius: 100%;
    }
</style>