import { defineStore } from 'pinia';
import useAPI from '@/composables/useAPI';

const { get, post, patch, del } = useAPI();

export const useHodStore = defineStore({
    id: 'hod',
    state: () => ({
        isReady: false,
        isLoading: false,
        workshops: [],
        teachers: [],
        terms: []
    }),
    actions: {
        async hodIndex() {
            this.isLoading = true;
            this.isReady = false;
            const res = await get(`/api/hod/index`, true);
            this.workshops = res.workshops;
            this.teachers = res.teachers;
            this.terms = res.terms;
            this.isLoading = false;
            this.isReady = true;
        }
    }
});