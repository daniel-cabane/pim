<template>
    <v-window v-model="mainWindow">
        <v-window-item :value="1">
            <v-card width="100%" class="pa-3">
                <post-edit-form :post="post"/>
                <div class="d-flex mt-4">
                    <v-dialog v-model="quitDialog" width="350">
                        <template v-slot:activator="{ props }">
                            <v-btn variant="text" color="error" :disabled="postLoading" v-bind="props">{{ $t('Quit') }}</v-btn>
                        </template>
                        <v-card :title="$t('Are you sure ?')" :text="$t('If you quit now all unsaved changes will be lost')">
                            <div class="d-flex justify-space-between pa-4">
                                <v-btn color="primary" @click="quitDialog = false">{{ $t('Stay') }}</v-btn>
                                <v-btn color="error" @click="quitConfirmed">{{ $t('Quit') }}</v-btn>
                            </div>
                        </v-card>
                    </v-dialog>
                    <v-spacer/>
                    <v-btn color="primary" width="150" :loading="postLoading" class="mr-2" @click="updatePost('keep')">{{ $t('Save draft') }}</v-btn>
                    <v-btn color="success" width="150" @click="preview">{{ $t('Preview') }}</v-btn>
                </div>
            </v-card>
        </v-window-item>
        <v-window-item :value="2">
            <post-display :post="post" :editing="true" @backToEditing="backToEditing"/>
        </v-window-item>
    </v-window>
</template>
<script setup>
    import { ref } from 'vue';
    import { usePostStore } from '@/stores/usePostStore';
    import { storeToRefs } from 'pinia';
    import { useRoute } from 'vue-router';
    import { useRouter } from 'vue-router';

    let quitDialog = ref(false);
    let mainWindow = ref(1);

    const route = useRoute();
    const router = useRouter();
    const postStore = usePostStore();
    const { getFullPost, updatePost } = postStore;
    const { post, loading: postLoading } = storeToRefs(postStore);

    getFullPost(route.params.slug);

    let rules = {
        required: value => !!value || 'Required.',
        minLengthTitle: value => value.length >= 8 || 'The title must at least 8 characters long',
        minLengthDescription: value => value.length >= 20 || 'The description must at least 20 characters long',
    };

    const quitConfirmed = () => {
        router.go(-1);
    }
    const preview = () => {
        mainWindow.value = 2;
    }
    const backToEditing = () => {
        mainWindow.value = 1;
    }
</script>