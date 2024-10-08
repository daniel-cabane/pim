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
                    <v-select variant="outlined" label="Call to action button" :items="buttonOptions" item-props v-model="email.data.actionButton.value"/>
                    <v-text-field variant="outlined" label="Button text" v-model="email.data.actionButton.text_en" v-if="email.data.actionButton.value && email.data.actionButton.value != 'none'"/>
                    <v-text-field :rules="validUrl" variant="outlined" label="Button link (starting with https://)" v-model="email.data.actionButton.url" v-if="email.data.actionButton.value == 'custom'"/>
                    <div>
                        <div class="text-caption text-captionColor">
                            Body
                        </div>
                        <Editor api-key="c6xujr454hv9o3u7uqat7dlla2v61j7n3syp29hhj0k4aeeu" :init="{
                    menubar: false, toolbar: 'undo redo | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist | removeformat', height: 300, resize: false
                }" v-model="email.data.body_en" />
                    </div>
                </v-window-item>
                <v-window-item :key="1" class="pt-2">
                    <v-text-field variant="outlined" label="Sujet" v-model="email.subject_fr" />
                    <v-select variant="outlined" label="Bouton d'action" :items="buttonOptions" item-props v-model="email.data.actionButton.value"/>
                    <v-text-field variant="outlined" label="Texte boutton" v-model="email.data.actionButton.text_fr" v-if="email.data.actionButton.value && email.data.actionButton.value != 'none'"/>
                    <v-text-field :rules="validUrl" variant="outlined" label="Lien boutton (débutant par https://)" v-model="email.data.actionButton.url" v-if="email.data.actionButton.value == 'custom'"/>
                    <div>
                        <div class="text-caption text-captionColor">
                            Message
                        </div>
                        <div @focusin.stop>

                            <Editor api-key="c6xujr454hv9o3u7uqat7dlla2v61j7n3syp29hhj0k4aeeu" :init="{
                                menubar: false, toolbar: 'undo redo | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist | removeformat', height: 300, resize: false
                            }" v-model="email.data.body_fr"/>
                        </div>
                    </div>
                </v-window-item>
            </v-window>
        </v-card-text>
        <v-card-actions>
            <v-spacer />
            <v-btn variant="text" color="error" :disabled="isLoading" @click="emit('closeDialog')">
                {{ $t('Cancel') }}
            </v-btn>
            <v-btn variant="flat" color="success" :loading="isLoading" @click="emit('updateEmail', email);">
                {{ $t('Submit') }}
            </v-btn>
        </v-card-actions>
    </v-card>
</template>
<script setup>
    import { ref, reactive, computed } from "vue";
    import Editor from '@tinymce/tinymce-vue';
    import { useI18n } from 'vue-i18n';

    const { t } = useI18n();

    const validUrl = [
        value => {
            const urlPattern = /^https:\/\/([\da-z.-]+)\.([a-z.]{2,6})([/\w .-]*)*\/?$/;
            return urlPattern.test(value) ? true : t('Invalid URL');
        }
    ];
    const props = defineProps({ email: Object, isLoading: Boolean, surveys: Array });
    const emit = defineEmits(['closeDialog', 'updateEmail']);

    const email = reactive(props.email);
    const language = ref(props.email.language == 'fr');
    const languageWindow = ref(language.value ? 1 : 0);
    const setlanguage = () => {
        email.language = language.value ? 'fr' : 'en';
        languageWindow.value = language.value ? 1 : 0;
    }
    const buttonOptions = computed(() => {
        const options = [
            { title: t('No button'), value: 'none' }
        ];
        props.surveys.forEach(s => options.push({ title: `${t('Survey')} - ${s.mainTitle}`, value: s.id }));
        options.push({ title: t('Custom url'), value: 'custom' });
        return options;
    });
</script>