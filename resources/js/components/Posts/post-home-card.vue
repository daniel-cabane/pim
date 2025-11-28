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

        <!-- <v-card-text class="py-2">
            <div>
                {{ post.description }}
            </div>
            <div class="mt-2">
                <v-chip size="small" label class="mr-2 mb-1" color="fis" v-for="theme in post.themeTitles">
                    #{{ theme }}
                </v-chip>
            </div>
        </v-card-text> -->
        <v-card-text class="py-2 card-text-grid" style="height:154px;">
            <div class="description">
                {{ post.description }}
            </div>
            <div class="chips">
                <v-chip
                    size="small"
                    label
                    class="mr-2 mb-1"
                    color="fis"
                    v-for="theme in post.themeTitles"
                    :key="theme"
                >
                    #{{ theme }}
                </v-chip>
            </div>
        </v-card-text>

    </v-card>
</template>
<script setup>
    const props = defineProps({ post: Object });
</script>
<style scoped>
    .card-text-grid {
        display: grid;
        grid-template-rows: 1fr 30px;
        gap: 8px;
    }
    .card-text-grid .description {
        overflow: hidden;
    }
    .card-text-grid .chips {
        overflow: hidden;
    }
</style>