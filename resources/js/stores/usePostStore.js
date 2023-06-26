import { defineStore } from 'pinia';
import { useAuthStore } from '@/stores/useAuthStore';
import { useAlertStore } from '@/stores/useAlertStore';
import { useLoadingStore } from '@/stores/useLoadingStore';

export const usePostStore = defineStore({
    id: 'post',
    state: () => ({
        posts: [],
        myPosts: [],
        post: {},
        loading: false
    }),
    actions: {
        addAlert(alert) {
            const alertStore = useAlertStore();
            const { addAlert } = alertStore;
            addAlert(alert);
        },
        async getPosts(){
            const loadingStore = useLoadingStore();
            const { addProcess, removeProcess } = loadingStore;
            addProcess('getPosts');
            try {
                const res = await axios.get('/api/posts');
                console.log(res.data);
                this.posts = res.data.posts;
            } catch (err) {
                this.addAlert({ text: err.response.data.message, type: 'error' });
            }
            removeProcess('getPosts');
        },
        async getMyPosts() {
            const loadingStore = useLoadingStore();
            const { addProcess, removeProcess } = loadingStore;
            addProcess('getPosts');
            try {
                const res = await axios.get('/api/myPosts');
                this.myPosts = res.data.posts;
            } catch (err) {
                this.addAlert({ text: err.response.data.message, type: 'error' });
            }
            removeProcess('getPosts');
        },
        async getFullPost(slug) {
            const loadingStore = useLoadingStore();
            const { addProcess, removeProcess } = loadingStore;
            addProcess('getPosts');
            try {
                const res = await axios.get(`/api/posts/${slug}`);
                this.post = res.data.post;
            } catch (err) {
                this.addAlert({ text: err.response.data.message, type: 'error' });
            }
            removeProcess('getPosts');
        },
        async updatePost() {
            this.loading = true;
            try {
                const res = await axios.patch(`/api/posts/${this.post.slug}`, this.post)
                console.log(res.data);
                this.addAlert({text: 'Post updated', type: 'success'});
            } catch (err) {
                this.addAlert({ text: err.response.data.message, type: 'error' });
            }
            this.loading = false;

        }
    }
});