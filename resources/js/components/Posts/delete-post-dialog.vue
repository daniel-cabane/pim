<template>
    <v-dialog v-model="dialog" width="400">
        <template v-slot:activator="{ props }">
            <v-list-item v-bind="props">
                <template v-slot:prepend>
                    <v-icon icon="mdi-delete" color="error"></v-icon>
                </template>
                <v-list-item-title>{{ $t('Delete post') }}</v-list-item-title>
            </v-list-item>
        </template>
        <v-card title="Are you sure ?" text="Deleting this post is permanent and cannot be undone">
            <v-checkbox :disabled="postLoading" color="error" class="ml-2" label="Yes, delete it all !" v-model="check"/>
            <div class="d-flex pa-2">
                <v-spacer/>
                <v-btn variant="text" :disabled="postLoading" class="mr-2" min-width="150" color="primary" @click="dialog=false">{{ $t('Cancel') }}</v-btn>
                <v-btn color="error" :disabled="!check" :loading="postLoading" min-width="150" @click="confirmDelete">{{ $t('Delete Post') }}</v-btn>
            </div>
        </v-card>
    </v-dialog>
</template>
<script setup>
    import { ref } from 'vue';
    import { usePostStore } from '@/stores/usePostStore';
    import { storeToRefs } from 'pinia';
    import { useRoute, useRouter } from 'vue-router';

    const props = defineProps({post: Object});

    let dialog = ref(false);
    let check = ref(false);

    const route = useRoute();
    const router = useRouter();
    const postStore = usePostStore();
    const { deletePost } = postStore;
    const { post, loading: postLoading } = storeToRefs(postStore);

    const confirmDelete = async () => {
        const res = await deletePost(route.params.slug);
        router.replace('/myPosts');
    }
</script>