<template>
    <v-container>
        <div class="pb-4">
            <back-btn />
        </div>
        <v-card v-if="isReady">
            <survey-display-card :survey="survey" :isLoading="isLoading" showSubmitButton @cancel="goBack" @submitSurvey="handleSubmitSurvey" />
        </v-card>
        <v-dialog persistent v-model="loginDialog" max-width="500">
            <v-card :title="$t('Login required')">
                <v-card-text v-if="!user">
                    <div>
                        {{ $t('You need to be signed in to answer this survey') }}
                    </div>
                    <div class="text-center">
                        <google-button />
                    </div>
                    <div class="py-2 text-caption text-captionColor text-center">
                        <span v-if="locale == 'en'">
                        @g.lfis.edu.hk accounts only
                        </span>
                        <span v-else>
                        Comptes @g.lfis.edu.hk uniquement
                        </span>
                    </div>
                </v-card-text>
                <v-card-actions>
                    <v-spacer/>
                    <v-btn variant="tonal" color="error" @click="goBack">{{ $t('Go back') }}</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>
    <script setup>
    import { useAuthStore } from '@/stores/useAuthStore';
    import { useRoute, useRouter } from 'vue-router';
    import { useSurveyStore } from '@/stores/useSurveyStore';
    import { storeToRefs } from 'pinia';
    import { ref } from 'vue';
    import { useI18n } from 'vue-i18n';

    const { locale } = useI18n();
    const { removeSurvey, user } = useAuthStore();

    const loginDialog = ref(!user);


    const SurveyStore = useSurveyStore();
    const { getSurvey, submitSurvey } = SurveyStore;
    const { survey, isReady, isLoading } = storeToRefs(SurveyStore);
    const route = useRoute();
    if(user){
        getSurvey(route.params.id);
    }

    const router = useRouter();
    const goBack = () => router.go(-1);

    const handleSubmitSurvey = async answers => {
        await submitSurvey(answers);
        removeSurvey(survey.value.id);
        goBack();
    }
</script>