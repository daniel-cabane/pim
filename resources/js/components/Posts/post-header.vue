<template>
    <div style="display:flex;align-items:center;justify-content:space-between;">
        <div>
            <div class="text-h5 font-weight-bold">
                {{ post.title }}
            </div>
            <div class="font-italic text-caption text-grey">
                By Pim
            </div>
        </div>
        <v-chip color="secondary" label v-if="post.published_at == null">
          <v-icon start icon="mdi-note-remove"></v-icon>
          Draft
        </v-chip>
        <div v-else>
            <div class="text-caption">
                Published on ...
            </div>
            <div class="text-caption font-italic">
                Edited on ...
            </div>
        </div>
    </div>
</template>
<script setup>
    const props = defineProps({ post: Object });
</script>