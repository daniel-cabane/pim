import { defineStore } from 'pinia';
import useAPI from '@/composables/useAPI';

const { get, post, patch, del } = useAPI();

export const useWorkshopStore = defineStore({
    id: 'workshop',
    state: () => ({
        workshops: [],
        myWorkshops: [],
        themes: [],
        workshop: {},
        isReady: false,
        imageLoading: false
    }),
    actions: {
        async getThemes() {
            this.themes = await get('/api/workshops/themes');
        },
        async createWorkshop(newWorkshop) {
            this.isReady = false;
            const res = await post('/api/workshops', newWorkshop);
            this.workshop = res.workshop;
            this.isReady = true;
            return res.workshop;
        },
        async getWorkshop(id){
            this.isReady = false;
            const res = await get(`/api/workshops/${id}`);
            this.workshop = res.workshop;
            this.isReady = true;
            return res.workshop;
        },
        async getWorkshops() {
            this.isReady = false;
            const res = await get(`/api/workshops`, true);
            this.workshops = res.workshops;
            this.isReady = true;
        },
        async updateWorkshop() {
            const res = await patch(`/api/workshops/${this.workshop.id}`, this.workshop);
            this.workshop = res.workshop;
            // UPDATE WORKSHOP IN WORKSHOPS ARRAY
            return res.workshop;
        },
        async getMyWorkshops() {
            this.isReady = false;
            const res = await get(`/api/myWorkshops`);
            this.myWorkshops = res.workshops;
            this.isReady = true;
        },
        async updateImage(data) {
            this.imageLoading = true;
            let formData = new FormData()
            formData.append('poster', data.file);
            const res = await post(`/api/workshops/${this.workshop.id}/poster/${data.language}`, formData);
            this.workshop = res.workshop;
            this.imageLoading = false;
            return res.workshop;
        },
        async deleteImage(language){
            this.imageLoading = true;
            const res = await del(`/api/workshops/${this.workshop.id}/poster/${language}`, {});
            this.workshop = res.workshop;
            this.imageLoading = false;
            return res.workshop;
        },
        async archiveWorkshop() {
            const res = await del(`/api/workshops/${this.workshop.id}/archive`, {});
            return res;
        },
        async deleteWorkshop(){
            const res = await del(`/api/workshops/${this.workshop.id}`, {});
            return res;
        },
        async adminGetAllWorkshops(){
            this.isReady = false;
            const res = await get(`/api/admin/workshops`);
            this.workshops = res.workshops;
            this.isReady = true;
        },
        async applyWorkshop(data){
            const res = await post(`/api/workshops/${this.workshop.id}/apply`, data);
            this.workshop = res.workshop;
        },
        async withdrawWorkshop(){
            const res = await post(`/api/workshops/${this.workshop.id}/withdraw`);
            this.workshop = res.workshop;
        },
        async editApplicantName(data){
            console.log(data.name);
            await post(`/api/workshops/${this.workshop.id}/editApplicantName/${data.id}`, {name: data.name});
        }
    }
});