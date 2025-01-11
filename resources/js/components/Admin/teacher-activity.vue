<template>
    <div v-if="teacher.activity">
        <div class="text-h5 text-grey">
            {{ teacher.name }}
        </div>
        <div class="d-flex mb-5">
            <div v-for="week in teacher.activity.weeks" class="weekBox">
                <v-tooltip v-for="session in week.workshopSessions">
                    <template v-slot:activator="{ props }">
                        <div class="sessionBox" style="background-color:#9ACD32;" v-bind="props"/>
                    </template>
                    <span>
                        <div style="max-width:150px;" class="text-subtitle-1 text-truncate">{{ session.workshopTitle }}</div>
                        <div class="d-flex justify-center">{{ session.roomNb }}</div>
                        <div class="d-flex justify-center">{{ formatDate(session.date) }} - {{ session.start }}</div>
                    </span>
                </v-tooltip>
                <v-tooltip v-for="session in week.openDoorSessions">
                    <template v-slot:activator="{ props }">
                        <div class="sessionBox" style="background-color:#C7E685;" v-bind="props"/>
                    </template>
                    <span>
                        <div class="text-subtitle-1 d-flex justify-center">{{ $t('Open doors') }}</div>
                        <div class="d-flex justify-center">{{ session.roomNb }}</div>
                        <div class="d-flex justify-center">{{ formatDate(session.date) }} - {{ session.start }}</div>
                    </span>
                </v-tooltip>
                <div class="base"/>
                <div style="position:absolute;bottom:-20px;left:0px;" v-if="week.newMonth" class="text-caption text-captionColor">
                    {{ $t(week.month).substring(0,3) }}.
                </div>
            </div>
        </div>
        <div class="d-flex justify-space-between text-captionColor">
            <span>
                {{ $t('Hours done') }} : {{ teacher.activity.hoursDone }}
            </span>
            <span>
                {{ $t('Current balance') }} : {{ hoursDue.balance }}
            </span>
            <span>
                {{ $t('Total hours due') }} : {{ hoursDue.total }}
            </span>
        </div>
    </div>
</template>
<script setup>
    import { computed } from "vue";
    import { useI18n } from 'vue-i18n';

    const { locale } = useI18n();

    const props = defineProps({ teacher: Object });

    const hoursDue = computed(() => {
        if(props.teacher.activity.weeks && props.teacher.activity.weeks.length && props.teacher.preferences.hoursDuePerWeek){
            return {
                balance: props.teacher.preferences.hoursDuePerWeek*props.teacher.activity.weeksPast - props.teacher.activity.hoursDone,
                total: props.teacher.activity.weeks.length * props.teacher.preferences.hoursDuePerWeek
            }
        }
        return {balance: "N/A", total: "N/A"}
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
        min-height: 25px;
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
</style>