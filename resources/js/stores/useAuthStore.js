import { defineStore } from 'pinia';
import useNotifications from '@/composables/useNotifications';

const { addNotification } = useNotifications();

export const useAuthStore = defineStore({
    id: 'user',
    state: () => ({
        user: null,
        messages: [],
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
        },
        async updatePreferences(data) {
            this.loading = true;
            try {
                const res = await axios.patch('/api/userinfo/preferences', data);
                this.loading = false;
                this.user = res.data.user;
            } catch (err) {
                addNotification({ text: err.response.data.message, type: 'error' });
                this.loading = false;
            }
        },
        removeSurvey(id) {
            this.user.open_surveys = this.user.open_surveys.filter(s => s.id != id);
        },
        async msgToAdmin(msg) {
            this.loading = true;
            try {
                const res = await axios.post('/api/adminmsg', { msg: msg });
                this.loading = false;
                addNotification({ text: res.data.message, type: 'success' });
            } catch (err) {
                addNotification({ text: err.response.data.message, type: 'error' });
                this.loading = false;
            }
        },
        async getMessages() {
            this.loading = true;
            try {
                const res = await axios.get('/api/admin/messages');
                this.messages = res.data.messages;
                console.log(this.messages);
                this.loading = false;
            } catch (err) {
                addNotification({ text: err.response.data.message, type: 'error' });
                this.loading = false;
            }
        },
        async updateMessageStatus(id, status) {
            this.loading = true;
            try {
                const res = await axios.patch(`/api/admin/msg/${id}/status`, { status: status });
                if(res.data.msg){
                    this.messages = this.messages.map(m => m.id == res.data.msg.id ? res.data.msg : m);
                    this.user.unread_messages = this.user.unread_messages.map(m => m.id == res.data.msg.id ? res.data.msg : m);
                    if(res.data.msg.status == 'read'){
                        this.user.unread_messages = this.user.unread_messages.filter(m => m.id != res.data.msg.id);
                    }
                }
                this.loading = false;
                addNotification({ text: res.data.message, type: 'success' });
            } catch (err) {
                addNotification({ text: err.response.data.message, type: 'error' });
                this.loading = false;
            }
        },
        async deleteMessage(id) {
            this.loading = true;
            try {
                const res = await axios.delete(`/api/admin/msg/${id}`);
                if(res.data.id){
                    this.messages = this.messages.filter(m => m.id != res.data.id);
                    this.user.unread_messages = this.user.unread_messages.filter(m => m.id != res.data.id);
                }
                this.loading = false;
                addNotification({ text: res.data.message, type: 'info' });
            } catch (err) {
                addNotification({ text: err.response.data.message, type: 'error' });
                this.loading = false;
            }
        },
        async updateDetails(details) {
            this.loading = true;
            try {
                const res = await axios.patch('/api/userinfo/details', details);
                addNotification({ text: res.data.message, type: 'success' });
                this.loading = false;
            } catch (err) {
                addNotification({ text: err.response.data.message, type: 'error' });
                this.loading = false;
            }
        },
        async fetchMyActivity() {
            try {
                const res = await axios.get('/api/userinfo/myActivity');
                this.user.activity = res.data.activity
                return res.data;
            } catch (err) {
                addNotification({ text: err.response.data.message, type: 'error' });
            }
        }
    }
});