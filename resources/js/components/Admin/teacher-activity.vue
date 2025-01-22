<template>
    <div v-if="teacher.activity">
        <div class="text-h5 text-grey mb-4" v-if="showName">
            {{ teacher.name }} <span v-if="teacher.preferences.hoursDuePerWeek">({{ teacher.preferences.hoursDuePerWeek }}h)</span>
        </div>
        <div class="d-flex mb-5">
            <div v-for="week in teacher.activity.weeks" class="weekBox">
                <v-tooltip v-for="session in week.workshopSessions">
                    <template v-slot:activator="{ props }">
                        <div class="sessionBox" :style="`background-color:${boxColor(session)};`" v-bind="props"/>
                    </template>
                    <span>
                        <div style="max-width:150px;" class="text-subtitle-1 text-truncate">{{ session.workshopTitle }}</div>
                        <div class="d-flex justify-center">{{ session.roomNb }}</div>
                        <div class="d-flex justify-center">{{ formatDate(session.date) }} - {{ session.start }}</div>
                    </span>
                </v-tooltip>
                <v-tooltip v-for="session in week.openDoorSessions">
                    <template v-slot:activator="{ props }">
                        <div class="sessionBox" :style="`background-color:${boxColor(session)};`" v-bind="props"/>
                    </template>
                    <span>
                        <div class="text-subtitle-1 d-flex justify-center">{{ $t('Open doors') }}</div>
                        <div class="d-flex justify-center">{{ session.roomNb }}</div>
                        <div class="d-flex justify-center">{{ formatDate(session.date) }} - {{ session.start }}</div>
                    </span>
                </v-tooltip>
                <div class="holidayBox" v-if="week.isHoliday"/>
                <div class="base" v-else/>
                <div style="position:absolute;bottom:-20px;left:0px;" v-if="week.newMonth" class="text-caption text-captionColor">
                    {{ $t(week.month).substring(0,3) }}.
                </div>
            </div>
        </div>
        <div class="pb-6 pt-2 mb-10">
            <div class="gutter d-flex">
                <div class="hoursDone" style="border-radius: 5px 0px 0px 5px;" :style="`flex:${hours.past};background-color:${colors.workshops.past}`" v-if="hours.past">
                    <!-- <span :style="`color:${colors.workshops.past}`">
                        <div class="text-caption">
                            {{ $t('Done') }}
                        </div>
                        <div>
                            {{ hours.past }}h
                        </div>
                    </span> -->
                </div>
                <div class="hoursDone" :style="`flex:${hours.future};background-color:${colors.workshops.future};`" v-if="hours.future">
                    <span class="text-captionColor">
                        {{ hours.past + hours.future }}/{{ hours.total }}h
                    </span>
                    <!-- <span :style="`color:${colors.workshops.future}`">
                        <div class="text-caption">
                            {{ $t('Planned') }}
                        </div>
                        <div>
                            {{ hours.future }}h
                        </div>
                    </span> -->
                </div>
                <div class="hoursDone" :style="`flex:${hours.total - (hours.past+hours.future)}`">
                    <!-- <span>
                        <div class="text-caption text-captionColor">
                            {{ $t('Due') }}
                        </div>
                        <div class="text-captionColor">
                            {{ hours.total }}h
                        </div>
                    </span> -->
                </div>
                <div class="nowDivider" :style="`left:${hours.ratio}%`" v-if="hours.ratio"/>
            </div>
        </div>
    </div>
</template>
<script setup>
    import { computed } from "vue";
    import { useI18n } from 'vue-i18n';

    const { locale } = useI18n();

    const props = defineProps({ teacher: Object, showName: Boolean });

    const colors = {
        workshops: {
            past: '#9ACD32',
            future: '#6A7CFF'
        },
        openDoors: {
            past: '#C7E685',
            future: '#A2D9F1'
        },
    }

    const boxColor = session => {
        if(session.type == 'Open doors'){
            if(session.isPast){
                return colors.openDoors.past;
            }
            return colors.openDoors.future;
        }
        if(session.isPast){
                return colors.workshops.past;
            }
            return colors.workshops.future;
    }

    const hours = computed(() => {
        if(props.teacher.activity.hours && props.teacher.activity.nbWeeks && props.teacher.activity.nbWeeks.past + props.teacher.activity.nbWeeks.future > 0 && props.teacher.preferences.hoursDuePerWeek){
            return {
                past: props.teacher.activity.hours.past,
                future: props.teacher.activity.hours.future,
                total: (props.teacher.activity.nbWeeks.past + props.teacher.activity.nbWeeks.future) * props.teacher.preferences.hoursDuePerWeek,
                ratio: 100 * props.teacher.activity.nbWeeks.past / (props.teacher.activity.nbWeeks.past + props.teacher.activity.nbWeeks.future)
            }
        }
        return false
    });

    const formatDate = dateString => {
        const date = new Date(dateString);
        const formattedDate = date.toLocaleDateString(locale.value, { weekday: 'short', day: '2-digit', month: '2-digit' });

        return formattedDate.charAt(0).toUpperCase() + formattedDate.slice(1);
    }
</script>
<style scoped>
    .weekBox {
        margin-right: 6px;
        position:relative;
        min-width: 20px;
        min-height: 60px;
        display: flex;
        flex-direction: column-reverse;
        justify-content: flex-start;
        align-items: center;
        flex-wrap: wrap;
    }
    .base {
        font-size: 10px;
        border-top: none;
        border-right: 1px solid lightgray;
        border-bottom: 1px solid lightgray;
        border-left: 1px solid lightgray;
        padding: 2px 10px;
        position:absolute;
        bottom: -2px;
        left: 0px;
    }
    .sessionBox {
        margin-left: 2px;
        margin-top: 1px;
        width: 18px;
        height: 18px;
    }
    .holidayBox {
        position:absolute;
        bottom: 0px;
        left: 2px;
        width: 18px;
        height: 54px;
        background: lightgray;
    }
    .gutter {
        position: relative;
        background-color: #f0f0f0;
        border-radius: 5px;
        box-shadow: inset 0 5px 5px -5px #888, inset 0 -5px 5px -5px #888;
        height: 40px;
        /* overflow: hidden; */
    }
    .hoursDone {
        height: 100%;
        position: relative;
    }
    .hoursDone > span {
        position: absolute;
        right: 5px;
        bottom: -20px;
        text-align: end;
        line-height: 75%;
        white-space: nowrap;
        /* overflow: hidden; */
        /* text-overflow: ellipsis; */
    }
    .nowDivider {
        position: absolute;
        top: -10px;
        height: calc(100% + 20px);
        width: 3px;
        border-left: 1px dotted red;
        border-radius: 5px;
    }
</style>