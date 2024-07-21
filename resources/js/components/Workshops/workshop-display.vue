<template>
    <div style="display:flex;flex-wrap:nowrap;max-width:100%">
        <div style="flex:1;">
            <v-card-title class="pb-1 pimSubtitleFont" style="text-overflow:ellipsis;font-size:48px;">
                {{ title }}
            </v-card-title>
            <v-card-subtitle class="font-italic">
                {{ $t('By') }} {{ workshop.teacher }}
            </v-card-subtitle>
        </div>
        <workshop-lcs :workshop="workshop" />
    </div>
    <v-card-text>
        <div v-html="description" />
        <v-container fluid class="px-0">
            <v-row>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-captionColor">
                        {{ $t('Sessions') }}
                    </div>
                    <div v-for="(session, index) in workshop.details.schedule" :key="index">
                        {{ $t(session.day) }} {{ $t('from') }} {{ session.start }} {{ $t('to') }} {{ session.finish }}
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-captionColor">
                        {{ $t('Start date') }}
                    </div>
                    <div>
                        {{ workshop.startDate ? workshop.formatedStartDate : $t(workshop.formatedStartDate) }}
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-captionColor">
                        {{ $t('Nb of sessions') }}
                    </div>
                    <div>
                        {{ workshop.details.nbSessions }}
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-captionColor">
                        {{ $t('Room nb') }}
                    </div>
                    <div>
                        {{ workshop.details.roomNb }}
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-captionColor">
                        {{ $t('Language') }}
                    </div>
                    <div>
                        {{ $t(spellLanguage) }}
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-captionColor">
                        {{ $t('Themes') }}
                    </div>
                    <div>
                        <workshop-themes-chips :themes="workshop.themes" size="small" />
                    </div>
                </v-col>
            </v-row>
            <v-row class="d-flex justify-end mb-3" v-if="workshop.editable">
                <span>
                    <v-btn color="primary" append-icon="mdi-pencil" @click="emit('editWorkshop')">
                        {{ $t('Edit') }}
                    </v-btn>
                </span>
            </v-row>
            <workshop-teacher-tabs :workshop="workshop" v-if="user && user.is.teacher" />
            <survey-table-student :workshopId="workshop.id" v-if="user && user.is.student && workshop.application" />
        </v-container>
    </v-card-text>
</template>
<script setup>
    import { computed, ref } from 'vue';
    import usePickWorkshopLg from '@/composables/usePickWorkshopLg';
    import { useAuthStore } from '@/stores/useAuthStore';

    const props = defineProps({ workshop: Object });
    const emit = defineEmits(['editWorkshop']);

    const { title, description } = usePickWorkshopLg(props.workshop);

    const { user } = useAuthStore();

    const spellLanguage = computed(() => {
        if(props.workshop.language == 'fr'){
            return 'French'
        }
        if (props.workshop.language == 'en') {
            return 'English'
        }

        return 'Both'
    });
</script>