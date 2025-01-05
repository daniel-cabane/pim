<template>
    <div style="display: flex;" class="mb-3">
        <div>
            <div style="height:80px;border-bottom: 1px solid #cccccc;"/>
            <div style="display: flex;">
                <div style="min-width:40px;max-width:40px;margin-right:4px;">
                    <div style="height: 6px;" />
                    <div v-for="hour in openDoorHours" style="height:60px;" class="text-right">
                        <span class="text-caption text-captionColor">
                            {{ hour }}
                        </span>
                    </div>
                </div>
                <div style="min-width:20px;max-width:20px;" class="hourDashContainer">
                    <div style="height: 20px;" />
                    <div v-for="i in 2" />
                </div>
            </div>
        </div>
        <div style="display:flex;flex:1;">
            <div v-for="day in week" style="min-width:19%;max-width:19%;">
                <div class="topBox text-center" v-if="day.dateData">
                    <div class="text-captionColor">
                        {{ name == 'xs' ? ($t(day.dateData.dayOfTheWeek)).substring(0,3)+'.' : $t(day.dateData.dayOfTheWeek) }}
                    </div>
                    <div class="font-weight-bold px-2" :class="[day.isHoliday ? 'text-captionColor' : '', name == 'xs' ? 'text-h6' : 'text-h4']">
                        {{ day.dateData.dayNumber }}
                    </div>
                    <div class="text-captionColor">
                        {{ name == 'xs' ? ($t(day.dateData.monthName)).substring(0,3)+'.' : $t(day.dateData.monthName) }}
                    </div>
                </div>
                <div style="height: 20px;border-left: 1px solid #cccccc;border-bottom: 1px solid #cccccc;"/>
                <pi-room-day :day="day"/>
                <div style="border-left: 1px solid #cccccc;height:15px;"/>
            </div>
        </div>
    </div>
</template>
<script setup>
     import { useDisplay } from 'vuetify';

     const { name } = useDisplay();

    const props = defineProps({week: Array});

    const openDoorHours = ['11:35', '12:30', '13:30'];
</script>
<style scoped>
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
    .hourDashContainer > div {
        height: 60px;
        border-bottom: 1px solid #cccccc;
    }
</style>