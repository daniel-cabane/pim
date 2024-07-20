<template>
    <v-container v-if="isReady">
        <div class="pb-4">
            <back-btn />
        </div>
        <v-card>
            <survey-display-card :survey="survey" :isLoading="isLoading" showSubmitButton @cancel="goBack" @submitSurvey="handleSubmitSurvey" />
        </v-card>
    </v-container>
</template>
    <script setup>
    import { useAuthStore } from '@/stores/useAuthStore';
    import { useRoute, useRouter } from 'vue-router';
    import { useSurveyStore } from '@/stores/useSurveyStore';
    import { storeToRefs } from 'pinia';

    const SurveyStore = useSurveyStore();
    const { getSurvey, submitSurvey } = SurveyStore;
    const { survey, isReady, isLoading } = storeToRefs(SurveyStore);
    const route = useRoute();
    getSurvey(route.params.id);

    const router = useRouter();
    const goBack = () => router.go(-1);

    const { removeSurvey } = useAuthStore();
    const handleSubmitSurvey = async answers => {
        await submitSurvey(answers);
        removeSurvey(survey.value.id);
        goBack();
    }
</script>