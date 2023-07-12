<template>
    <v-card width="100%" class="pa-3">
        <div class="mb-4" style="display:flex;justify-content:flex-end;align-items:center;">
            <v-chip color="success" append-icon="mdi-file-check" v-if="post.published_at">
                {{ $t('Published') }}
            </v-chip>
            <v-chip color="primary" append-icon="mdi-file-hidden" v-else>
                {{ $t('DRAFT') }}
            </v-chip>
        </div>
        <post-edit-form :post="post"/>
        <div class="d-flex align-center mt-4">
            <v-dialog v-model="quitDialog" width="350">
                <template v-slot:activator="{ props }">
                    <v-btn variant="tonal" color="error" rounded="xl" style="width:120px" :disabled="loading" prepend-icon="mdi-arrow-left-bold-hexagon-outline" v-bind="props">
                        {{ $t('Quit') }}
                    </v-btn>
                </template>
                <v-card :title="$t('Are you sure ?')" :text="$t('If you quit now all unsaved changes will be lost')">
                    <div class="d-flex justify-space-between pa-4">
                        <v-btn color="primary" @click="quitDialog = false">{{ $t('Stay') }}</v-btn>
                        <v-btn color="error" @click="quitConfirmed">{{ $t('Quit') }}</v-btn>
                    </div>
                </v-card>
            </v-dialog>
            <v-spacer/>
            <v-dialog v-model="previewDialog" fullscreen :scrim="false" transition="dialog-bottom-transition">
                <template v-slot:activator="{ props }">
                    <v-btn class="mr-2" color="success" icon="mdi-eye" v-bind="props"/>
                </template>
                <v-card>
                    <v-toolbar dark color="secondary">
                    <v-btn icon dark @click="previewDialog = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                    <v-toolbar-title>Post preview</v-toolbar-title>
                    </v-toolbar>
                    <div class="px-5 py-2">
                        <post-header :post="post"/>
                        <v-divider class="my-2"/>
                        <div class="mt-4" v-html="post.post"/>
                    </div>
                </v-card>
            </v-dialog>
            <post-edit-menu :post="post" :editing="true"/>
        </div>
    </v-card>
</template>
<script setup>
    import { ref } from 'vue';
    import { usePostStore } from '@/stores/usePostStore';
    import { storeToRefs } from 'pinia';
    import { useRoute } from 'vue-router';
    import { useRouter } from 'vue-router';

    let quitDialog = ref(false);
    let previewDialog = ref(false);
    let loading = ref(false);

    const route = useRoute();
    const router = useRouter();
    const postStore = usePostStore();
    const { getPost, updatePost } = postStore;
    const { post } = storeToRefs(postStore);

    getPost(route.params.slug);

    const quitConfirmed = () => {
        quitDialog.value = false;
        router.go(-1);
    }
    const preview = () => {
        previewDialog.value = true;
    }
</script>