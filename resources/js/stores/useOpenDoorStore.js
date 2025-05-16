import { defineStore } from 'pinia';
import useAPI from '@/composables/useAPI';

const { get, post, patch, del, isLoading } = useAPI();

export const useOpenDoorStore = defineStore({
    id: 'openDoor',
    state: () => ({
        visitor: {},
        visits: [],
        message: '',
        alert: null
    }),
    actions: {
        async visit(tagNb) {
            const res = await post('/api/opendoors/visit', { tagNb });
            this.visitor = res;
        },
        async register() {
            const res = await post(`/api/opendoors/${this.visitor.visitId}/register`, this.visitor);
        },
        async getRecentVisits() {
            const res = await get('/api/visits/recent');
            this.visits = res.visits;
        },
        async deleteVisit(id) {
            const res = await del(`/api/visits/${id}`, {});
            this.visits = this.visits.filter(v => v.id != id);
        },
        async editVisitByEmail(data) {
            const res = await patch(`/api/visits/${data.id}/byEmail`, { email: data.email });
            this.visits = this.visits.map(v => v.id == res.visit.id ? res.visit : v);
        },
        async editVisitByTagNb(data) {
            const res = await patch(`/api/visits/${data.id}/byTagNb`, { tagNb: data.tagNb });
            this.visits = this.visits.map(v => v.id == res.visit.id ? res.visit : v);
        },
        async newVisit(data) {
            const res = await post('/api/visits/new', data);
            this.visits.unshift(res.visit);
        },
        async getVisitsToReview() {
            const res = await get('/api/admin/visits/toReview');
            this.visits = res.visits;
        },
        async findMatch(data) {
            const res = await get(`/api/admin/visits/match?query=Laravel&email=${data.email}&name=${data.name}`);
            return res.matches;
        },
        async confirmMatch(visitId, matchId) {
            const res = await post(`/api/admin/visits/${visitId}/confirmMatch/${matchId}`);
            this.alert = res.alert;
            this.visits = res.visits;
        }
    },
    getters: {
        isLoading: () => {
            return isLoading.value;
        },
        visitsBySession: (state) => {
            const groupedVisits = state.visits.reduce((acc, visit) => {
                if(visit.session){
                    const key = visit.session.id;
                    if (!acc[key]) {
                        acc[key] = [];
                    }
                    acc[key].push(visit);
                }
                return acc;
            }, {});

            for(const key in groupedVisits) {
                groupedVisits[key].sort((a, b) => {
                    if (a.session.date === b.session.date) {
                        return a.session.start.localeCompare(b.session.start);
                    }
                    return a.session.date.localeCompare(b.session.date);
                });
            }

            return Object.values(groupedVisits).reverse();
        }
    }
});