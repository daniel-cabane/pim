<template>
    <div style="display:flex;align-items:flex-start;justify-content:space-between;">
        <div>
            <div class="text-h5 font-weight-bold">
                {{ post.title }}
            </div>
            <div class="font-italic text-caption text-grey" v-if="post.author">
                By {{ post.author.name }}
            </div>
        </div>
        <v-chip color="secondary" label v-if="post.published_at == null">
          <v-icon start icon="mdi-note-remove"></v-icon>
          Draft
        </v-chip>
        <div class="text-right" v-else>
            <div class="text-caption">
                {{ $t('Published on') }} {{ post.published_at_formated }}
            </div>
            <div class="text-caption font-italic" v-if="post.edited">
                {{ $t('Edited on') }} {{ post.updated_at_formated }}
            </div>
        </div>
    </div>
</template>
<script setup>
    const props = defineProps({ post: Object });
</script>