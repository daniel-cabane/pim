<template>
    <v-container style="display:flex;">
        <div style="flex:1">
            <div class="text-h5 font-weight-thin text-grey mb-3 text-center">
                {{ $t("The Pole's Blog") }}
            </div>
            <post-card v-for="post in posts" :key="post.id" :post="post"/>
        </div>
        <div style="width:350px" class="d-none d-md-flex pl-4">
            <div style="width:100%;" class="text-h5 font-weight-thin text-grey mb-3 text-center">
                {{ $t('Upcoming') }}
            </div>
        </div>
    </v-container>
</template>
<script setup>
    import { usePostStore } from '@/stores/usePostStore';
    import { storeToRefs } from 'pinia';

    const postStore = usePostStore();
    const { getPosts } = postStore;
    const { posts } = storeToRefs(postStore);

    getPosts();
</script>