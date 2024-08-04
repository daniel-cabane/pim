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
            const res = await patch(`/api/surveys/${survey.id}`, survey);
            if (res.survey) {
                this.surveys = this.surveys.map(s => s.id == res.survey.id ? res.survey : s);
            }
            this.isLoading = false;
        },
        async deleteSurvey(survey) {
            this.isLoading = true;
            const res = await del(`/api/surveys/${survey.id}`);
            this.surveys = this.surveys.filter(s => s.id != res.id);
            this.isLoading = false;
        },
        async getWorkshopSurveys(id) {
            this.isLoading = true;
            const res = await get(`/api/workshops/${id}/surveys`);
            this.surveys = res.surveys;
            this.isLoading = false;
        },
        async sendSurvey(survey, options) {
            this.isLoading = true;
            const res = await post(`/api/surveys/${survey.id}/send`, options);
            if (res.survey) {
                this.surveys = this.surveys.map(s => s.id == res.survey.id ? res.survey : s);
            }
            this.isLoading = false;
        },
        async openSurvey(survey){
            this.isLoading = true;
            const res = await post(`/api/surveys/${survey.id}/open`);
            if (res.survey) {
                survey.status = res.survey.status;
            }
            this.isLoading = false;
        },
        async closeSurvey(survey) {
            this.isLoading = true;
            const res = await post(`/api/surveys/${survey.id}/close`);
            if (res.survey) {
                survey.status = res.survey.status;
            }
            this.isLoading = false;
        },
        async getSurvey(id) {
            this.isReady = false;
            const res = await get(`/api/surveys/${id}`, true);
            this.survey = res.survey;
            this.isReady = true;
        },
        async submitSurvey(answers) {
            this.isLoading = true;
            await post(`/api/surveys/${this.survey.id}/submit`, {answers});
            this.loading = false;
        },
        async getResults(survey) {
            this.isLoading = true;
            const res = await get(`/api/surveys/${survey.id}/results`);
            console.log(res.results);
            survey.results = res.results;
            this.isLoading = false;
        }
    }
});