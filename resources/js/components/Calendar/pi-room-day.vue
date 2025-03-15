<template>
    <div class="holiday" v-if="day.isHoliday">
        <span>
            Holiday
        </span>
    </div>
    <div v-else>
        <div class="dayHour" v-for="hour in hours">
            <div v-if="hour" class="dayHour px-2 pt-3" :style="`background:${hour.bg};`">
                <div style="line-height:40%;" class="mb-2 font-weight-bold">
                    {{ $t(hour.title) }}
                </div>
                <div class="text-caption" style="line-height:90%;">
                    {{ hour.subtitle }}
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
    import { useTheme } from 'vuetify';
    const theme = useTheme();

    const props = defineProps({ day: Object });

    const hours = [null, null];
    
    [['11:30', '11:35'], ['12:30', '12:35']].forEach((startTime, index) => {
        props.day.events.forEach(e => {
            if(startTime.includes(e.start)){
                if(e.eventType == 'session' && e.allowSilentGames){
                    hours[index] = { title: 'Workshop', subtitle: e.title, bg: theme.global.current.value.colors.inclusiveWorkshop }    
                } else if(e.eventType == 'session'){
                    hours[index] = { title: 'Workshop', subtitle: e.title, bg: theme.global.current.value.colors.exclusiveWorkshop }
                } else if(e.eventType == 'openDoor' && hours[index] == null){
                    hours[index] = { title: 'Open Doors', subtitle: '', bg: theme.global.current.value.colors.openDoors }
                }
            }
        });
        // if(hourDetails){
        //     if(hourDetails.eventType == "openDoor"){
        //         hours[index] = { title: 'Open Doors', subtitle: '', bg: theme.global.current.value.colors.openDoors }
        //     } else if(hourDetails.allowSilentGames) {
        //         hours[index] = { title: 'Workshop', subtitle: hourDetails.title, bg: theme.global.current.value.colors.inclusiveWorkshop }
        //     } else {
        //         hours[index] = { title: 'Workshop', subtitle: hourDetails.title, bg: theme.global.current.value.colors.exclusiveWorkshop }
        //     }
        // }
    });
</script>
<style scoped>
    .dayHour {
        min-height:60px;
        max-height:60px;
        width:100%;
        border-bottom: 1px solid #cccccc;
        border-left: 1px solid #cccccc;
        color: white;
    }
    .holiday {
        background-color: #eeeeee;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height:120px;
        max-height:120px;
        width:100%;
        border-bottom: 1px solid #cccccc;
        border-left: 1px solid #cccccc;
    }
    .holiday > span {
        color: lightgray;
        font-weight: bold;
        font-size: 3vw;
        max-width: 100%;
        display: inline-block;
        transform: rotate(-30deg);
    }
    .openDoors {
        background: green;
    }
    .inclusiveWorkshop {
        background: blue;
    }
    .exclusiveWorkshop {
        background: red;
    }
</style>