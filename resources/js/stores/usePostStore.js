import { defineStore } from 'pinia';
import useAPI from '@/composables/useAPI';

const { get, post, patch, del } = useAPI();

export const usePostStore = defineStore({
    id: 'post',
    state: () => ({
        posts: [],
        myPosts: [],
        post: {}
    }),
    actions: {
        async createPost(newPost){
            const res = await post('/api/posts', newPost);
            return res.post;
        },
        async getPosts(){
            const res = await get('/api/posts', true);
            this.posts = res.posts;
        },
        async getMyPosts() {
            const res = await get('/api/myPosts', true);
            this.myPosts = res.posts;
        },
        async getPost(slug) {
            const res = await get(`/api/posts/${slug}`, true);
            this.post = res.post;
            return res.post;
        },
        async updatePost(post, publish) {
            const res = await patch(`/api/posts/${post.slug}`, {publish, ...post}, false);
            this.post = res.post;
            return res.post;
        },
        async deletePost(slug) {
            const res = await del(`/api/posts/${slug}`, true);
            return res;
        }
    }
});