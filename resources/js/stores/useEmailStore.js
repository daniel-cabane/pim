import { defineStore } from 'pinia';
import useAPI from '@/composables/useAPI';

const { get, post, patch, del } = useAPI();

export const useEmailStore = defineStore({
    id: 'email',
    state: () => ({
        isReady: false,
        isLoading: false,
        email: {},
        emails: [],
        preview: ''
    }),
    actions: {
        async getWorkshopEmails(id) {
            this.isReady = false;
            const res = await get(`/api/workshops/${id}/emails`);
            this.emails = res.emails;
            this.isReady = true;
        },
        async updateEmail(email) {
            this.isLoading = true;
            const res = await patch(`/api/emails/${email.id}`, email);
            this.emails = this.emails.map(e => e.id == res.email.id ? res.email : e);
            this.isLoading = false;
        },
        async updateEmailSchedule(id, dateTime){
            this.isLoading = true;
            const res = await patch(`/api/emails/${id}/schedule`, { dateTime });
            this.emails = this.emails.map(e => e.id == res.email.id ? res.email : e);
            this.isLoading = false;
        },
        async deleteEmail(id) {
            console.log(`uncomment and test ${id}`);
            // <============================================================== UNCOMMENT AND TEST (ROUTE AND CONTROLLER METHOD DONE)
            // this.isLoading = true;
            // const res = await del(`/api/emails/${id}`);
            // this.emails = this.emails.filter(e => e.id != res.email.id);
            // this.isLoading = false;
        },
        async previewMail(id) {
            this.isLoading = true;
            const res = await get(`/api/emails/${id}/preview`);
            this.preview = res.preview;
            this.isLoading = false;
        },
        async sendEmail(id) {
            console.log(id);
            const res = await post(`/api/emails/${id}/send`);
            console.log(res.email);
            this.emails = this.emails.map(e => e.id == res.email.id ? res.email : e);
            this.isLoading = false;
        }
    }
});