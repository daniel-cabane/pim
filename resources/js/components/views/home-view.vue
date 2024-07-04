<template>
    <v-container style="display:flex;gap: 15px;">
        <v-card flat color="surface" style="flex:1;box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.5);" class="mb-3 pa-4">
            <div class="text-h3 font-weight-thin text-captionColor mb-4">
                {{ $t("The Pole's Blog") }}
            </div>
            <div class="d-flex flex-wrap justify-space-around">
                <post-home-card class="my-2" v-for="post in posts" :key="post.id" :post="post" />
            </div>
        </v-card>
        <v-card flat width="350" color="surface" style="box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.5);" class="mb-3 pa-4">
            <div style="width:100%;" class="text-h5 font-weight-thin text-captionColor mb-3 text-center">
                {{ $t('Upcoming') }}
            </div>
        </v-card>
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