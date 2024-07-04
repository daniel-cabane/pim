<template>
    <v-card width="100%" class="pa-3">
        <post-header :post="post"/>
        <v-divider class="my-2"/>
        <div class="mt-4" v-html="post.post"/>
        <div class="d-flex align-center mt-4">
            <back-btn/>
            <v-spacer/>
            <v-btn icon="mdi-pencil" color="primary" @click="goEditPost"/>
        </div>
    </v-card>
</template>
<script setup>
    import { useRouter } from 'vue-router';

    const props = defineProps({ post: Object });

    const router = useRouter();
    const goEditPost = () => router.push(`/posts/${props.post.slug}/edit`);
</script>