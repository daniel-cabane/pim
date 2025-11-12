<template>
    <v-container>
        <div class="pb-4">
            <back-btn />
        </div>
        <post-display :post="post" v-if="isReady" />
    </v-container>
</template>
<script setup>
    import { useRoute } from 'vue-router';
    import { usePostStore } from '@/stores/usePostStore';
    import { storeToRefs } from 'pinia';
    import { useSeoMeta } from '@unhead/vue'
    import { computed } from 'vue';

    const route = useRoute();
    const postStore = usePostStore();
    const { getPost } = postStore;
    const { post, isReady } = storeToRefs(postStore)

    getPost(route.params.slug, true);

    useSeoMeta({
        title: computed(() => post?.value.title),
        description: computed(() => post?.value.description),
        ogTitle: computed(() => post?.value.title),
        ogDescription: computed(() => post?.value.description),
        ogImage: computed(() => post?.value.cover?.url)
    });
</script>