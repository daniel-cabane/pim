import { defineStore } from 'pinia';
import useNotifications from '@/composables/useNotifications';

const { addNotification } = useNotifications();

export const useAuthStore = defineStore({
    id: 'user',
    state: () => ({
        user: null,
        loading: false
    }),
    actions: {
        defineUser(user){
            this.user = typeof user == 'object' ? user : JSON.parse(user);
        },
        async register(form) {
            this.loading = true;
            form.name = form.email.split('@')[0];
            try {
                const res = await axios.post('/register', form);
                if (res.status == 201) {
                    this.user = await this.fetchUser();
                    addNotification({ text: 'Registration successful', type: 'success' });
                    this.loading = false;
                } else {
                    addNotification({text: 'We encountered an error, please try again', type: 'error'});
                    this.loading = false;
                }
            } catch(err) {
                addNotification({ text: err.response.data.message, type: 'error' });
                this.loading = false;
            }
        },
        async login(form) {
            this.loading = true;
            try {
                const res = await axios.post('/login', form);
                if (res.status == 200) {
                    this.user = await this.fetchUser();
                    this.loading = false;
                    addNotification({ text: "You're signed in !", type: 'success' });
                    window.location.reload();
                } else {
                    addNotification({ text: 'We encountered an error, please try again', type: 'error' });
                    this.loading = false;
                }
            } catch(err) {
                addNotification({ text: err.response.data.message, type: 'error' });
                this.loading = false;
            }
        },
        async fetchUser(){
            try {
                const res = await axios.get('/api/userinfo');
                return res.data.user;
            } catch (err) {
                addNotification({ text: err.response.data.message, type: 'error' });
                this.loading = false;
            }
        },
        async logout() {
            let res = await axios.post('/logout');
            if(res.status == 204){
                this.user = null;
                addNotification({ text: 'Signed out !', type: 'info' });
                window.location.replace('/');
            }
        },
        async resetPassword(form){
            this.loading = true;
            try {
                const res = await axios.post('/forgot-password', form);
                if(res.status == 200){
                    addNotification({ text: 'An email has been sent, check your inbox', type: 'info' });
                    this.loading = false;
                } else {
                    addNotification({ text: 'We encountered an error, please try again', type: 'error' });
                    this.loading = false;
                }
            } catch (err) {
                addNotification({ text: err.response.data.message, type: 'error' });
                this.loading = false;
            }
        },
        async getTeachers(){
            try {
                const res = await axios.get('/api/userinfo/teachers');
                return res.data;
            } catch (err) {
                addNotification({ text: err.response.data.message, type: 'error' });
                this.loading = false;
            }
        }
    }
});