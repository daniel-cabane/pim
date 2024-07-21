<template>
    <div>
        <div class="d-flex mb-3">
            <v-dialog width="450" v-model="newSurveyDialog">
                <template v-slot:activator="{ props: activatorProps }">
                    <span class="text-caption text-captionColor" v-if="!admin">
                        {{ $t('Surveys') }}
                    </span>
                    <v-spacer />
                    <v-btn color="primary" append-icon="mdi-plus" :size="admin ? 'default' : 'x-small'"
                        v-bind="activatorProps">
                        {{ $t('Add a survey') }}
                    </v-btn>
                </template>
                <v-card :title="$t('Add a survey')">
                    <v-card-text>
                        <v-select variant="outlined" :items="languages" :label="$t('Languages')"
                            v-model="newSurvey.language" />
                        <v-text-field variant="outlined" label="Titre (fr)" v-model="newSurvey.title_fr"
                            v-if="newSurvey.language != 'en'" />
                        <v-text-field variant="outlined" label="Title (en)" v-model="newSurvey.title_en"
                            v-if="newSurvey.language != 'fr'" />
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer />
                        <v-btn color="error" variant="text" :disabled="isLoading" @click="newSurveyDialog = false">
                            {{ $t('Cancel') }}
                        </v-btn>
                        <v-btn color="primary" variant="flat" :loading="isLoading" @click="handleNnewSurvey">
                            {{ $t('Submit') }}
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </div>
        <v-data-table hover :headers="headers" :items="surveys" item-value="name" items-per-page="25"
            :items-per-page-options="ipp" v-if="admin">
            <template v-slot:item="{ item }">
                <tr>
                    <td class="text-center">
                        {{ item.mainTitle }}
                    </td>
                    <td class="text-center">
                        {{ (languages.find(l => l.value == item.options.language)).title }}
                    </td>
                    <td class="text-center">
                        {{ item.author.name }}
                    </td>
                    <td class="text-center">
                        {{ $t(item.status) }}
                    </td>
                    <td class="text-center">
                        {{ item.questions.length }}
                    </td>
                    <td class="text-center" style="cursor:pointer;" @click="navigateToWorkshop(item.workshopId)">
                        {{ item.workshopName ? item.workshopName : '--' }}
                    </td>
                    <td class="pr-0 text-center" style="width:50px;">
                        <survey-action-menu :survey="item" @surveyAction="surveyAction" />
                        <!-- <v-menu>
                            <template v-slot:activator="{ props }">
                                <v-btn icon="mdi-dots-vertical" variant="text" size="small" v-bind="props"></v-btn>
                            </template>
                            <v-list>
                                <v-list-item @click="showSendDialog(item)">
                                    <template v-slot:prepend>
                                        <v-icon icon="mdi-send" color="secondary" />
                                    </template>
                                    <v-list-item-title>
                                        Send
                                    </v-list-item-title>
                                </v-list-item>
                                <v-list-item @click="showPreviewDialog(item)">
                                    <template v-slot:prepend>
                                        <v-icon icon="mdi-eye" color="success" />
                                    </template>
                                    <v-list-item-title>
                                        Preview
                                    </v-list-item-title>
                                </v-list-item>
                                <v-list-item @click="showEditDialog(item)">
                                    <template v-slot:prepend>
                                        <v-icon icon="mdi-pencil" color="primary" />
                                    </template>
                                    <v-list-item-title>
                                        Edit
                                    </v-list-item-title>
                                </v-list-item>
                                <v-list-item @click="showDeleteDialog(item)">
                                    <template v-slot:prepend>
                                        <v-icon icon="mdi-delete" color="error" />
                                    </template>
                                    <v-list-item-title>
                                        Delete
                                    </v-list-item-title>
                                </v-list-item>
                            </v-list>
                        </v-menu> -->
                        <!-- <v-icon size="small" class="mr-3" color="success" @click="showPreviewDialog(item)">
                            mdi-eye
                        </v-icon>
                        <v-icon size="small" class="mr-3" color="primary" @click="showEditDialog(item)">
                            mdi-pencil
                        </v-icon>
                        <v-icon size="small" color="error" @click="showDeleteDialog(item)"
                            v-if="item.workshop_id == null">
                            mdi-delete
                        </v-icon> -->
                    </td>
                </tr>
            </template>
        </v-data-table>
        <v-table hover v-else>
            <tbody>
                <tr v-for="survey in surveys">
                    <td>{{ survey.mainTitle }}</td>
                    <td>{{ survey.questions.length }} question<span v-if="survey.questions.length>1">s</span></td>
                    <td>{{ survey.status }}</td>
                    <td style="width:50px;text-align:center;">
                        <survey-action-menu :survey="survey" @surveyAction="surveyAction" />
                        <!-- <v-menu>
                            <template v-slot:activator="{ props }">
                                <v-btn icon="mdi-dots-vertical" variant="text" size="small" v-bind="props"></v-btn>
                            </template>
                            <v-list>
                                <v-list-item @click="showSendDialog(survey)" v-if="survey.status == 'draft'">
                                    <template v-slot:prepend>
                                        <v-icon icon="mdi-send" color="success" />
                                    </template>
                                    <v-list-item-title>
                                        {{ $t('Send') }}
                                    </v-list-item-title>
                                </v-list-item>
                                <v-list-item @click="showSendDialog(survey)" v-if="survey.status == 'open'">
                                    <template v-slot:prepend>
                                        <v-icon icon="mdi-close-box" color="error" />
                                    </template>
                                    <v-list-item-title>
                                        {{ $t('Close') }}
                                    </v-list-item-title>
                                </v-list-item>
                                <v-list-item @click="showSendDialog(survey)" v-if="survey.status == 'closed'">
                                    <template v-slot:prepend>
                                        <v-icon icon="mdi-reload" color="success" />
                                    </template>
                                    <v-list-item-title>
                                        {{ $t('Re-open') }}
                                    </v-list-item-title>
                                </v-list-item>
                                <v-list-item @click="showPreviewDialog(survey)">
                                    <template v-slot:prepend>
                                        <v-icon icon="mdi-eye" color="secondary" />
                                    </template>
                                    <v-list-item-title>
                                        {{ $t('Preview') }}
                                    </v-list-item-title>
                                </v-list-item>
                                <v-list-item @click="showEditDialog(survey)">
                                    <template v-slot:prepend>
                                        <v-icon icon="mdi-pencil" color="primary" />
                                    </template>
                                    <v-list-item-title>
                                        {{ $t('Edit') }}
                                    </v-list-item-title>
                                </v-list-item>
                                <v-list-item @click="showDeleteDialog(survey)">
                                    <template v-slot:prepend>
                                        <v-icon icon="mdi-delete" color="error" />
                                    </template>
                                    <v-list-item-title>
                                        {{ $t('Delete') }}
                                    </v-list-item-title>
                                </v-list-item>
                            </v-list>
                        </v-menu> -->
                    </td>
                </tr>
            </tbody>
        </v-table>
        <v-dialog width="300" v-model="sendDialog">
            <survey-send-card :survey="focusedSurvey" :isLoading="isLoading" @closeDialog="sendDialog = false"
                @sendSurvey="handleSend" />
        </v-dialog>
        <v-dialog width="300" v-model="deleteDialog">
            <survey-delete-card :survey="focusedSurvey" :isLoading="isLoading" @closeDialog="deleteDialog = false"
                @deleteSurvey="handleDelete" />
        </v-dialog>
        <v-dialog width="850" scrollable v-model="editDialog">
            <survey-edit-card :survey="focusedSurvey" :isLoading="isLoading" @closeDialog="editDialog = false"
                @updateSurvey="handleEditSurvey" @addQuestion="addQuestion" @addOption="addOption"
                @deleteOption="deleteOption" />
        </v-dialog>
        <v-dialog v-model="previewDialog" transition="dialog-bottom-transition" fullscreen>
            <survey-display-card :survey="focusedSurvey" showCloseButton @closeDialog="previewDialog=false;" />
        </v-dialog>
    </div>
</template>
<script setup>
    import { ref, reactive } from "vue";
    import { useSurveyStore } from '@/stores/useSurveyStore';
    import { storeToRefs } from 'pinia';
    import { useI18n } from 'vue-i18n';
    import useNotifications from '@/composables/useNotifications';
    import { useRouter } from 'vue-router';

    const router = useRouter();

    const { addNotification } = useNotifications();

    const props = defineProps({ workshopId: { type: Number, nullable: true }, admin: { type: Boolean, default: false }});

    const { t } = useI18n();

    const SurveyStore = useSurveyStore();
    const { createSurvey, getAdminSurveys, getWorkshopSurveys, deleteSurvey, updateSurvey, sendSurvey, openSurvey, closeSurvey } = SurveyStore;
    const { surveys, isLoading } = storeToRefs(SurveyStore);
    if(props.workshopId){
        getWorkshopSurveys(props.workshopId)
    } else if(props.admin) {
        getAdminSurveys();
    }

    const headers = ref([
        { title: t('Title'), align: 'center', key: 'mainTitle' },
        { title: t('language'), align: 'center', key: 'options.language' },
        { title: t('Author'), align: 'center', key: 'author.name' },
        { title: t('Status'), align: 'center', key: 'status' },
        { title: 'Nb questions', align: 'center', sortable: false },
        { title: t('Workshop'), align: 'center', sortable: false },
        { title: t('Actions'), align: 'center', sortable: false, width: 100 }
    ]);
    const ipp = [25, 50, 100];

    const languages = ref([
        { title: 'Français', value: 'fr' }, { title: 'English', value: 'en' }, { title: t('Both'), value: 'both' }
    ]);
    const newSurveyDialog = ref(false);
    const newSurvey = reactive({ title_fr: '', title_en: '', language: 'fr' });
    const handleNnewSurvey = async () => {
        await createSurvey(newSurvey);
        newSurveyDialog.value = false;
        newSurvey.title_fr = '';
        newSurvey.title_en = '';
        newSurvey.language = 'fr';
    }

    const surveyAction = data => {
        switch (data.action) {
            case 'send':
                showSendDialog(data.survey);
                break;
            case 'close':
                closeSurvey(data.survey);
                break;
            case 'reopen':
                openSurvey(data.survey);
                break;
            case 'preview':
                showPreviewDialog(data.survey);
                break;
            case 'edit':
                showEditDialog(data.survey);
                break;
            case 'delete':
                showDeleteDialog(data.survey);
                break;
        }
    }

    const focusedSurvey = ref(null);
    const editDialog = ref(false)
    const showEditDialog = survey => {
        focusedSurvey.value = JSON.parse(JSON.stringify(survey));
        editDialog.value = true;
    }
    const handleEditSurvey = async () => {
        await updateSurvey(focusedSurvey.value);
        editDialog.value = false;
    }
    const deleteDialog = ref(false)
    const showDeleteDialog = survey => {
        focusedSurvey.value = survey;
        deleteDialog.value = true;
    }
    const handleDelete = async () => {
        await deleteSurvey(focusedSurvey.value);
        deleteDialog.value = false;
    }
    const addQuestion = () => {
        const question = focusedSurvey.value.options.language == 'fr' ? 'Nouvelle question' : 'New question'
        focusedSurvey.value.questions.push({
            q_fr: 'Nouvelle question', q_en: 'New question', type: 'radio', options: [{ fr: 'option A', en: 'option A' }, { fr: 'option B', en: 'option B' }]
        });
    }
    const addOption = question => {
        question.options.push({ fr: 'Nouvelle option', en: 'New option' });
    }
    const deleteOption = data => {
        const options = [];
        Object.values(data.question.options).forEach((o, i) => {
            if (i != data.index) {
                options.push(o);
            }
        });
        if (options.length > 0) {
            data.question.options = options;
        } else {
            addNotification({ text: 'You need at least one option', type: 'warning' });
        }
    }
    const navigateToWorkshop = id => {
        router.push(`/workshops/${id}`);
    }
    const previewDialog = ref(false);
    const showPreviewDialog = survey => {
        focusedSurvey.value = survey;
        previewDialog.value = true;
    }
    const sendDialog = ref(false);
    const showSendDialog = survey => {
        focusedSurvey.value = survey;
        sendDialog.value = true;
    }
    const handleSend = async () => {
        await sendSurvey(focusedSurvey.value);
        sendDialog.value = false;
    }
</script>