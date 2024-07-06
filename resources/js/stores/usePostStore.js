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
        totalNbPosts: 0,
        isReady: false,
        isLoading: false,
        statusLoading: false,
        imageLoading: false
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
        async getPublishedPosts(skip = 0, take = 10) {
            this.isReady = false;
            this.posts = [];
            const res = await get(`/api/posts/published?query=Laravel&skip=${skip}&take=${take}`, true);
            this.posts = res.posts;
            this.totalNbPosts = res.total;
            this.isReady = true;
        },
        async searchPosts(text){
            const res = await get(`/api/posts/search?query=Laravel&text=${text}`, true);
            this.posts = res.posts;
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
        async updateCoverImage(data){
            this.imageLoading = true;
            let formData = new FormData()
            formData.append('file', data.file);
            const res = await post(`/api/posts/${this.post.slug}/cover`, formData);
            this.post = res.post;
            this.imageLoading = false;
        },
        async uploadPostImage(formData) {
            return await post(`/api/posts/${this.post.slug}/image`, formData);
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