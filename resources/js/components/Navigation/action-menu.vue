<template>
    <v-menu close-on-content-click>
        <template v-slot:activator="{ props }">
            <v-btn dark icon="mdi-star-four-points-circle-outline" v-bind="props" />
        </template>
        <v-list>
            <v-list-item @click="gotoMyPosts" v-if="user.is.publisher">
                <template v-slot:prepend>
                    <v-icon icon="mdi-post-outline"></v-icon>
                </template>
                <v-list-item-title>{{ $t("My posts") }}</v-list-item-title>
            </v-list-item>
            <v-dialog v-model="newPostDialog" width="450" v-if="user.is.publisher">
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
                        <v-text-field :rules="[rules.required, rules.minLengthTitle]" v-model="newPost.title"
                            :label="$t('Title')" variant="outlined" validate-on="blur" />
                        <v-select v-model="newPost.language"
                            :items="[{title: 'Français', value: 'fr'}, {title: 'English', value: 'en'}]"
                            :label="$t('Language') " variant="outlined" />
                        <v-textarea :rules="[rules.required, rules.minLengthDescription]" v-model="newPost.description"
                            :label="$t('Description')" variant="outlined" validate-on="blur" />
                    </v-card-text>
                    <div class="d-flex pa-2">
                        <v-spacer />
                        <v-btn variant="text" class="mr-2" min-width="150" :disabled="newPostLoading" color="error"
                            @click="newPostDialog = false">{{ $t('Close') }}</v-btn>
                        <v-btn color="success" min-width="150" :loading="newPostLoading" @click="submitNewPost">{{
                            $t('Create') }}</v-btn>
                    </div>
                </v-card>
            </v-dialog>
            <v-divider />
            <v-list-item @click="gotoMyWorkshops" v-if="user.is.teacher">
                <template v-slot:prepend>
                    <v-icon icon="mdi-puzzle"></v-icon>
                </template>
                <v-list-item-title>{{ $t("My workshops") }}</v-list-item-title>
            </v-list-item>
            <v-dialog v-model="newWorkshopDialog" width="450" v-if="user.is.teacher">
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
                        <v-text-field :rules="[rules.required, rules.minLengthTitle]" v-model="workshopTitleFr"
                            :label="`Titre ${workshopLanguage != 'fr' ? '(fr)' : ''}`" variant="outlined"
                            validate-on="blur" v-if="workshopLanguage != 'en'" />
                        <v-text-field :rules="[rules.required, rules.minLengthTitle]" v-model="workshopTitleEn"
                            :label="`Title ${workshopLanguage != 'en' ? '(en)' : ''}`" variant="outlined"
                            validate-on="blur" v-if="workshopLanguage != 'fr'" />
                        <v-select v-model="workshopLanguage" :items="availableLanguages" :label="$t('Languages')"
                            variant="outlined" />
                        <v-select :label="$t('Term')" variant="outlined" validate-on="blur" :items="[1, 2, 3]"
                            v-model="workshopTerm" />
                    </v-card-text>
                    <div class="d-flex pa-2">
                        <v-spacer />
                        <v-btn variant="text" class="mr-2" min-width="150" :disabled="newWorkshopLoading" color="error"
                            @click="newWorkshopDialog = false">{{ $t('Close') }}</v-btn>
                        <v-btn color="primary" min-width="150" :loading="newWorkshopLoading"
                            @click="submitCreateWorkshop">{{ $t('Create') }}</v-btn>
                    </div>
                </v-card>
            </v-dialog>
            <v-divider/>
            <v-list-item @click="gotoMyCourses" v-if="user.is.teacher">
                <template v-slot:prepend>
                    <v-icon icon="mdi-school"></v-icon>
                </template>
                <v-list-item-title>{{ $t("My courses") }}</v-list-item-title>
            </v-list-item>
            <v-divider/>
            <v-list-item @click="gotoOpenDoors" v-if="user.is.teacher">
                <template v-slot:prepend>
                    <v-icon icon="mdi-door-open"></v-icon>
                </template>
                <v-list-item-title>{{ $t("Open doors") }} (BPR)</v-list-item-title>
            </v-list-item>
            <v-dialog v-model="myActivityDialog" width="80%" v-if="user.is.teacher">
                <template v-slot:activator="{ props }">
                    <v-list-item v-bind="props" @click="showMyHours">
                        <template v-slot:prepend>
                            <v-icon icon="mdi-clock-time-eight-outline"></v-icon>
                        </template>
                        <v-list-item-title>{{ $t("My hours") }}</v-list-item-title>
                    </v-list-item>
                </template>
                <v-card :loading="activityLoading">
                    <v-card-title class="d-flex">
                        {{ $t("My hours") }}
                        <span>
                            ({{ user.preferences.hoursDuePerWeek }}h {{ $t('per week') }})
                        </span>
                        <v-spacer/>
                        <teacher-activity-legend/>
                    </v-card-title>
                    <v-card-text>
                        <teacher-activity :teacher="user" class="mt-8"/>
                    </v-card-text>
                    <div class="d-flex pa-2">
                        <v-spacer />
                        <v-btn 
                            variant="tonal"
                            class="mr-2"
                            min-width="150"
                            :disabled="newWorkshopLoading"
                            color="error"
                            @click="myActivityDialog=false"
                        >
                            {{ $t('Close') }}
                        </v-btn>
                    </div>
                </v-card>
            </v-dialog>
        </v-list>
    </v-menu>
</template>
<script setup>
    import { ref, reactive, computed } from 'vue';
    import { useRouter } from 'vue-router';
    import { usePostStore } from '@/stores/usePostStore';
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    import { useAuthStore } from '@/stores/useAuthStore';
    import { storeToRefs } from 'pinia';

    const postStore = usePostStore();
    const { createPost } = postStore;
    const router = useRouter();
    const { fetchMyActivity } = useAuthStore();
    const { user } = storeToRefs(useAuthStore());

    let newPostDialog = ref(false);
    const newPost = reactive({title: '', description: '', language: ''});

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
    const { createWorkshop } = workshopStore;

    const availableLanguages = [{ value: 'fr', title: 'Français' }, { value: 'en', title: 'English' }, { value: 'both', title: 'Les deux / Both' }];

    let newWorkshopDialog = ref(false);
    let workshopTitleEn = ref('');
    let workshopTitleFr = ref('');
    let workshopThemes = ref([]);
    let workshopLanguage = ref('fr');
    let workshopTerm = ref(1);
    let newWorkshopLoading = ref(false);
    const submitCreateWorkshop = async () => {
        newWorkshopLoading.value = true;
        try{
            const workshop = await createWorkshop({ 
                title_fr: workshopTitleFr.value,
                title_en: workshopTitleEn.value,
                themes: workshopThemes.value,
                language: workshopLanguage.value,
                term: workshopTerm.value
            });
            workshopTitleFr.value = '';
            workshopTitleEn.value = '';
            workshopLanguage.value = 'fr';
            workshopThemes.value = [];
            newWorkshopDialog.value = false;
            newWorkshopLoading.value = false;
            router.push(`/workshops/${workshop.id}/edit`);
        } catch {
            newWorkshopLoading.value = false;
        }
    }

    const gotoMyWorkshops = () => {
        router.push('/myWorkshops');
    }

    const gotoOpenDoors = () => {
        router.push('/openDoorsBPR');
    }

    const gotoMyCourses = () => {
        router.push('/myCourses');
    }

    const myActivityDialog = ref(false);
    const activityLoading = ref(false);
    const showMyHours =  async () => {
        activityLoading.value = true;
        myActivityDialog.value = true;
        await fetchMyActivity();
        activityLoading.value = false;
    }
</script>