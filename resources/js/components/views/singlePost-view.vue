<template>
    <v-container>
        <post-display :post="post" v-if="isReady"/>
    </v-container>
</template>
<script setup>
    import { useRoute } from 'vue-router';
    import { usePostStore } from '@/stores/usePostStore';
    import { storeToRefs } from 'pinia';

    const route = useRoute();
    const postStore = usePostStore();
    const { getPost } = postStore;
    const { post, isReady } = storeToRefs(postStore)

    getPost(route.params.slug);
</script>