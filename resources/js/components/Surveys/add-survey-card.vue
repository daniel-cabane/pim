<template>
    <v-card :title="$t('New survey')">
        <v-card-text>
            <v-select variant="outlined" :items="languages" :label="$t('Languages')" v-model="newSurvey.language" />
            <v-text-field variant="outlined" label="Titre (fr)" v-model="newSurvey.title_fr"
                v-if="newSurvey.language != 'en'" />
            <v-text-field variant="outlined" label="Title (en)" v-model="newSurvey.title_en"
                v-if="newSurvey.language != 'fr'" />
        </v-card-text>
        <v-card-actions>
            <v-spacer />
            <v-btn color="error" variant="text" :disabled="isLoading" @click="emit('closeDialog')">
                {{ $t('Cancel') }}
            </v-btn>
            <v-btn color="primary" variant="flat" :loading="isLoading" @click="handleNnewSurvey">
                {{ $t('Submit') }}
            </v-btn>
        </v-card-actions>
    </v-card>
</template>
<script setup>
    import { reactive } from "vue";
    import { useSurveyStore } from '@/stores/useSurveyStore';
    import { storeToRefs } from 'pinia';
    import { useI18n } from 'vue-i18n';

    const { t } = useI18n();

    const props = defineProps({workshopId: [Number, null]});
    const emit = defineEmits(['closeDialog']);

    const languages = reactive([
        { title: 'Français', value: 'fr' }, { title: 'English', value: 'en' }, { title: t('Both'), value: 'both' }
    ]);

    const SurveyStore = useSurveyStore();
    const { createSurvey } = SurveyStore;
    const { isLoading } = storeToRefs(SurveyStore);
    const newSurvey = reactive({ title_fr: '', title_en: '', language: 'fr' });
    const handleNnewSurvey = async () => {
        await createSurvey({workshopId: props.workshopId, ...newSurvey});
        newSurvey.title_fr = '';
        newSurvey.title_en = '';
        newSurvey.language = 'fr';
        emit('closeDialog');
    }
</script>