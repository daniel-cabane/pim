import { defineStore } from 'pinia';
import useAPI from '@/composables/useAPI';

const { get, post, patch, del, isLoading } = useAPI();

export const useHodStore = defineStore({
    id: 'hod',
    state: () => ({
        isReady: false,
        workshops: [],
        teachers: [],
        terms: [],
        missions: []
    }),
    actions: {
        async hodIndex() {
            this.isReady = false;
            const res = await get(`/api/hod/index`, true);
            console.log(res.teachers);
            this.workshops = res.workshops;
            this.teachers = res.teachers;
            this.isReady = true;
        },
        async updateTeachersHours() {
            await post(`/api/hod/teacherHours`, { teachers: this.teachers.map(t => ({ id: t.id, hours: t.preferences.hoursDuePerWeek }))});
        },
        async getMissions() {
            const res = await get('/api/hod/missions');
            console.log(res.missions);
            this.missions = res.missions;
        },
        async addMission(data) {
            const res = await post('/api/hod/mission', data);
            if(res && res.mission){
                this.missions.push(res.mission);
                return true;
            }
            return false;
        },
        async deleteMission(id) {
            const res = await del(`/api/hod/missions/${id}`);
            this.missions = this.missions.filter(m => m.id != id);
            return true;
        },
        async updateMission(mission) {
            const res = await patch(`/api/hod/missions/${mission.id}`, mission);
            this.missions = this.missions.filter(m => m.id == res.mission.id ? res.mission : m);
            return true;
        }
    },
    getters: {
        isLoading: () => {
            return isLoading.value;
        },
    }
});