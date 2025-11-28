import { defineStore } from 'pinia';
import useAPI from '@/composables/useAPI';

const { get, post, patch, del, isLoading } = useAPI();

export const usePostStore = defineStore({
    id: 'post',
    state: () => ({
        posts: [],
        myPosts: [],
        post: {},
        // themes: [],
        totalNbPosts: 0,
        isReady: false,
        // isLoading: false,
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
            console.log(res.posts);
            this.isReady = true;
        },
        async getPublishedPosts(locale = null, skip = 0, take = 6) {
            this.isReady = false;
            const res = await get(`/api/posts/published?query=Laravel&skip=${skip}&take=${take}&locale=${locale}`, true);
            this.posts = res.posts;
            this.totalNbPosts = res.total;
            this.isReady = true;
        },
        async searchPosts(text){
            const res = await get(`/api/posts/search?query=Laravel&text=${text}`, true);
            this.posts = res.posts;
        },
        async getPost(slug, read = false) {
            this.isReady = false;
            const res = await get(`/api/posts/${slug}?query=Laravel&read=${read ? 1 :0}`, true);
            this.post = res.post;
            this.isReady = true;
            // console.log(`https://pim.fis.edu.hk${this.post?.cover?.url}`)
            return res.post;
        },
        async adminGetposts() {
            this.isReady = false;
            const res = await get(`/api/admin/posts/`, true);
            this.posts = res.posts;
            this.isReady = true;
        },
        async updateCoverImage(data){
            this.imageLoading = true;
            let formData = new FormData()
            formData.append('file', data.file);
            const res = await post(`/api/posts/${this.post.slug}/cover`, formData);
            this.post.cover = res.post.cover;
            this.imageLoading = false;
        },
        async uploadPostImage(formData) {
            return await post(`/api/posts/${this.post.slug}/image`, formData);
        },
        async updatePost() {
            // this.isLoading = true;
            const res = await patch(`/api/posts/${this.post.slug}`, this.post, false);
            this.post = res.post;
            window.history.replaceState({}, '', `/posts/${res.post.slug}/edit`);
            // this.isLoading = false;
            return res.post;
        },
        async updatePostStatus(status) {
            this.statusLoading = true;
            const res = await patch(`/api/posts/${this.post.slug}/status`, { status }, false);
            this.post.status = res.post.status;
            this.statusLoading = false;
        },
        async deletePost(slug) {
            const res = await del(`/api/posts/${slug}`, true);
            return res;
        },
        async createTranslation(title){
            // this.isLoading = true;
            const res = await post(`/api/posts/${this.post.slug}/translation`, { title });
            this.post.translationId = res.post.id;
            // this.isLoading = false;
            // return res.post;
        },
        async findTranslation(){
            // this.isLoading = true;
            const res = await get(`/api/posts/${this.post.slug}/translation`);
            this.post = res.post;
            // this.isLoading = false;
            return res.post;
        }
        // async getThemes() {
        //     const res = await get('/api/posts/themes');
        //     this.themes = res.themes;
        // },
    },
    getters: {
        isLoading: () => {
            return isLoading.value;
        },
    }
});