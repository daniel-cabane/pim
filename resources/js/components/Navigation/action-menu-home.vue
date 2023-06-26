<template>
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
                      v-model="post.title"
                      :label="$t('Title')" 
                      variant="outlined"
                      validate-on="blur"
                    />
                    <v-textarea 
                        :rules="[rules.required, rules.minLengthDescription]"
                        v-model="post.description"
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
    </v-list>
</template>
<script setup>
    import { ref, reactive } from 'vue';
    import { useRouter } from 'vue-router';

    let newPostDialog = ref(false);
    let post = reactive({title: '', description: ''});

    let rules = {
        required: value => !!value || 'Required.',
        minLengthTitle: value => value.length >= 8 || 'The title must at least 8 characters long',
        minLengthDescription: value => value.length >= 20 || 'The description must at least 20 characters long',
    };

    const gotoMyPosts = () => {
        router.push('/myPosts');
    }

    const router = useRouter();
    let newPostLoading = ref(false);
    const submitNewPost = async () => {
        newPostLoading.value = true;
        let res = await axios.post('/api/posts', post);

        newPostLoading.value = false;
        newPostDialog.value = false;
        router.push(`/posts/${res.data.slug}/edit`);
    }
</script>