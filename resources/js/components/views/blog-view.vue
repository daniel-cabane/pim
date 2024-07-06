<template>
    <v-container style="display:flex;gap: 15px;">
        blog
    </v-container>
</template>
<script setup>
    import { usePostStore } from '@/stores/usePostStore';
    import { storeToRefs } from 'pinia';

    const postStore = usePostStore();
    const { getPublishedPosts } = postStore;
    const { posts } = storeToRefs(postStore);
    getPublishedPosts();
    
</script>