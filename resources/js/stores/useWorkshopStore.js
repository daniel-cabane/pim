import { defineStore } from 'pinia';
// import { useAlertStore } from '@/stores/useAlertStore';
// import { useLoadingStore } from '@/stores/useLoadingStore';
import useAPI from '@/composables/useAPI';

const { get, post, patch, del } = useAPI();

export const useWorkshopStore = defineStore({
    id: 'workshop',
    state: () => ({
        workshops: [],
        myWorkshops: [],
        themes: [],
        workshop: {},
        loading: false,
        themesLoading: false
    }),
    actions: {
        // addAlert(alert) {
        //     const alertStore = useAlertStore();
        //     const { addAlert } = alertStore;
        //     addAlert(alert);
        // },
        async getThemes() {
            this.themesLoading = true;
            this.themes = await get('/api/workshops/themes');
            this.themesLoading = false;
            // try {
            //     const res = await axios.get('/api/workshops/themes', {});
            //     this.themes = res.data;
            // } catch (err) {
            //     this.addAlert({ text: err.response.data.message, type: 'error' });
            // }
            // this.themesLoading = false;
        },
        async createWorkshop(form) {
            this.loading = true;
            this.workshop = await post('/api/workshops', form, false);
            this.loading = false;
            // try {
            //     const res = await axios.post('/api/workshops', form);
            //     this.workshop = res.data;
            //     this.addAlert({text: 'Workshop created', type: 'success'});
            //     this.loading = false;
            //     return this.workshop;
            // } catch (err) {
            //     console.log(err);
            //     this.addAlert({ text: err.response.data.message, type: 'error' });
            // }
            // this.loading = false;
        },
        async getSingleWorkshops(id){
            if(this.workshop.id != id){
                this.loading = true;
                this.workshop = await get(`/api/workshops/${id}`);
                this.loading = false;
                // try {
                //     const res = await axios.get(`/api/workshops/${id}`, {});
                //     this.workshop = res.data;
                // } catch (err) {
                //     this.addAlert({ text: err.response.data.message, type: 'error' });
                // }
                // this.loading = false;
            }

            return this.workshop;
        },
        async getWorkshops() {
            this.loading = true;
            this.workshops = await get(`/api/workshops`);
            this.loading = false;
            // try {
            //     const res = await axios.get('/api/workshops/', {});
            //     this.workshops = res.data;
            // } catch (err) {
            //     this.addAlert({ text: err.response.data.message, type: 'error' });
            // }
            // this.loading = false;
        },
        async getMyWorkshops() {
            console.log('my workshops');
        }
    }
});