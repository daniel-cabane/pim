<template>
    <v-text-field 
        :rules="[rules.required, rules.minLengthTitle]"
        v-model="post.title"
        :label="$t('Title')"
        variant="outlined" 
        validate-on="blur" 
        :disabled="!post.editable" 
    />
    <v-textarea 
        :rules="[rules.required, rules.minLengthDescription]"
        v-model="post.description"
        :label="$t('Description')" 
        variant="outlined" 
        validate-on="blur" 
        :disabled="!post.editable" 
    />
    <div class="text-grey text-caption">
        Content
    </div>
    <Editor 
        api-key="no-api-key" 
        :init="{
            plugins: 'lists link image table code help wordcount'
        }" 
        v-model="post.post" 
        route.params.slug
    />
</template>
<script setup>
    import Editor from '@tinymce/tinymce-vue';

    const props = defineProps({ post: Object });
    
    let rules = {
        required: value => !!value || 'Required.',
        minLengthTitle: value => value.length >= 8 || 'The title must at least 8 characters long',
        minLengthDescription: value => value.length >= 20 || 'The description must at least 20 characters long',
    };
</script>