<template>
    <v-card flat class="pa-4">
        <!-- Header: New email form -->
        <v-card-title class="d-flex align-center justify-space-between px-0">
            <span>{{ $t('Emails') }}</span>
            <v-dialog width="400" v-model="newEmailDialog">
                <template v-slot:activator="{ props: activatorProps }">
                    <v-btn variant="outlined" color="primary" prepend-icon="mdi-plus" v-bind="activatorProps">
                        {{ $t('New email') }}
                    </v-btn>
                </template>
                <v-card :title="$t('New email')">
                    <v-card-text>
                        <v-text-field
                            variant="outlined"
                            :label="$t('Title')"
                            v-model="newSubject"
                            validate-on="blur"
                            :rules="[v => !!v && v.length >= 3 || $t('The title must at least 5 characters long')]"
                        />
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer />
                        <v-btn variant="text" color="error" @click="newEmailDialog = false">
                            {{ $t('Cancel') }}
                        </v-btn>
                        <v-btn variant="flat" color="primary" :loading="isLoading" :disabled="newSubject.length < 3" @click="handleAddEmail">
                            {{ $t('Submit') }}
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-card-title>

        <!-- Emails table -->
        <v-table hover v-if="isReady">
            <thead>
                <tr>
                    <th>{{ $t('Title') }}</th>
                    <th>{{ $t('Status') }}</th>
                    <th style="width: 50px;"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="emails.length === 0">
                    <td colspan="3" class="text-center text-captionColor py-6">
                        <i>{{ $t('No emails yet') }}</i>
                    </td>
                </tr>
                <tr v-for="email in emails" :key="email.id">
                    <td>{{ email[`subject_${email.language}`] }}</td>
                    <td>
                        <v-chip v-if="email.sent" label color="success" variant="outlined" append-icon="mdi-check"
                            @click="showSentToDialog(email)">
                            {{ $t('sent') }}
                        </v-chip>
                        <v-chip v-else-if="email.schedule" label color="info" variant="outlined" append-icon="mdi-clock-outline">
                            {{ $t('confirmed') }}
                        </v-chip>
                        <v-chip v-else label color="default" variant="outlined">
                            {{ $t('draft') }}
                        </v-chip>
                    </td>
                    <td style="text-align: center;">
                        <email-action-menu :email="email" @emailAction="emailAction" />
                    </td>
                </tr>
            </tbody>
        </v-table>
        <div v-else class="d-flex justify-center py-8">
            <v-progress-circular indeterminate color="primary" />
        </div>

        <!-- Dialogs -->
        <v-dialog width="450" v-model="scheduleDialog">
            <email-schedule-card :email="focusedEmail" :isLoading="isLoading" @closeDialog="scheduleDialog = false"
                @updateSchedule="handleSchedule" @sendMail="handleSend" />
        </v-dialog>

        <v-dialog width="650" v-model="previewDialog">
            <email-preview-card :email="focusedEmail" @closeDialog="previewDialog = false" />
        </v-dialog>

        <v-dialog width="750" v-model="editDialog">
            <email-edit-card :email="focusedEmail" :isLoading="isLoading" @closeDialog="editDialog = false"
                :surveys="[]" @updateEmail="handleUpdate" />
        </v-dialog>

        <v-dialog width="450" v-model="deleteDialog">
            <email-delete-card :email="focusedEmail" :isLoading="isLoading" @closeDialog="deleteDialog = false"
                @deleteEmail="handleDelete" />
        </v-dialog>

        <v-dialog width="350" v-model="sentToDialog">
            <v-card :loading="isLoading" :title="focusedEmail ? focusedEmail.subject_en : ''" :subtitle="$t('Sent to')">
                <v-card-text class="d-flex align-center flex-wrap">
                    <v-card class="ma-1 pa-2" width="300" v-for="recipient in (focusedEmail && focusedEmail.sentTo) ? focusedEmail.sentTo : []" :key="recipient.id">
                        <div class="d-flex justify-space-between text-body text-subtitle-1">
                            <span>{{ recipient.name }}</span>
                        </div>
                        <div class="text-caption text-captionColor">
                            {{ recipient.email }}
                        </div>
                    </v-card>
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" color="primary" @click="sentToDialog = false">{{ $t('Close') }}</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-card>
</template>

<script setup>
    import { ref } from 'vue';
    import { useEmailStore } from '@/stores/useEmailStore';
    import { storeToRefs } from 'pinia';
    import EmailActionMenu from '@/components/Emails/email-action-menu.vue';
    import EmailEditCard from '@/components/Emails/email-edit-card.vue';
    import EmailPreviewCard from '@/components/Emails/email-preview-card.vue';
    import EmailScheduleCard from '@/components/Emails/email-schedule-card.vue';
    import EmailDeleteCard from '@/components/Emails/email-delete-card.vue';

    const props = defineProps({ tournament: Object });

    const emailStore = useEmailStore();
    const {
        getTournamentEmails, addTournamentEmail,
        updateEmail, updateEmailSchedule, deleteEmail, sendEmail, getEmailSentTo
    } = emailStore;
    const { emails, isLoading, isReady } = storeToRefs(emailStore);

    getTournamentEmails(props.tournament.slug);

    // New email creation
    const newEmailDialog = ref(false);
    const newSubject = ref('');
    const handleAddEmail = async () => {
        await addTournamentEmail(props.tournament.slug, newSubject.value);
        newSubject.value = '';
        newEmailDialog.value = false;
    };

    // Action dialogs
    const focusedEmail = ref(null);
    const editDialog = ref(false);
    const scheduleDialog = ref(false);
    const previewDialog = ref(false);
    const deleteDialog = ref(false);
    const sentToDialog = ref(false);

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
    };

    const showSentToDialog = async email => {
        focusedEmail.value = email;
        if (!email.sentTo) {
            await getEmailSentTo(email);
        }
        sentToDialog.value = true;
    };

    const handleUpdate = async email => {
        await updateEmail(email);
        editDialog.value = false;
    };

    const handleSchedule = async dateTime => {
        await updateEmailSchedule(focusedEmail.value.id, dateTime);
        scheduleDialog.value = false;
    };

    const handleSend = async email => {
        await sendEmail(email.id);
        scheduleDialog.value = false;
    };

    const handleDelete = async () => {
        await deleteEmail(focusedEmail.value.id);
        deleteDialog.value = false;
    };
</script>
