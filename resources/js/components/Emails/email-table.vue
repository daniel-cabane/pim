<template>
    <v-table hover v-if="isReady">
        <tbody>
            <tr v-for="email in emails">
                <td>{{ email[`subject_${email.language}`] }}</td>
                <td>
                    <v-chip label variant="outlined" color="success" append-icon="mdi-check" v-if="email.sent">
                        {{ $t('Sent on') }} {{ formatSchedule(email) }}
                    </v-chip>
                    <span v-else-if="email.schedule">
                        <v-chip label>
                            {{ $t('Scheduled for') }} {{ formatSchedule(email) }}
                        </v-chip>
                    </span>
                    <span class="text-grey" v-else>
                        <i>-- {{ $t('Not scheduled') }} --</i>
                    </span>
                </td>
                <td style="width:50px;text-align:center;">
                    <email-action-menu :email="email" @emailAction="emailAction" />
                </td>
            </tr>
        </tbody>
        <v-dialog width="450" v-model="scheduleDialog">
            <email-schedule-card :email="focusedEmail" :isLoading="isLoading" @closeDialog="scheduleDialog = false"
                @updateSchedule="handleSchedule" @sendMail="handleSend" />
        </v-dialog>
        <v-dialog width="650" v-model="previewDialog">
            <email-preview-card :email="focusedEmail" @closeDialog="previewDialog = false" />
        </v-dialog>
        <v-dialog width="650" v-model="editDialog">
            <email-edit-card :email="focusedEmail" :isLoading="isLoading" @closeDialog="editDialog = false"
                @updateEmail="handleUpdate" />
        </v-dialog>
        <v-dialog width="450" v-model="deleteDialog">
            <email-delete-card :email="focusedEmail" :isLoading="isLoading" @closeDialog="deleteDialog = false"
                @deleteEmail="handleDelete" />
        </v-dialog>
    </v-table>
</template>
<script setup>
    import { ref } from "vue";
    import { useEmailStore } from '@/stores/useEmailStore';
    import { storeToRefs } from 'pinia';
    import { useI18n } from 'vue-i18n';

    const { locale } = useI18n();

    const props = defineProps({ workshopId: { type: Number, nullable: true }, admin: { type: Boolean, default: false } });

    const EmailStore = useEmailStore();
    const { getWorkshopEmails, updateEmail, updateEmailSchedule, deleteEmail, sendEmail } = EmailStore;
    const { emails, isLoading, isReady } = storeToRefs(EmailStore);
    getWorkshopEmails(props.workshopId);

    const formatSchedule = email => {
        return (new Date(email.schedule)).toLocaleString(locale.value, { month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric' });
    }

    const focusedEmail = ref(null);
    const editDialog = ref(false);
    const handleUpdate = async email => {
        await updateEmail(email);
        editDialog.value = false;
    }
    const scheduleDialog = ref(false);
    const handleSchedule = async dateTime => {
        await updateEmailSchedule(focusedEmail.value.id, dateTime);
        scheduleDialog.value = false;
    }
    const handleSend = async email => {
        await sendEmail(email.id);
        scheduleDialog.value = false;
    }
    const deleteDialog = ref(false);
    const handleDelete = async mail => {
        await deleteEmail(focusedEmail.value.id)
        deleteDialog.value = false;
    }
    const previewDialog = ref(false);
    const emailAction = data => {
        focusedEmail.value = data.email;
        switch (data.action) {
            case 'schedule':
                scheduleDialog.value = true;
                break;
            case 'preview':
                previewDialog.value = true;
                break;
            case 'edit':
                editDialog.value = true;
                break;
            case 'delete':
                deleteDialog.value = true;
                break;
        }
    }
</script>