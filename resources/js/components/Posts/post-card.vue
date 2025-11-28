<template>
    <v-card variant="outlined" width="100%" class="pa-3 mb-3" @click="seePost">
        <post-header :post="post"/>
        <div class="mt-2">
            {{ post.description }}
        </div>
        <div class="text-caption text-captionColor d-flex justify-center mt-4" v-if="post.published_at">
            {{ $t('Published on') }} {{ post.published_at_formated }} - {{ post.stats.reads }} {{ $t('reads') }}
        </div>
        <v-btn 
            size="small"
            color="primary"
            icon="mdi-pencil"
            style="position:absolute;bottom:5px;right:5px;"
            @click.stop="editPost"
            v-if="post.editable"
        />
    </v-card>
</template>
<script setup>
    const props = defineProps({ post: Object });
    import { useRouter } from 'vue-router';

    const router = useRouter();
    const seePost = () => {
        router.push(`/posts/${props.post.slug}`);
    }
    const editPost = () => {
        router.push(`/posts/${props.post.slug}/edit`);
    }
</script>