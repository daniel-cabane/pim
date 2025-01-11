import { defineStore } from 'pinia';
import useAPI from '@/composables/useAPI';

const { get, post, patch, del, isLoading } = useAPI();

export const useHodStore = defineStore({
    id: 'hod',
    state: () => ({
        isReady: false,
        workshops: [],
        teachers: [],
        terms: []
    }),
    actions: {
        async hodIndex() {
            this.isReady = false;
            const res = await get(`/api/hod/index`, true);
            this.workshops = res.workshops;
            this.teachers = res.teachers;
            this.isReady = true;
        },
        async updateTeachersHours() {
            this.isLoading = true;
            await post(`/api/hod/teacherHours`, { teachers: this.teachers.map(t => ({ id: t.id, hours: t.preferences.hoursDuePerWeek }))});
            this.isLoading = false;
        }
    },
    getters: {
        isLoading: () => {
            return isLoading.value;
        },
    }
});