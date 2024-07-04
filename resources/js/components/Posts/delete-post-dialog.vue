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
        <v-card :title="$t('Are you sure ?')" :text="$t('Deleting this post is permanent and cannot be undone')">
            <v-checkbox :disabled="loading" color="error" class="ml-2" :label="$t('Yes, delete it forever !')" v-model="check"/>
            <div class="d-flex pa-2">
                <v-spacer/>
                <v-btn variant="text" :disabled="loading" class="mr-2" min-width="150" color="primary" @click="dialog=false">{{ $t('Cancel') }}</v-btn>
                <v-btn color="error" :disabled="!check" :loading="loading" min-width="150" @click="confirmDelete">{{ $t('Delete Post') }}</v-btn>
            </div>
        </v-card>
    </v-dialog>
</template>
<script setup>
    import { ref } from 'vue';
    import { usePostStore } from '@/stores/usePostStore';
    import { useRoute, useRouter } from 'vue-router';

    const props = defineProps({post: Object});

    let dialog = ref(false);
    let check = ref(false);
    let loading = ref(false);

    const route = useRoute();
    const router = useRouter();
    const postStore = usePostStore();
    const { deletePost } = postStore;

    const confirmDelete = async () => {
        loading.value = true;
        const res = await deletePost(route.params.slug);
        loading.value = false;
        router.replace('/myPosts');
    }
</script>