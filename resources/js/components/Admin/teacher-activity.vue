<template>
    <div v-if="teacher.activity">
        <div class="text-h5 text-grey mb-4 d-flex align-center" v-if="showName">
            <span>
                {{ teacher.name }} <span v-if="teacher.preferences.hoursDuePerWeek">({{ teacher.preferences.hoursDuePerWeek }}h)</span>
            </span>
            <v-btn class="ml-3" flat icon="mdi-list-box-outline" @click="showTeacherActivityList(teacher)"/>
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
                        <div class="d-flex justify-center">{{ formatDate(session.date) }} - {{ session.start }} ({{ session.duration }}h)</div>
                    </span>
                </v-tooltip>
                <v-tooltip v-for="session in week.openDoorSessions">
                    <template v-slot:activator="{ props }">
                        <div class="sessionBox" :style="`background-color:${boxColor(session)};`" v-bind="props"/>
                    </template>
                    <span>
                        <div class="text-subtitle-1 d-flex justify-center">{{ $t('Open doors') }}</div>
                        <div class="d-flex justify-center">{{ session.roomNb }}</div>
                        <div class="d-flex justify-center">{{ formatDate(session.date) }} - {{ session.start }} (1h)</div>
                    </span>
                </v-tooltip>
                <div class="holidayBox" v-if="week.isHoliday"/>
                <div class="base" v-else/>
                <div style="position:absolute;bottom:-20px;left:0px;" v-if="week.newMonth" class="text-caption text-captionColor">
                    {{ $t(week.month).substring(0,3) }}.
                </div>
            </div>
        </div>
        <div class="pt-2 mb-2">
            <div class="gutter d-flex">
                <div class="hoursDone" style="border-radius: 5px 0px 0px 5px;" :style="`flex:${hours.mission};background-color:${colors.mission}`" v-if="hours.mission">
                    <span class="hoursLegend" :style="`color:${colors.mission}`">
                        Missions
                    </span>
                </div>
                <div class="hoursDone" style="border-radius: 5px 0px 0px 5px;" :style="`flex:${hours.past};background-color:${colors.workshops.past}`" v-if="hours.past">
                    <span class="hoursLegend" :style="`color:${colors.workshops.past}`">
                        {{ $t('Past') }}
                    </span>
                </div>
                <div class="hoursDone" :style="`flex:${hours.future};background-color:${colors.workshops.future};`" v-if="hours.future">
                    <span class="hoursLegend" :style="`color:${colors.workshops.future}`">
                        {{ $t('Future') }}
                    </span>
                </div>
                <div class="hoursDone" :style="`flex:${hours.total - (hours.past+hours.future+hours.mission)}`">
                </div>
                <div class="nowDivider" :style="`left:${hours.ratio}%`" v-if="hours.ratio"/>
            </div>
        </div>
        <div class="d-flex justify-center text-captionColor text-h6">
            <span v-if="hours.total">
                {{ hours.past + hours.future + hours.mission }}/{{ hours.total }}h
            </span>
            <span v-else>
                {{ $t('No hours due') }}
            </span>
        </div>
        <v-dialog v-model="activityListDialog" width="650" scrollable>
            <v-card prepend-icon="mdi-list-box-outline" :title="`${focusedTeacher.name} - ${$t('Details')}`">
                <v-card-subtitle class="d-flex text-h6 justify-space-around mb-2">
                    <div>
                        <div v-if="teacher.preferences.hoursDuePerWeek">
                            {{ teacher.preferences.hoursDuePerWeek }}h {{ $t('per week') }}
                        </div>
                        <div v-else>
                            {{ $t('No hours due') }}
                        </div>
                    </div>
                    <div>
                        <div v-if="hours.total">
                            {{ $t('Annual total') }} {{ hours.past + hours.future + hours.mission }}/{{ hours.total }}h
                        </div>
                        <div v-else>
                            {{ $t('No hours due') }}
                        </div>
                    </div>
                </v-card-subtitle>
                <v-card-text>
                    <div class="text-caption text-captionColor">
                        Missions
                    </div>
                    <div>
                        <v-table>
                            <tbody>
                                <tr v-for="mission in focusedTeacher.activity.missions">
                                    <td class="text-h6">{{ mission.title }}</td>
                                    <td><v-chip color="black" variant="flat" text="mission" label/></td>
                                    <td>{{ mission.lessonHours + mission.prepHours*0.5 }}h</td>
                                </tr>
                            </tbody>
                        </v-table>
                    </div>
                    <div class="text-caption text-captionColor mt-5">
                        {{ $t('Open doors') }} & {{ $t('workshops') }}
                    </div>
                    <v-data-table :items-per-page="-1" :items="focusedTeacher.activityItems" :headers="headers">
                        <template v-slot:item="{ item }">
                            <tr>
                                <td>{{ formatDateDDMMYY(item.date) }}</td>
                                <td>{{ item.start }}</td>
                                <td>
                                    <v-tooltip :text="item.typeDetails.text" location="top">
                                        <template v-slot:activator="{ props }">
                                            <v-chip variant="flat" :color="item.typeDetails.color" v-bind="props">
                                                <span class="text-white">{{ $t(item.typeDetails.title) }}</span>
                                            </v-chip>
                                        </template>
                                    </v-tooltip>
                                </td>
                                <td>{{ item.cumulative }}h</td>
                            </tr>
                        </template>
                    </v-data-table>
                </v-card-text>
                <v-card-actions>
                    <v-spacer/>
                    <v-btn color="primary" variant="tonal" :text="$t('Close')" @click="activityListDialog = false"/>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>
<script setup>
    import { computed, ref } from "vue";
    import { useI18n } from 'vue-i18n';

    const { locale, t } = useI18n();

    const props = defineProps({ teacher: Object, showName: Boolean });

    const colors = {
        mission: '#555555',
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
                mission: props.teacher.activity.hours.mission,
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

    const formatDateDDMMYY = dateString => {
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = String(date.getFullYear()).slice(-2);
        const dayOfWeek = date.toLocaleDateString(locale, { weekday: 'short' });
        return `${dayOfWeek}. ${day}-${month}-${year}`;
    }

    const headers = computed(() => [
        { title: 'Date', value: 'date' },
        { title: t('Time'), value: 'start' },
        { title: t('Type'), value: 'type' },
        { title: t('Cumulative'), value: 'cumulative' },
    ]);
    const activityListDialog = ref(false);
    const focusedTeacher = ref(null);
    const showTeacherActivityList = teacher => {
        focusedTeacher.value = teacher;
        if(!focusedTeacher.value.activityItems){
            let items = [];
            focusedTeacher.value.activity.weeks.forEach(w => {
                let weekItems = [];
                w.openDoorSessions.forEach(s => weekItems.push(s));
                w.workshopSessions.forEach(s => weekItems.push(s));
                weekItems = weekItems.sort((a, b) => {
                    if (a.date !== b.date) {
                        return new Date(a.date) - new Date(b.date);
                    }
                    return a.start.localeCompare(b.start);
                });
                items = items.concat(weekItems);
            });
            let cumulative = 0;
            focusedTeacher.value.activity.missions.forEach(m => {
                cumulative += m.lessonHours + 0.5*m.prepHours;
            });
            focusedTeacher.value.activityItems = items.map(i => {
                console.log(i);
                cumulative += i.type == 'Open doors' ? 0.5 : i.duration;
                let typeDetails;
                if(i.type == 'Open doors'){
                    typeDetails = {
                        title: 'Open doors',
                        text: `${i.campus} - ${i.roomNb}`,
                        color: i.isPast ? colors.openDoors.past : colors.openDoors.future
                    }
                } else {
                    typeDetails = {
                        title: 'Workshop',
                        text: `${i.workshopTitle} (${i.duration}h)`,
                        color: i.isPast ? colors.workshops.past : colors.workshops.future
                    }
                }
                return { ...i, cumulative, typeDetails };
            });
        }
        activityListDialog.value = true;
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
        display: flex;
        justify-content: flex-end;
        align-items: center;
        overflow-x: hidden;
    }
    .hoursLegend {
        margin-right: 8px;
        font-size: 24px;
        font-weight: bold;
        text-shadow: rgba(20,20,20,0.5) -1px 0, rgba(20,20,20,0.3) 0 -1px, rgba(240,240,240,0.5) 0 1px, rgba(20,20,20,0.3) -1px -2px; 
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
