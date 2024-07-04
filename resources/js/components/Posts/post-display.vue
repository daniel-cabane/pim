<template>
    <v-card width="100%" class="pa-3">
        <div class="d-flex justify-space-between">
            <div>
                <div class="text-h5 font-weight-bold">
                    {{ post.title }}
                </div>
                <div v-if="post.published_at">
                    <span class="text-caption">
                        {{ $t('Published on') }} {{ post.published_at_formated }}
                    </span>
                    <span class="text-caption font-italic" v-if="post.edited">
                        ({{ $t('Edited on') }} {{ post.updated_at_formated }})
                    </span>
                </div>
                <div class="font-italic" v-if="post.author">
                    {{ $t('By') }} {{ post.author.name }}
                </div>
            </div>
            <v-img class="align-end text-white" max-width="120" max-height="67.5" aspect-ratio="16/9" cover :src="post.cover.url" />
        </div>
        <v-divider class="my-2" />
        <div class="mt-4" v-html="post.post" />
        <div class="d-flex align-center mt-4">
            <back-btn />
            <v-spacer />
            <v-btn icon="mdi-pencil" color="primary" @click="goEditPost" v-if="post.editable" />
        </div>
    </v-card>
</template>
<script setup>
    import { useRouter } from 'vue-router';

    const props = defineProps({ post: Object });

    const router = useRouter();
    const goEditPost = () => router.push(`/posts/${props.post.slug}/edit`);
</script>