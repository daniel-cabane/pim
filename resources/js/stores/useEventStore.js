import { defineStore } from 'pinia';
import useAPI from '@/composables/useAPI';

const { get, post, patch, del } = useAPI();

export const useEventStore = defineStore({
    id: 'event',
    state: () => ({
        sessiona: [],
        session: {},
        holidays: [],
        holiday: {},
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
            console.log(this.holidays);
            this.isLoading = false;
            return res.id;
        }
    }
});