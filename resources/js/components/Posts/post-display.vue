<template>
    <v-card width="100%" class="pa-3">
        <div 
            class="pimSubtitleFont font-weight-bold mb-4" 
            style="font-size:48px;line-height:60px;"
            v-text="post.title"
        />
        <div class="d-flex flex-column-reverse flex-lg-row justify-space-between ga-4">
            <div>
                <div v-text="post.description" />
                <div class="font-italic mt-3" v-if="post.author">
                    {{ $t('By') }} {{ post.author.name }}
                </div>
                <div class="text-caption">
                    {{ $t('Published on') }} {{ post.published_at_formated }}
                </div>
                <div class="pt-4">
                    <v-chip size="small" label class="mr-2 mb-2" color="fis" v-for="theme in post.themeTitles">
                        #{{ theme }}
                    </v-chip>
                </div>
                <series-display :series="post.seriesDetails"/>
            </div>
            <v-img width="400" max-width="100%" aspect-ratio="16/9" contain style="border-radius:4px;" :src="post.cover.url" />
        </div>
    </v-card>
    <v-card class="mt-4 pa-3">
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
        border-radius: 8px;
    }
</style>