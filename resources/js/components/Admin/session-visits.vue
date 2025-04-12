<template>
    <div class="mb-8">
        <v-divider class="my-3"/>
        <div class="text-h6 text-grey d-flex justify-space-between">
            <span>
                {{ session.teacherName }}
            </span>
            <span>
                {{ session.date }}
            </span>
            <span>
                {{ session.start }}
                &rarr;
                {{ session.finish }}
            </span>
        </div>
        <div class="d-flex flex-wrap">
            <v-card width="300" class="ma-2 pa-2" v-for="visit in visits">
                <div class="d-flex justify-end text-caption text-captionColor">
                    {{ formatTime(visit.dateTime) }}
                </div>
                <div class="d-flex ga-2">
                    <v-avatar :image="visit.user.avatar" size="80"/>
                    <div class="mt-2">
                        <div v-if="visit.user.name" class="text-truncate" style="max-width:200px;">
                            {{ visit.user.name  }} ({{ visit.user.level }}{{ visit.user.section }})
                        </div>
                        <div v-else>
                            {{ $t('Unknown student') }}
                        </div>
                        <div class="text-caption text-captionColor">
                            {{ visit.user.email }}
                        </div>
                        <div class="text-caption text-captionColor">
                            {{ visit.tagNb }}
                        </div>
                    </div>
                </div>
            </v-card>
        </div>
    </div>
</template>
<script setup>
    import { computed } from 'vue';
    import { useI18n } from 'vue-i18n';

    const { locale } = useI18n();

    const props = defineProps({ visits: Object });

    const session = computed(() => {
        const sessionData = props.visits[0].session;
        return {
            teacherName : sessionData.teacher.name,
            date: (new Date(sessionData.date)).toLocaleString(locale.value, { weekday: 'short', month: 'long', day: 'numeric' }),
            start: sessionData.start,
            finish: sessionData.finish
        }
    });

    const formatTime = dateTime => {
        const date = new Date(dateTime);
        const options = { hour: '2-digit', minute: '2-digit' };
        return new Intl.DateTimeFormat(locale.value, options).format(date);
    }
</script>