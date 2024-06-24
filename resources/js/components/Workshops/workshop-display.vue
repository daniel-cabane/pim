<template>
    <workshop-header :title="title" :workshop="workshop" />
    <v-card-text>
        <div v-html="description" />
        <v-container fluid class="px-0">
            <v-row no-gutters>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-grey">
                        {{ $t('Sessions') }}
                    </div>
                    <div v-for="(session, index) in workshop.details.schedule" :key="index">
                        {{ $t(session.day) }} {{ $t('from') }} {{ session.start }} {{ $t('to') }} {{ session.finish }}
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-grey">
                        {{ $t('Start date') }}
                    </div>
                    <div>
                        {{ workshop.startDate ? workshop.formatedStartDate : $t(workshop.formatedStartDate) }}
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-grey">
                        {{ $t('Nb of sessions') }}
                    </div>
                    <div>
                        {{ workshop.details.nbSessions }}
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-grey">
                        {{ $t('Room nb') }}
                    </div>
                    <div>
                        {{ workshop.details.roomNb }}
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-grey">
                        {{ $t('Language') }}
                    </div>
                    <div>
                        {{ $t(spellLanguage) }}
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-grey">
                        {{ $t('Themes') }}
                    </div>
                    <div>
                        <workshop-themes-chips :themes="workshop.themes" size="small" />
                    </div>
                </v-col>
            </v-row>
        </v-container>
    </v-card-text>
</template>
<script setup>
    import { computed } from 'vue';
    import usePickWorkshopLg from '@/composables/usePickWorkshopLg';

    const props = defineProps({ workshop: Object });

    const { title, description } = usePickWorkshopLg(props.workshop);

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