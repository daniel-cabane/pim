<template>
    <v-card>
        <v-card-title class="d-flex justify-space-between">
            <span>{{ survey.mainTitle }}</span>
            <v-btn color="primary" variant="tonal" append-icon="mdi-plus" @click="emit('addQuestion')">
                {{ $t('Add question') }}
            </v-btn>
        </v-card-title>
        <v-card-text>
            <v-expansion-panels>
                <v-expansion-panel>
                    <v-expansion-panel-title class="d-flex">
                        <span class="text-h6">Options</span>
                        <v-spacer />
                        <v-img :src="`/images/flag ${survey.options.language}.png`" :max-width="30"
                            class="mr-2" />
                    </v-expansion-panel-title>
                    <v-expansion-panel-text>
                        <div style="display:flex;gap:15px;">
                            <v-select variant="outlined" style="flex:1" :items="languages" :label="$t('Languages')"
                                v-model="survey.options.language" />
                            <v-select variant="outlined" style="flex:1" :items="['closed', 'open']"
                                :label="$t('Status')" v-model="survey.status" />
                        </div>
                        <div style="display:flex;gap:15px;">
                            <div style="flex:1" v-if="survey.options.language != 'fr'">
                                <v-text-field variant="outlined" label="Title (en)"
                                    v-model="survey.options.title_en" />
                            </div>
                            <div style="flex:1" v-if="survey.options.language != 'en'">
                                <v-text-field variant="outlined" label="Titre (fr)"
                                    v-model="survey.options.title_fr" />
                            </div>
                        </div>
                        <div>
                            <v-textarea variant="outlined" label="Description (en)"
                                v-model="survey.options.description_en"
                                v-if="survey.options.language != 'fr'" />
                            <v-textarea variant="outlined" label="Description (fr)"
                                v-model="survey.options.description_fr"
                                v-if="survey.options.language != 'en'" />
                        </div>
                    </v-expansion-panel-text>
                </v-expansion-panel>
            </v-expansion-panels>
            <question-edit-form v-for="(question, index) in survey.questions" :question="question"
                :language="survey.options.language" :index="index" @addOption="emit('addOption', question)"
                @deleteOption="emit('deleteOption')" />
        </v-card-text>
        <v-card-actions>
            <v-spacer />
            <v-btn variant="text" color="error" :disabled="isLoading" @click="emit('closeDialog')">
                {{ $t('Cancel') }}
            </v-btn>
            <v-btn variant="flat" color="success" :loading="isLoading" @click="emit('updateSurvey')">
                {{ $t('Submit') }}
            </v-btn>
        </v-card-actions>
    </v-card>
</template>
<script setup>
    import { ref } from "vue";
    import { useI18n } from 'vue-i18n';

    const { t } = useI18n();

    const props = defineProps({ survey: Object,  isLoading: Boolean});
    const emit = defineEmits(['closeDialog', 'updateSurvey', 'addQuestion', 'addOption', 'deleteOption']);

    const languages = ref([
        { title: 'Français', value: 'fr' }, { title: 'English', value: 'en' }, { title: t('Both'), value: 'both' }
    ]);
</script>