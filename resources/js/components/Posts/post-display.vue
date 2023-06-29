<template>
    <v-card width="100%" class="pa-3">
        <post-header :post="post"/>
        <v-divider class="my-2"/>
        <div class="mt-4" v-html="post.post"/>
        <div class="d-flex mt-4">
            <v-btn variant="text" color="error" :disabled="postLoading" @click="quit" v-if="!editing">
                {{ $t('Quit') }}
            </v-btn>
            <v-spacer/>
            <v-menu location="top" v-if="post.editable">
                <template v-slot:activator="{ props }">
                    <v-btn icon="mdi-dots-vertical" :loading="postLoading" v-bind="props"></v-btn>
                </template>
                <v-list>
                    <v-list-item @click="editPost">
                        <template v-slot:prepend>
                            <v-icon icon="mdi-pencil" color="primary"></v-icon>
                        </template>
                        <v-list-item-title>{{ $t('Edit post') }}</v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="updatePost('publish')" v-if="post.published_at == null">
                        <template v-slot:prepend>
                            <v-icon icon="mdi-file-check" color="success"></v-icon>
                        </template>
                        <v-list-item-title>{{ $t('Publish post') }}</v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="updatePost('unpublish')" v-else>
                        <template v-slot:prepend>
                            <v-icon icon="mdi-file-remove" color="secondary"></v-icon>
                        </template>
                        <v-list-item-title>{{ $t('Unpublish post') }}</v-list-item-title>
                    </v-list-item>
                    <v-divider/>
                    <delete-post-dialog :post="post"/>
                </v-list>
            </v-menu>
        </div>
    </v-card>
</template>
<script setup>
    import { usePostStore } from '@/stores/usePostStore';
    import { storeToRefs } from 'pinia';
    import { useRoute } from 'vue-router';
    import { useRouter } from 'vue-router';
    const props = defineProps({ post: Object, editing: Boolean });

    const route = useRoute();
    const router = useRouter();
    const postStore = usePostStore();
    const { updatePost } = postStore;
    const { post, loading: postLoading } = storeToRefs(postStore);

    const quit = () => {
        router.go(-1);
    }

    const emit = defineEmits(['backToEditing'])
    const editPost = () => {
        if(props.editing){
            emit('backToEditing');
        } else {
            router.push(`/posts/${route.params.slug}/edit`);
        }
    }

</script>