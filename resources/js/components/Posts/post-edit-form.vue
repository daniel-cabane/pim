<template>
    <v-text-field :rules="[rules.required, rules.minLengthTitle]" v-model="post.title" :label="$t('Title')"
        variant="outlined" validate-on="blur" :disabled="!post.editable" />
    <v-textarea :rules="[rules.required, rules.minLengthDescription]" v-model="post.description"
        :label="$t('Description')" variant="outlined" validate-on="blur" :disabled="!post.editable" />
    <div class="text-grey text-caption">
        Content
    </div>
    <Editor api-key="c6xujr454hv9o3u7uqat7dlla2v61j7n3syp29hhj0k4aeeu" :init="{
            plugins: 'lists link image table code help wordcount'
        }" v-model="post.post" />
</template>
<script setup>
    import Editor from '@tinymce/tinymce-vue';
    const props = defineProps({ post: Object });
    
    const rules = {
        required: value => !!value || 'Required.',
        minLengthTitle: value => value.length >= 5 || 'The title must at least 5 characters long',
        minLengthDescription: value => value.length >= 10 || 'The description must at least 10 characters long',
    };
</script>