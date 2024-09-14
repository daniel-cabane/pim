<template>
    <v-card width="100%" class="pa-3">
        <div class="d-flex justify-space-between">
            <div>
                <div class="pimSubtitleFont font-weight-bold" style="font-size:48px;line-height:60px;">
                    {{ post.title }}
                </div>
                <div v-if="post.published_at">                   
    
                </div>
                <div class="font-italic mt-3" v-if="post.author">
                    {{ $t('By') }} {{ post.author.name }}
                </div>
                <div class="pt-4">
                    <v-chip size="small" label class="mr-2 mb-2" color="fis" v-for="theme in post.themeTitles">
                        #{{ theme }}
                    </v-chip>
                </div>
            </div>
            <div style="min-width:250px;max-width:250px;">
                <v-img class="align-end text-white" min-width="250" max-width="250" aspect-ratio="16/9" cover :src="post.cover.url" />
                <div class="d-flex justify-end mt-1" style="white-space:nowrap;" v-if="post.published_at">
                    <span class="text-caption">
                        {{ $t('Published on') }} {{ post.published_at_formated }}
                    </span>
                    <span class="text-caption font-italic ml-1" v-if="post.edited">
                        ({{ $t('Edited on') }} {{ post.updated_at_formated }})
                    </span>
                </div>
            </div>
        </div>
        <v-divider class="my-2" />
        <div class="mt-4 postWrapper" v-html="formattedPost" />
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

    const formattedPost = props.post.post
                                .split('<ul').join('<ul class="pl-5"')
                                .split('<ol').join('<ol class="pl-5"');
</script>
<style>
    .postWrapper img {
        max-width: 100%;
        height: auto;
    }
</style>