<template>
    <div>
        <div class="d-flex mb-3" v-if="admin">
            <v-dialog width="450" v-model="newSurveyDialog">
                <template v-slot:activator="{ props: activatorProps }">
                    <v-spacer />
                    <v-btn color="primary" append-icon="mdi-plus" v-bind="activatorProps">
                        {{ $t('New survey') }}
                    </v-btn>
                </template>
                <add-survey-card @closeDialog="newSurveyDialog = false" />
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
                    <td>
                        {{ survey.nbAnswers == 0 ? 'No' : survey.nbAnswers }}
                        answer<span v-if="survey.nbAnswers > 1">s</span>
                    </td>
                    <td style="width:50px;text-align:center;">
                        <survey-action-menu :survey="survey" @surveyAction="surveyAction" />
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
                @deleteOption="deleteOption" @deleteQuestion="deleteQuestion"/>
        </v-dialog>
        <v-dialog v-model="previewDialog" transition="dialog-bottom-transition" fullscreen>
            <survey-display-card :survey="focusedSurvey" showCloseButton @closeDialog="previewDialog=false;" />
        </v-dialog>
        <v-dialog fullscreen v-model="resultDialog">
            <survey-result-card :survey="focusedSurvey" :isLoading="isLoading" @closeDialog="resultDialog = false" />
        </v-dialog>
    </div>
</template>
<script setup>
    import { ref } from "vue";
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
    const { getAdminSurveys, getWorkshopSurveys, deleteSurvey, updateSurvey, sendSurvey, openSurvey, closeSurvey, getResults } = SurveyStore;
    const { surveys, isLoading } = storeToRefs(SurveyStore);
    if(props.workshopId){
        getWorkshopSurveys(props.workshopId);
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
            case 'result':
                showResultDialog(data.survey);
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
        focusedSurvey.value.questions.push({
            q_fr: 'Nouvelle question', q_en: 'New question', type: 'radio', required: false, options: [{ fr: 'Réponse A', en: 'Option A' }, { fr: 'Réponse B', en: 'Option B' }]
        });
    }
    const addOption = question => {
        question.options.push({ fr: 'Nouvelle réponse', en: 'New option' });
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
    const deleteQuestion = index => {
        focusedSurvey.value.questions.splice(index, 1);
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
    const handleSend = async sendEmail => {
        await sendSurvey(focusedSurvey.value, { sendEmail });
        sendDialog.value = false;
    }
    const resultDialog = ref(false);
    const showResultDialog = survey => {
        focusedSurvey.value = survey;
        if(!survey.results){
            getResults(survey);
        }
        resultDialog.value = true;
    }
</script>