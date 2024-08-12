<template>
    <div>
        <div class="text-caption text-captionColor" v-if="surveys.length > 0">
            {{ $t('Surveys') }}
        </div>
        <v-table>
            <tbody>
                <tr v-for="survey in surveys">
                    <td>{{ survey.mainTitle }}</td>
                    <td>{{ survey.questions.length }} question<span v-if="survey.questions.length > 1">s</span></td>
                    <td>{{ survey.status }}</td>
                    <td>
                        <v-btn :variant="survey.answers ? 'outlined' : 'flat'" :color="survey.answers ? 'primary' : 'success'"
                            size="small" :disabled="survey.status == 'closed' && survey.answers == null"
                            :to="`/surveys/${survey.id}`">
                            {{ survey.answers ? $t('See answer') : $t('Answer|verb') }}
                        </v-btn>
                    </td>
                </tr>
            </tbody>
        </v-table>
    </div>
</template>
<script setup>
    import { useSurveyStore } from '@/stores/useSurveyStore';
    import { storeToRefs } from 'pinia';

    const props = defineProps({ workshopId: Number });

    const SurveyStore = useSurveyStore();
    const { getWorkshopSurveys } = SurveyStore;
    const { surveys } = storeToRefs(SurveyStore);
    getWorkshopSurveys(props.workshopId);
</script>