import { defineStore } from 'pinia';
import useAPI from '@/composables/useAPI';

const { get, post, patch, del, isLoading } = useAPI();

export const useTournamentStore = defineStore({
    id: 'tournament',
    state: () => ({
        tournaments: [],
        tournament: {},
        isReady: false,
    }),
    actions: {
        async createTournament(newTournament) {
            const res = await post('/api/tournaments', newTournament);
            this.tournament = res;
            return res;
        },
        async getTournaments() {
            this.isReady = false;
            const res = await get('/api/tournaments', true);
            this.tournaments = res.data;
            this.isReady = true;
        },
        async getTournament(slug) {
            this.isReady = false;
            const res = await get(`/api/tournaments/${slug}`);
            this.tournament = res;
            this.isReady = true;
            return res;
        },
        async updateTournament(slug, data) {
            const res = await patch(`/api/tournaments/${slug}`, data);
            this.tournament = res.tournament;
            return res;
        },
        async deleteTournament(slug) {
            const res = await del(`/api/tournaments/${slug}`, {});
            return res;
        },
        async joinTournament(slug, data = {}) {
            const res = await post(`/api/tournaments/${slug}/join`, data);
            this.tournament = res;
            return res;
        },
        async leaveTournament(slug) {
            const res = await post(`/api/tournaments/${slug}/leave`, {});
            this.tournament = res;
            return res;
        },
        async startTournament(slug) {
            const res = await post(`/api/tournaments/${slug}/start`, {});
            this.tournament = res;
            return res;
        },
        async getTournamentStandings(slug) {
            const res = await get(`/api/tournaments/${slug}/standings`);
            return res;
        },
        async getTournamentRounds(slug) {
            const res = await get(`/api/tournaments/${slug}/rounds`);
            return res;
        },
        async createNextRound(slug) {
            const res = await post(`/api/tournaments/${slug}/next-round`, {});
            return res;
        },
        async searchUsers(slug, query) {
            const res = await get(`/api/tournaments/${slug}/search-users?query=${encodeURIComponent(query)}`);
            return res;
        },
        async getOrganisers(slug) {
            const res = await get(`/api/tournaments/${slug}/organisers`);
            return res;
        },
        async addOrganiser(slug, userId) {
            const res = await post(`/api/tournaments/${slug}/organisers`, { user_id: userId });
            return res;
        },
        async removeOrganiser(slug, userId) {
            const res = await del(`/api/tournaments/${slug}/organisers/${userId}`);
            return res;
        },
        async addPlayer(slug, userId) {
            const res = await post(`/api/tournaments/${slug}/players`, { user_id: userId });
            if(res.tournament){
                this.tournament = res.tournament;
                console.log(res.tournament);
            }
            return res;
        },
        async removePlayer(slug, userId) {
            const res = await del(`/api/tournaments/${slug}/players/${userId}`);
            if(res.tournament){
                this.tournament = res.tournament;
            }
            return res;
        },
        async banPlayer(slug, userId, isBanned) {
            const res = await patch(`/api/tournaments/${slug}/players/${userId}`, { banned: isBanned });
            if(res.tournament){
                this.tournament = res.tournament;
            }
            return res;
        },
        async editPlayerRating(player) {
            const res = await patch(`/api/tournaments/${this.tournament.slug}/players/${player.id}/rating`, {rating: player.pivot.rating});
            if(res.tournament){
                this.tournament = res.tournament;
            }
            return res;
        }
    },
    getters: {
        isLoading: () => {
            return isLoading.value;
        },
    }
});
