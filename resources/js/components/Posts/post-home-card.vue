<template>
    <v-card width="350" :to="`/posts/${props.post.slug}`">
        <v-img class="align-end text-white" aspect-ratio="16/9" max-height="197" :src="post.cover.url" cover>
            <v-card-title class="pimSubtitleFont py-0" style="font-weight:bold;font-size:28px;" :style="`color:${post.cover.titleColor};`">
                {{ post.title }}
            </v-card-title>
        </v-img>

        <v-card-subtitle class="pt-4 d-flex justify-space-between">
            <span>
                <v-icon icon="mdi-draw-pen" />
                {{ post.author.name }}
            </span>
            <span class="text-caption">
                {{ $t('Published on') }} {{ post.published_at_formated }}
            </span>
        </v-card-subtitle>

        <v-card-text class="py-2">
            {{ post.description }}
        </v-card-text>

        <div class="px-3 pb-3">
            <v-chip size="small" label class="mr-2 mb-1" color="primary" v-for="theme in post.themeTitles">
                #{{ theme }}
            </v-chip>
        </div>
    </v-card>
</template>
<script setup>
    const props = defineProps({ post: Object });
</script>