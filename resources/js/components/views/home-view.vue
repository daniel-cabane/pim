<template>
    <v-container>
        <div>
            {{ $t('home') }}
        </div>
        <!-- <Editor
          api-key="no-api-key"
          :init="{
              plugins: 'lists link image table code help wordcount'
          }"
        /> -->
        <div>Posts !</div>
        <div v-for="post in posts" :key="post.id">{{ post.title }}</div>
    </v-container>
</template>
<script setup>
    import { usePostStore } from '@/stores/usePostStore';
    import { storeToRefs } from 'pinia';

    const postStore = usePostStore();
    const { getPosts } = postStore;
    const { posts } = storeToRefs(postStore);

    getPosts();
    // import Editor from '@tinymce/tinymce-vue'
    // import { storeToRefs } from 'pinia';
    // import { useAuthStore } from '@/stores/useAuthStore';

    // const authStore = useAuthStore();
    // const { user } = storeToRefs(authStore);
        
</script>