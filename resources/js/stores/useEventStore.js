import { defineStore } from 'pinia';
import useAPI from '@/composables/useAPI';

const { get, post, patch, del } = useAPI();

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
        isReady: false,
        isLoading: false
    }),
    actions: {
        async createHoliday(data) {
            this.isLoading = true;
            const res = await post(`/api/admin/holiday`, data);
            if(res.holiday){
                this.holidays.push(res.holiday);
            }
            this.isLoading = false;
            return res.holiday;
        },
        async getHolidays(){
            const res = await get(`/api/holidays`, true);
            this.holidays = res.holidays
        },
        async editHoliday(data){
            this.isLoading = true;
            const res = await patch(`/api/admin/holidays/${data.id}`, data);
            if (res.holiday) {
                this.holidays = this.holidays.map(h => h.id == res.holiday.id ? res.holiday : h);
            }
            this.isLoading = false;
            return res.holiday;
        },
        async deleteHoliday(data){
            this.isLoading = true;
            const res = await del(`/api/admin/holidays/${data.id}`);
            if (res.id) {
                this.holidays = this.holidays.filter(h => h.id != res.id);
            }
            this.isLoading = false;
            return res.id;
        },
        async getOpenDoors()
        {
            const res = await get(`/api/openDoors`, true);
            this.openDoors = res.openDoors
        },
        async createOpenDoor(data){
            this.isLoading = true;
            const res = await post(`/api/admin/openDoor`, data);
            this.openDoors = res.openDoors;
            this.isLoading = false;
            return res.openDoor;
        },
        async updateOpenDoor(data){
            this.isLoading = true;
            const res = await patch(`/api/admin/openDoors/${data.id}`, data);
            this.openDoors = res.openDoors;
            this.isLoading = false;
            return res.openDoors;
        },
        async deleteOpenDoor(data) {
            this.isLoading = true;
            const res = await post(`/api/admin/openDoors/${data.id}/delete`, data);
            this.openDoors = res.openDoors;
            this.isLoading = false;
            return res.openDoors;
        },
        async getCurrentMonth(){
            this.isReady = false;
            const currentDate = new Date();
            let date = new Date(currentDate.getFullYear(), currentDate.getMonth() - 1, 1);
            let formattedDate = date.toLocaleString('en-US', { year: 'numeric', month: '2-digit', day: '2-digit' });
            const res = await get(`/api/calendar/getMonths?query=Laravel&org=${formattedDate}&backward=0&forward=1`);
            this.weeks = res.weeks;
            this.months = res.months;
            this.isReady = true;
            this.isLoading = true;
            date = new Date();
            formattedDate = date.toLocaleString('en-US', {year: 'numeric', month: '2-digit', day: '2-digit'});
            const adj = await get(`/api/calendar/getMonths?query=Laravel&org=${formattedDate}&backward=2&forward=3`);
            this.weeks = adj.weeks;
            this.months = adj.months;
            console.log(this.months);
            this.isLoading = false;
        },
        async getMoredMonths(nb, date){
            this.isLoading = true;
            const backward = nb < 0 ? -nb+1 : 0;
            const forward = nb > 0 ? nb-1 : 0;
            console.log(date, nb);
            // const monthNb = nb > 0 ? Math.max(...this.months.map(m => parseInt(m.nb))) : Math.min(...this.months.map(m => parseInt(m.nb)));
            // const date = new Date(new Date().getFullYear(), monthNb, 1).toLocaleString('en-US', { year: 'numeric', month: '2-digit', day: '2-digit' });
            // const formattedDate = date.toLocaleString('en-US', { year: 'numeric', month: '2-digit', day: '2-digit' });
            const res = await get(`/api/calendar/getMonths?query=Laravel&org=${date}&backward=${backward}&forward=${forward}`);
            res.months.forEach(m => console.log(m.nb, m.year));
            this.months = [...res.months, ...this.months];
            res.weeks.forEach(w => {
                if(!this.weeks.some(w2 => w2.nb == w.nb && w2.year == w.year)){
                    this.weeks.push(w);
                }
            });
            // console.log(this.months);
            const test = [];
            this.months.forEach(m => test.push(`${m.year}-${m.nb}`));
            console.log(test.sort((a, b) => a - b));
            this.isLoading = false;
        }
    }
});