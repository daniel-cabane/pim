<template>
    <v-card class="d-flex" v-if="isReady">
        <v-tabs v-model="tab" color="primary" direction="vertical">
            <v-tab prepend-icon="mdi-file-eye" :text="$t('Submitted')" value="submitted" />
            <v-tab prepend-icon="mdi-file-check" :text="$t('Published')" value="published"></v-tab> 
        </v-tabs>

        <v-tabs-window style="flex:1" v-model="tab">
            <v-tabs-window-item value="submitted">
                <div class="pa-4">
                    <post-card v-for="post in posts.submitted" :key="post.id" :post="post" />
                </div>
            </v-tabs-window-item>
            <v-tabs-window-item value="published">
                <div class="pa-4">
                    <post-card v-for="post in posts.published" :key="post.id" :post="post" />
                </div>
            </v-tabs-window-item>
        </v-tabs-window>
    </v-card>
</template>
<script setup>
    import { ref } from "vue";
    import { usePostStore } from '@/stores/usePostStore';
    import { storeToRefs } from 'pinia';

    const postStore = usePostStore();
    const { adminGetposts } = postStore;
    const { posts, isReady } = storeToRefs(postStore);
    adminGetposts();

    const tab = ref('submitted');
</script>