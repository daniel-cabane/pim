import { defineStore } from 'pinia';
import useAPI from '@/composables/useAPI';

const { get, post, patch, del } = useAPI();

export const usePostStore = defineStore({
    id: 'post',
    state: () => ({
        posts: [],
        submittedPosts: [],
        myPosts: [],
        post: {},
        isReady: false,
        isLoading: false,
        statusLoading: false
    }),
    actions: {
        async createPost(newPost){
            const res = await post('/api/posts', newPost);
            return res.post;
        },
        async getPosts(){
            this.isReady = false;
            const res = await get('/api/posts', true);
            this.posts = res.posts;
            this.isReady = true;
        },
        async getMyPosts() {
            this.isReady = false;
            const res = await get('/api/myPosts', true);
            this.myPosts = res.posts;
            this.isReady = true;
        },
        async getPost(slug) {
            this.isReady = false;
            const res = await get(`/api/posts/${slug}`, true);
            this.post = res.post;
            this.isReady = true;
            return res.post;
        },
        async adminGetposts() {
            this.isReady = false;
            const res = await get(`/api/admin/posts/`, true);
            this.posts = res.publishedPosts;
            this.submittedPosts = res.submittedPosts;
            this.isReady = true;
        },
        async updatePost() {
            this.isLoading = true;
            const res = await patch(`/api/posts/${this.post.slug}`, this.post, false);
            this.post = res.post;
            window.history.replaceState({}, '', `/posts/${res.post.slug}/edit`);
            this.isLoading = false;
            return res.post;
        },
        async updatePostStatus(status) {
            this.statusLoading = true;
            const res = await patch(`/api/posts/${this.post.slug}/status`, { status }, false);
            this.post = res.post;
            this.statusLoading = false;
        },
        async deletePost(slug) {
            const res = await del(`/api/posts/${slug}`, true);
            return res;
        }
    }
});