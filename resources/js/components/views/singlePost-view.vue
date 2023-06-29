<template>
    <v-container>
        <post-display :post="post" :editing="false"/>
    </v-container>
</template>
<script setup>
    import { usePostStore } from '@/stores/usePostStore';
    import { storeToRefs } from 'pinia';
    import { useRoute } from 'vue-router';

    const route = useRoute();
    const postStore = usePostStore();
    const { getFullPost } = postStore;
    const { post } = storeToRefs(postStore);

    getFullPost(route.params.slug);
</script>