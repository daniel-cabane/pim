<template>
    <v-container>
        <div class="pb-4">
            <back-btn />
        </div>
        <div class="d-flex flex-column flex-md-row ga-8">
            <div>
                <post-display :post="post" v-if="isReady" />
            </div>
            <div class="d-flex flex-wrap flex-row flex-md-column justify-center ga-4" v-if="isReady && similarPosts.length">
                <post-home-card
                    v-for="s in similarPosts"
                    :key="s.id"
                    :post="s"
                    class="mb-4"
                />
            </div>
        </div>
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
    const { post, isReady, similarPosts } = storeToRefs(postStore)

    getPost(route.params.slug, true);

    useSeoMeta({
        title: computed(() => post?.value.title),
        description: computed(() => post?.value.description),
        ogTitle: computed(() => post?.value.title),
        ogDescription: computed(() => post?.value.description),
        ogImage: computed(() => post ? `https://pim.fis.edu.hk${post?.value.cover?.url}` : null )
    });
</script>