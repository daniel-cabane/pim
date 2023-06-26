import { defineStore } from 'pinia';
import { useAlertStore } from '@/stores/useAlertStore';

export const useAuthStore = defineStore({
    id: 'user',
    state: () => ({
        user: null,
        loading: false
    }),
    actions: {
        addAlert(alert){
            const alertStore = useAlertStore();
            const { addAlert } = alertStore;
            addAlert(alert);
        },
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
                    this.addAlert({ text: 'Registration successful', type: 'success' });
                    this.loading = false;
                } else {
                    this.addAlert({text: 'We encountered an error, please try again', type: 'error'});
                    this.loading = false;
                }
            } catch(err) {
                this.addAlert({ text: err.response.data.message, type: 'error' });
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
                    this.addAlert({ text: "You're signed in !", type: 'success' });
                } else {
                    this.addAlert({ text: 'We encountered an error, please try again', type: 'error' });
                    this.loading = false;
                }
            } catch(err) {
                this.addAlert({ text: err.response.data.message, type: 'error' });
                this.loading = false;
            }
        },
        async fetchUser(){
            try {
                const res = await axios.get('/api/userinfo');
                console.log(res.data.user);
                return res.data.user;
            } catch (err) {
                this.addAlert({ text: err.response.data.message, type: 'error' });
                this.loading = false;
            }
        },
        async logout() {
            let res = await axios.post('logout');
            if(res.status == 204){
                this.user = null;
                this.addAlert({ text: 'Signed out !', type: 'info' });
            }
        },
        async resetPassword(form){
            this.loading = true;
            try {
                const res = await axios.post('/forgot-password', form);
                if(res.status == 200){
                    this.addAlert({ text: 'An email has been sent, check your inbox', type: 'info' });
                    this.loading = false;
                } else {
                    this.addAlert({ text: 'We encountered an error, please try again', type: 'error' });
                    this.loading = false;
                }
            } catch (err) {
                this.addAlert({ text: err.response.data.message, type: 'error' });
                this.loading = false;
            }
        }
    }
});