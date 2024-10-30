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
            console.log(res);
            this.terms = res.terms;
            this.isLoading = false;
            this.isReady = true;
        },
        async updateTeachersHours() {
            this.isLoading = true;
            await post(`/api/hod/teacherHours`, { teachers: this.teachers.map(t => ({ id: t.id, hours: t.preferences.hoursDuePerWeek }))});
            this.isLoading = false;
        }
    }
});