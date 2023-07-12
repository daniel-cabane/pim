<template>
    <v-container>
        <post-card v-for="post in myPosts" :key="post.id" :post="post"/>
    </v-container>
</template>
<script setup>
    import { usePostStore } from '@/stores/usePostStore';
    import { storeToRefs } from 'pinia';

    const postStore = usePostStore();
    const { getMyPosts } = postStore;
    const { myPosts } = storeToRefs(postStore);

    getMyPosts();
</script>