import { defineStore } from 'pinia';
import useAPI from '@/composables/useAPI';

const { get, post, patch, del } = useAPI();

export const useSurveyStore = defineStore({
    id: 'survey',
    state: () => ({
        isReady: false,
        isLoading: false,
        survey: {},
        surveys: []
    }),
    actions: {
        async createSurvey(data) {
            this.isLoading = true;
            const res = await post('/api/survey', data);
            if(res.survey){
                this.survey = res.survey;
                this.surveys = [res.survey, ...this.surveys];
            }
            this.isLoading = false;
        },
        async getAdminSurveys() {
            this.isReady = false;
            const res = await get('/api/admin/surveys');
            this.surveys = res.surveys;
            this.isReady = true;
        },
        async updateSurvey(survey){
            this.isLoading = true;
            const res = await patch(`/api/survey/${survey.id}`, survey);
            if (res.survey) { //////////////////// WHY IS THE MAIN TITLE NOT UPDATING IN THE ADMIN TABLE ??
                this.surveys = this.surveys.map(s => s.id == res.survey.id ? survey : s);
            }
            this.isLoading = false;
        },
        async deleteSurvey(survey) {
            this.isLoading = true;
            const res = await del(`/api/survey/${survey.id}`);
            this.surveys = res.surveys;
            this.isLoading = false;
        },
        async getWorkshopSurveys(id) {
            this.isLoading = true;
            const res = await get(`/api/workshops/${id}/surveys`);
            this.surveys = res.surveys;
            this.isLoading = false;
        }
    }
});