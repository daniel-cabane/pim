import { defineStore } from 'pinia';
import useAPI from '@/composables/useAPI';

const { get, post, patch, del, isLoading } = useAPI();

export const useEventStore = defineStore({
    id: 'event',
    state: () => ({
        session: {},
        holidays: [],
        holiday: {},
        openDoors: [],
        openDoor: {},
        weeks: [],
        months: [],
        boundaries: {},
        terms: [],
        events: [],
        isReady: false,
        // isLoading: false
    }),
    actions: {
        async createHoliday(data) {
            //this.isLoading = true;
            const res = await post(`/api/admin/holiday`, data);
            if(res.holiday){
                this.holidays.push(res.holiday);
            }
            //this.isLoading = false;
            return res.holiday;
        },
        async getHolidays(){
            const res = await get(`/api/holidays`, true);
            this.holidays = res.holidays
        },
        async editHoliday(data){
            //this.isLoading = true;
            const res = await patch(`/api/admin/holidays/${data.id}`, data);
            if (res.holiday) {
                this.holidays = this.holidays.map(h => h.id == res.holiday.id ? res.holiday : h);
            }
            //this.isLoading = false;
            return res.holiday;
        },
        async deleteHoliday(data){
            //this.isLoading = true;
            const res = await del(`/api/admin/holidays/${data.id}`);
            if (res.id) {
                this.holidays = this.holidays.filter(h => h.id != res.id);
            }
            //this.isLoading = false;
            return res.id;
        },
        async getOpenDoors()
        {
            const res = await get(`/api/openDoors`, true);
            this.openDoors = res.openDoors
        },
        async createOpenDoor(data){
            //this.isLoading = true;
            const res = await post(`/api/admin/openDoor`, data);
            this.openDoors = res.openDoors;
            //this.isLoading = false;
            return res.openDoor;
        },
        async updateOpenDoor(data){
            //this.isLoading = true;
            const res = await patch(`/api/admin/openDoors/${data.id}`, data);
            this.openDoors = res.openDoors;
            //this.isLoading = false;
            return res.openDoors;
        },
        async deleteOpenDoor(data) {
            //this.isLoading = true;
            const res = await post(`/api/admin/openDoors/${data.id}/delete`, data);
            this.openDoors = res.openDoors;
            //this.isLoading = false;
            return res.openDoors;
        },
        async getCurrentMonth(){
            this.isReady = false;
            const currentDate = new Date();
            let date = new Date(currentDate.getFullYear(), currentDate.getMonth() - 1, 1);
            let formattedDate = date.toLocaleString('en-US', { year: 'numeric', month: '2-digit', day: '2-digit' });
            const res = await get(`/api/calendar/getMonths?query=Laravel&org=${formattedDate}&backward=0&forward=1`, true);
            this.weeks = res.weeks;
            this.months = res.months;
            this.isReady = true;
            //this.isLoading = true;
            date = new Date();
            formattedDate = date.toLocaleString('en-US', {year: 'numeric', month: '2-digit', day: '2-digit'});
            const adj = await get(`/api/calendar/getMonths?query=Laravel&org=${formattedDate}&backward=2&forward=3`);
            this.weeks = adj.weeks;
            this.months = adj.months;
            //this.isLoading = false;
        },
        async getMoredMonths(nb, date){
            //this.isLoading = true;
            const backward = nb < 0 ? -nb-1 : 0;
            const forward = nb > 0 ? nb-1 : 0;
            const res = await get(`/api/calendar/getMonths?query=Laravel&org=${date}&backward=${backward}&forward=${forward}`);
            this.months = nb > 0 ? [...this.months, ...res.months] : [...res.months, ...this.months];
            res.weeks.forEach(w => {
                if(!this.weeks.some(w2 => w2.nb == w.nb && w2.year == w.year)){
                    this.weeks.push(w);
                }
            });
            //this.isLoading = false;
        },
        async getTerms() {
            //this.isLoading = true;
            const res = await get(`/api/terms`);
            this.terms = res.terms;
            //this.isLoading = false;
        },
        async createTerm(data) {
            //this.isLoading = true;
            const res = await post(`/api/admin/term`, data);
            this.terms = res.terms;
            //this.isLoading = false;
        },
        async editTerm(data) {
            const res = await patch(`/api/admin/term/${data.id}`, {start: data.start_date, finish: data.finish_date});
            this.terms = res.terms;
        },
        async deleteTerm(id) {
            //this.isLoading = true;
            const res = await del(`/api/admin/term/${id}`);
            console.log(res.terms);
            this.terms = res.terms;
            //this.isLoading = false;
        },
        async getUpcomingEvents() {
            const res = await get(`/api/events/upcoming`,true);
            this.events = res.events;
        }
    },
    getters: {
        isLoading: () => {
            return isLoading.value;
        },
    }
});