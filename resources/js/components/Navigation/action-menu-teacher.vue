<template>
    <v-menu close-on-content-click>
        <template v-slot:activator="{ props }">
            <v-btn dark  icon="mdi-school" v-bind="props"/>
        </template>
        <v-list>
        <v-list-item @click="gotoMyPosts">
            <template v-slot:prepend>
                <v-icon icon="mdi-post-outline"></v-icon>
            </template>
            <v-list-item-title>{{ $t("My posts") }}</v-list-item-title>
        </v-list-item>
        <v-dialog v-model="newPostDialog" width="450">
            <template v-slot:activator="{ props }">
                <v-list-item v-bind="props">
                    <template v-slot:prepend>
                        <v-icon icon="mdi-pencil-plus-outline"></v-icon>
                    </template>
                    <v-list-item-title>{{ $t("New post") }}</v-list-item-title>
                </v-list-item>
            </template>
            <v-card>
                <v-card-title>{{ $t("New post") }}</v-card-title>
                <v-card-text>
                    <v-text-field
                      :rules="[rules.required, rules.minLengthTitle]"
                      v-model="newPost.title"
                      :label="$t('Title')" 
                      variant="outlined"
                      validate-on="blur"
                    />
                    <v-textarea 
                        :rules="[rules.required, rules.minLengthDescription]"
                        v-model="newPost.description"
                        :label="$t('Description')" 
                        variant="outlined"
                        validate-on="blur"
                    />
                </v-card-text>
                <div class="d-flex pa-2">
                    <v-spacer/>
                    <v-btn variant="text" class="mr-2" min-width="150" :disabled="newPostLoading" color="error" @click="newPostDialog = false">{{ $t('Close') }}</v-btn>
                    <v-btn color="primary" min-width="150" :loading="newPostLoading" @click="submitNewPost">{{ $t('Submit') }}</v-btn>
                </div>
            </v-card>
        </v-dialog>
        <v-divider/>
        <v-list-item @click="gotoMyWorkshops">
            <template v-slot:prepend>
                <v-icon icon="mdi-puzzle"></v-icon>
            </template>
            <v-list-item-title>{{ $t("My workshops") }}</v-list-item-title>
        </v-list-item>
        <v-dialog v-model="newWorkshopDialog" width="450">
            <template v-slot:activator="{ props }">
                <v-list-item v-bind="props">
                    <template v-slot:prepend>
                        <v-icon icon="mdi-puzzle-plus"></v-icon>
                    </template>
                    <v-list-item-title>{{ $t("New workshop") }}</v-list-item-title>
                </v-list-item>
            </template>
            <v-card>
                <v-card-title>{{ $t("New workshop") }}</v-card-title>
                <v-card-text>
                    <v-text-field
                        :rules="[rules.required, rules.minLengthTitle]"
                        v-model="workshopTitle"
                        :label="$t('Title')" 
                        variant="outlined"
                        validate-on="blur"
                    />
                    <v-select
                        v-model="workshopThemes"
                        :items="availableThemes"
                        label="Themes"
                        multiple
                        chips
                        variant="outlined"
                    />
                </v-card-text>
                <div class="d-flex pa-2">
                    <v-spacer/>
                    <v-btn variant="text" class="mr-2" min-width="150" :disabled="newWorkshopLoading" color="error" @click="newWorkshopDialog = false">{{ $t('Close') }}</v-btn>
                    <v-btn color="primary" min-width="150" :loading="newWorkshopLoading" @click="submitCreateWorkshop">{{ $t('Submit') }}</v-btn>
                </div>
            </v-card>
        </v-dialog>
        <v-divider/>
    </v-list>
    </v-menu>
</template>
<script setup>
    import { ref, reactive, computed } from 'vue';
    import { useRouter } from 'vue-router';
    import { usePostStore } from '@/stores/usePostStore';
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    import { storeToRefs } from 'pinia';
    import { useI18n } from 'vue-i18n';

    const postStore = usePostStore();
    const { createPost } = postStore;
    const router = useRouter();

    let newPostDialog = ref(false);
    const newPost = reactive({title: '', description: ''});

    const rules = {
        required: value => !!value || 'Required.',
        minLengthTitle: value => value.length >= 5 || 'The title must at least 5 characters long',
        minLengthDescription: value => value.length >= 10 || 'The description must at least 10 characters long',
    }

    const gotoMyPosts = () => {
        router.push('/myPosts');
    }

    let newPostLoading = ref(false);
    const submitNewPost = async () => {
        if(newPost.title.length >= 5 && newPost.description.length >= 10){
            newPostLoading.value = true;
            const post = await createPost(newPost);
            newPost.title = '';
            newPost.description = '';
            newPostLoading.value = false;
            newPostDialog.value = false;
            router.push(`/posts/${post.slug}/edit`);
        }
    }

    const workshopStore = useWorkshopStore();
    const { createWorkshop, getThemes } = workshopStore;
    const { themes } = storeToRefs(workshopStore);

    getThemes();
    const { locale } = useI18n();
    const availableThemes = computed(() => themes.value.map(theme => {
        return {
            title: locale == 'en' ? theme.title_en : theme.title_fr,
            value: theme.id
        }
    }));

    let newWorkshopDialog = ref(false);
    let workshopTitle = ref('');
    let workshopThemes = ref([]);
    let newWorkshopLoading = ref(false);
    const submitCreateWorkshop = async () => {
        newWorkshopLoading.value = true;
        const workshop = await createWorkshop({title: workshopTitle.value, themes: workshopThemes.value});
        workshopTitle.value = '';
        workshopThemes.value = [];
        newWorkshopDialog.value = false;
        newWorkshopLoading.value = false;
        router.push(`/workshops/${workshop.id}/edit`);
    }

    const gotoMyWorkshops = () => {
        router.push('/myWorkshops');
    }
</script>