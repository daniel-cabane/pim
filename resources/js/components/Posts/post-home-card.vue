<template>
    <v-card class="mx-auto" max-width="350" :to="`/posts/${props.post.slug}`">
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

        <v-card-text>
            {{ post.description }}
        </v-card-text>

        <!-- <v-card-actions>
            <v-spacer />

            <v-btn color="primary" icon="mdi-eye" />
        </v-card-actions> -->
    </v-card>
</template>
<script setup>
    const props = defineProps({ post: Object });
</script>