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
        isReady: false
    }),
    actions: {
        async getThemes() {
            this.themes = await get('/api/workshops/themes');
        },
        async createWorkshop(newWorkshop) {
            this.isReady = false;
            const res = await post('/api/workshops', newWorkshop, false);
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
            const res = await get(`/api/workshops`);
            this.workshops = res.workshops;
            this.isReady = true;
        },
        async updateWorkshop() {
            const res = await patch(`/api/workshops/${this.workshop.id}`, this.workshop, false);
            this.workshop = res.workshop;
            return res.workshop;
        },
        async getMyWorkshops() {
            this.isReady = false;
            const res = await get(`/api/myWorkshops`);
            this.myWorkshops = res.workshops;
            this.isReady = true;
        }
    }
});