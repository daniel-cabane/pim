<template>
    <v-card width="100%" class="pa-3" v-if="isReady">
        <div class="mb-4 d-flex justify-space-between align-center">
            <span class="d-flex">
                <v-img max-width='30px' min-width='30px' class="mr-4" src="/images/flag en.png" contain />
                <v-switch density="compact" hide-details v-model='lgSwitch'
                    @change="post.language = lgSwitch ? 'fr' : 'en'">
                </v-switch>
                <v-img max-width='30px' min-width='30px' class='ml-4' src="/images/flag fr.png" contain />
            </span>
            <span>
                <v-dialog max-width="500">
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn class="mr-2" v-bind="activatorProps" :text="$t('Translation')" rounded="xl" color="primary"/>
                    </template>
                    <template v-slot:default="{ isActive }">
                        <v-card :title="$t('Translation')" :subtitle="post.title">
                        <v-card-text v-if="post.translationId">
                            <div>
                                {{ $t('This post has a translation') }}
                            </div>
                            <div class="d-flex justify-center pa-2 mt-2">
                                <v-btn :text="$t('See translation')" color="primary" @click="goToTranslation"/>
                            </div>
                        </v-card-text>
                        <v-card-text v-else>
                            <div>
                                {{ $t('This post does not have a translation') }}
                            </div>
                            <div class="d-flex flex-column align-center pa-2 mt-2">
                                <v-text-field variant="outlined" :label="$t('Translation title')" v-model="translationTitle" style="width:400px;"/>
                                <v-btn :text="$t('Create translation')" :disabled="translationTitle.length < 8" color="primary" :loading="isLoading" @click="createTranslationAndGo"/>
                            </div>
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="error" :text="$t('Close')" @click="isActive.value = false"/>
                        </v-card-actions>
                        </v-card>
                    </template>
                </v-dialog>
                <v-chip color="success" variant="flat" append-icon="mdi-file-check" v-if="post.status == 'published' && !user.is.admin">
                    {{ $t('Published') }}
                </v-chip>
                <v-menu v-else>
                    <template v-slot:activator="{ props }">
                        <v-btn :color="statusButton.color" rounded="xl" :append-icon="statusButton.icon" v-bind="props" :disabled="statusLoading">
                            {{ $t(statusButton.status) }}
                        </v-btn>
                    </template>
                    <v-list>
                        <v-list-item v-for="item in statusList" :color="item.color" :key="item.status" rounded="xl"
                            @click="updatePostStatus(item.status)">
                            <v-list-item-title>{{ $t(item.status) }}</v-list-item-title>
                            <template v-slot:append>
                                <v-icon :icon="item.icon"></v-icon>
                            </template>
                        </v-list-item>
                    </v-list>
                </v-menu>
            </span>
        </div>
        <post-edit-form :post="post" />
        <div class="d-flex align-center mt-4">
            <v-dialog v-model="quitDialog" width="350">
                <template v-slot:activator="{ props }">
                    <v-btn 
                        variant="tonal"
                        color="error"
                        rounded="xl"
                        style="width:120px"
                        :disabled="loading"
                        prepend-icon="mdi-arrow-left-bold-circle-outline" v-bind="props"
                    >
                        {{ $t('Quit') }}
                    </v-btn>
                </template>
                <v-card :title="$t('Are you sure ?')" :text="$t('If you quit now all unsaved changes will be lost')">
                    <div class="d-flex justify-space-between pa-4">
                        <v-btn variant="tonal" color="primary" @click="quitDialog = false">{{ $t('Stay') }}</v-btn>
                        <v-btn variant="tonal" color="error" @click="quitConfirmed">{{ $t('Quit') }}</v-btn>
                    </div>
                </v-card>
            </v-dialog>
            <v-spacer />
            <v-btn color="success" class="mr-2" style="min-width:150px;" :loading="isLoading" :disabled="statusLoading"
                @click="updatePost">
                {{ $t('Save') }}
            </v-btn>
            <post-edit-menu :post="post" :loading="statusLoading" @updatePostStatus="updatePostStatus" />
        </div>
    </v-card>
</template>
<script setup>
    import { ref, computed } from 'vue';
    import { useAuthStore } from '@/stores/useAuthStore';
    import { usePostStore } from '@/stores/usePostStore';
    import { storeToRefs } from 'pinia';
    import { useRoute } from 'vue-router';
    import { useRouter } from 'vue-router';

    const { user } = useAuthStore();

    const quitDialog = ref(false);
    const loading = ref(false);

    const route = useRoute();
    const router = useRouter();
    const postStore = usePostStore();
    const { getPost, updatePost, updatePostStatus, createTranslation, findTranslation } = postStore;
    const { post, isReady, isLoading, statusLoading } = storeToRefs(postStore);

    getPost(route.params.slug);

    const lgSwitch = ref(post.value.language == 'fr');

    const quitConfirmed = () => {
        quitDialog.value = false;
        router.go(-1);
    }

    const statusButtonDetails = [
        { status: 'draft', icon: 'mdi-file-hidden', color: 'secondary' },
        { status: 'submitted', icon: 'mdi-file-eye', color: 'primary' },
    ];
    if(user.is.admin){
        statusButtonDetails.push({ status: 'published', icon: 'mdi-file-check', color: 'success' })
    }

    const statusButton = computed(() => {
        return statusButtonDetails.find(b => b.status == post.value.status);
    });
    const statusList = computed(() => {
        return statusButtonDetails.filter(b => b.status != post.value.status);
    });

    const translationTitle = ref('');
    const createTranslationAndGo = async () => {
        await createTranslation(translationTitle.value);
        // router.push(`/posts/${newPost.slug}`);
        // getPost(newPost.slug);
        // lgSwitch.value = newPost.language == 'fr';
    }
    const goToTranslation = async () => {
        const newPost = await findTranslation();
        if(newPost){
            router.push(`/posts/${newPost.slug}/edit`);
            getPost(newPost.slug);
            lgSwitch.value = newPost.language == 'fr';
        }
    }
</script>