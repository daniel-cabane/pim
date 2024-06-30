<template>
    <div class="d-flex text-center justify-space-between">
        <div class="dayBox" :class="{holiday: day.isHoliday}" v-for="day in week.days" :key="day.date">
            <v-btn :variant="day.date.substr(5, 2) != currentMonth ? 'text' : 'plain'" icon>
                {{ day.date.substr(8, 2) }}
            </v-btn>
            <div v-for="event in day.events" :key="event.start" :style="`background-color:${event.color}`" style="border-radius:5px;margin: 0px 2px;max-width:100%;">
                <span class="text-caption text-white text-truncate">
                    {{ event.start }} : <b>{{ event.title }}</b>
                </span>
            </div>
        </div>
    </div>
</template>
<script setup>
    const props = defineProps({ week: Object, currentMonth: Number });
</script>
<style scoped>
    .dayBox {
        width: 200px;
        max-width: 14.2857%;
        height: 175px;
        border: 1px solid #cccccc;
    }
    .holiday {
        background-color: #dddddd;
    }
</style>