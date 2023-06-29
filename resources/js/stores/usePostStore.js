import { defineStore } from 'pinia';
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
        async updatePost(publish) {
            this.loading = true;
            try {
                const res = await axios.patch(`/api/posts/${this.post.slug}`, {publish, ...this.post})
                this.post = res.data;
                const text = publish ? 'Post published' : 'Post updated';
                this.addAlert({text, type: 'success'});
            } catch (err) {
                this.addAlert({ text: err.response.data.message, type: 'error' });
            }
            this.loading = false;

        },
        async deletePost(slug) {
            this.loading = true;
            try {
                const res = await axios.delete(`/api/posts/${slug}`);
                return res;
            } catch (err) {
                console.log(err);
                this.addAlert({ text: err.response.data.message, type: 'error' });
            }
            this.loading = false;
        }
    }
});