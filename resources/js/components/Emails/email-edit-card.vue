<template>
    <v-card>
        <v-card-title class="d-flex justify-space-between align-center">
            <span>
                {{ $t('Edit email') }}
            </span>
            <span class="d-flex">
                <v-img max-width='35px' min-width='35px' class='mr-3' :style='language ? "filter: grayscale(80%)" : ""'
                    src="/images/flag en.png" contain />
                <v-switch density="compact" hide-details class="mr-3" color="primary" v-model='language'
                    @change="setlanguage" />
                <v-img max-width='35px' min-width='35px' :style='language ? "" : "filter: grayscale(80%)"'
                    src="/images/flag fr.png" contain />
            </span>
        </v-card-title>
        <v-card-text>
            <v-window v-model="languageWindow">
                <v-window-item :key="0" class="pt-2">
                    <v-text-field variant="outlined" label="Subject" v-model="email.subject_en" />
                    <v-text-field variant="outlined" label="Button text" v-model="email.data.buttonText_en" />
                    <v-text-field variant="outlined" label="Button link" v-model="email.data.url" />
                    <div>
                        <div class="text-caption text-captionColor">
                            Body
                        </div>
                        <Editor api-key="c6xujr454hv9o3u7uqat7dlla2v61j7n3syp29hhj0k4aeeu" :init="{
                    plugins: 'lists link image table code help wordcount', height: 300, resize: false
                }" v-model="email.data.body_en" />
                    </div>
                </v-window-item>
                <v-window-item :key="1" class="pt-2">
                    <v-text-field variant="outlined" label="Sujet" v-model="email.subject_fr" />
                    <v-text-field variant="outlined" label="Texte boutton" v-model="email.data.buttonText_fr" />
                    <v-text-field variant="outlined" label="Lien boutton" v-model="email.data.url" />
                    <div>
                        <div class="text-caption text-captionColor">
                            Message
                        </div>
                        <Editor api-key="c6xujr454hv9o3u7uqat7dlla2v61j7n3syp29hhj0k4aeeu" :init="{
                            plugins: 'lists link image table code help wordcount', height: 300, resize: false
                        }" v-model="email.data.body_fr" />
                    </div>
                </v-window-item>
            </v-window>
        </v-card-text>
        <v-card-actions>
            <v-spacer />
            <v-btn variant="text" color="error" :disabled="isLoading" @click="emit('closeDialog')">
                {{ $t('Cancel') }}
            </v-btn>
            <v-btn variant="flat" color="success" :loading="isLoading" @click="emit('updateEmail', email)">
                {{ $t('Submit') }}
            </v-btn>
        </v-card-actions>
    </v-card>
</template>
<script setup>
    import { ref, reactive } from "vue";
    import Editor from '@tinymce/tinymce-vue';

    const props = defineProps({ email: Object, isLoading: Boolean });
    const emit = defineEmits(['closeDialog', 'updateEmail']);

    const email = reactive(props.email);
    const language = ref(props.email.language == 'fr');
    const languageWindow = ref(language.value ? 1 : 0);
    const setlanguage = () => {
        email.language = language.value ? 'fr' : 'en';
        languageWindow.value = language.value ? 1 : 0;
    }
</script>