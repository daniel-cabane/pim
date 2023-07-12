<template>
    <v-menu location="top">
        <template v-slot:activator="{ props }">
            <v-btn icon="mdi-dots-vertical" :loading="loading" v-bind="props"></v-btn>
        </template>
        <v-list>
            <v-list-item @click="update('keep')" v-if="editing">
                <template v-slot:prepend>
                    <v-icon icon="mdi-content-save" color="primary"></v-icon>
                </template>
                <v-list-item-title>{{ $t('Save post') }}</v-list-item-title>
            </v-list-item>
            <v-list-item @click="editPost" v-else>
                <template v-slot:prepend>
                    <v-icon icon="mdi-pencil" color="primary"></v-icon>
                </template>
                <v-list-item-title>{{ $t('Edit post') }}</v-list-item-title>
            </v-list-item>
            <v-list-item @click="update('publish')" v-if="post.published_at == null">
                <template v-slot:prepend>
                    <v-icon icon="mdi-file-check" color="success"></v-icon>
                </template>
                <v-list-item-title>{{ $t('Publish post') }}</v-list-item-title>
            </v-list-item>
            <v-list-item @click="update('unpublish')" v-else>
                <template v-slot:prepend>
                    <v-icon icon="mdi-file-remove" color="secondary"></v-icon>
                </template>
                <v-list-item-title>{{ $t('Unpublish post') }}</v-list-item-title>
            </v-list-item>
            <v-divider/>
            <delete-post-dialog :post="post"/>
        </v-list>
    </v-menu>
</template>
<script setup>
    import { ref } from 'vue';
    import { usePostStore } from '@/stores/usePostStore';
    import { useRouter } from 'vue-router';
    const props = defineProps({ post: Object, editing: Boolean });

    const loading = ref(false);

    const router = useRouter();
    const postStore = usePostStore();
    const { updatePost } = postStore;

    const editPost = () => router.push(`/posts/${props.post.slug}/edit`);

    const update = async publish => {
        loading.value = true;
        await updatePost(props.post, publish);
        loading.value = false;
    }
</script>