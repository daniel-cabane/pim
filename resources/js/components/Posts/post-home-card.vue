<template>
    <v-card width="350" :to="`/posts/${props.post.slug}`">
        <post-cover :post="post"/>

        <v-card-subtitle class="pt-1 d-flex justify-space-between align-center">
            <span class="d-flex align-baseline">
                <v-icon icon="mdi-account-edit" class="mr-1"/>
                {{ post.author.name }}
            </span>
            <span class="text-caption d-flex align-center">
                <span>
                    {{ post.published_at_formated }}
                </span>
                <v-img :src="`/images/flag ${post.language}.png`" :width="25" class="ml-2 " />
            </span>
        </v-card-subtitle>

        <v-card-text class="py-2">
            {{ post.description }}
        </v-card-text>

        <div class="px-3 pb-3">
            <v-chip size="small" label class="mr-2 mb-1" color="fis" v-for="theme in post.themeTitles">
                #{{ theme }}
            </v-chip>
        </div>
    </v-card>
</template>
<script setup>
    const props = defineProps({ post: Object });
</script>