<template>
    <v-tabs v-model="tab">
        <v-tab value="applicants">{{ $t('Students enrolled') }} ({{ workshop.applicants.length }})</v-tab>
        <v-tab value="surveys">{{ $t('Surveys') }}</v-tab>
        <v-tab value="emails">Emails</v-tab>
        <v-spacer />
        <v-dialog width="450" v-model="addDialog">
            <template v-slot:activator="{ props: activatorProps }">
                <v-btn class="mt-2" variant="outlined" size="small" color="primary" v-bind="activatorProps">
                    {{ addButtonDetails.text }}
                </v-btn>
            </template>
            <component :is="addButtonDetails.component" :workshopId="workshop.id" @closeDialog="addDialog = false" />
        </v-dialog>
    </v-tabs>
    <v-tabs-window v-model="tab">
        <v-tabs-window-item value="applicants">
            <v-row>
                <v-col cols="12" class="pt=0">
                    <v-data-table :headers="headers" :items="workshop.applicants" item-value="name">
                        <template v-slot:item="{ item }">
                            <tr>
                                <td>{{ item.name }}</td>
                                <td>{{ item.available ? $t('Yes') : $t('No') }}</td>
                                <td>{{ item.confirmed ? $t('Yes') : $t('No') }}</td>
                                <td>{{ item.comment }}</td>
                                <td class="d-flex align-center">
                                    <v-icon icon="mdi-pencil" class="mr-2" color="primary"
                                        @click="showEditApplicantDialog(item)" />
                                    <v-icon icon="mdi-close-octagon" color="error"
                                        @click="showremoveApplicantDialog(item)" />
                                </td>
                            </tr>
                        </template>
                    </v-data-table>
                </v-col>
            </v-row>
            <v-dialog v-model="editApplicantDialog" width="400">
                <v-card :title="focusedApplicant.name" :subtitle="focusedApplicant.email">
                    <v-card-text>
                        <v-text-field :label="$t('Name')" v-model="focusedApplicant.name" />
                        <div class="d-flex justify-space-around">
                            <v-switch v-model="focusedApplicant.available" :label="$t('Available')" color="primary" />
                            <v-switch v-model="focusedApplicant.confirmed" :label="$t('Confirmed')" color="primary" />
                        </div>
                        <v-text-field v-model="focusedApplicant.comment" :label="$t('Comment')" />
                    </v-card-text>
                    <div style="display:flex;justify-content:flex-end;" class="pa-3">
                        <v-btn variant="tonal" class="mr-3" color="error" :disabled="isLoading"
                            @click="editApplicantDialog = false">
                            {{ $t('Cancel') }}
                        </v-btn>
                        <v-btn color="success" :loading="isLoading" @click="handleEditApplicant">
                            {{ $t('Save') }}
                        </v-btn>
                    </div>
                </v-card>
            </v-dialog>
            <v-dialog v-model="removeApplicantDialog" width="350">
                <v-card :title="$t('Remove applicant')">
                    <v-card-text>
                        <div>
                            {{ $t('Are you sure you want to withdraw this applicant') }} ?
                        </div>
                        <v-checkbox :label="$t('Yes I\'m sure')" v-model="confirmRemoveApplicant" />
                    </v-card-text>
                    <div style="display:flex;justify-content:flex-end;" class="pa-3">
                        <v-btn variant="tonal" class="mr-3" color="primary" :disabled="isLoading"
                            @click="removeApplicantDialog = false">
                            {{ $t('Cancel') }}
                        </v-btn>
                        <v-btn color="error" :disabled="!confirmRemoveApplicant" :loading="isLoading"
                            @click="handleRemoveApplicant">
                            {{ $t('Remove') }}
                        </v-btn>
                    </div>
                </v-card>
            </v-dialog>
        </v-tabs-window-item>
        <v-tabs-window-item value="surveys" class="px-4">
            <survey-table :workshopId="workshop.id" />
        </v-tabs-window-item>
        <v-tabs-window-item value="emails" class="px-4">
            <email-table :workshopId="workshop.id" />
        </v-tabs-window-item>
    </v-tabs-window>
</template>
<script setup>
    import { ref, computed } from "vue";
    import { useI18n } from 'vue-i18n';
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    import { storeToRefs } from 'pinia';

    
    const props = defineProps({ workshop: Object });
    
    const { t } = useI18n();

    const workshopStore = useWorkshopStore();
    const { editApplicant, removeApplicant } = workshopStore;
    const { isLoading } = storeToRefs(workshopStore);

    const tab = ref('applicants');

    const headers = [
        { title: t('Student'), align: 'start', key: 'name' },
        { title: t('Available'), key: 'available' },
        { title: t('Confirmed'), key: 'confirmed' },
        { title: t('Comment'), key: 'comment' },
        { title: '', sortable: false, width: 10 }
    ];

    const focusedApplicant = ref(null);
    const editApplicantDialog = ref(false);
    const showEditApplicantDialog = applicant => {
        focusedApplicant.value = JSON.parse(JSON.stringify(applicant));
        focusedApplicant.value.available = !!focusedApplicant.value.available
        focusedApplicant.value.confirmed = !!focusedApplicant.value.confirmed
        editApplicantDialog.value = true;
    }
    const handleEditApplicant = async () => {
        await editApplicant(focusedApplicant.value);
        editApplicantDialog.value = false;
    }

    const removeApplicantDialog = ref(false);
    const confirmRemoveApplicant = ref(false);
    const showremoveApplicantDialog = applicant => {
        focusedApplicant.value = JSON.parse(JSON.stringify(applicant));
        focusedApplicant.value.available = !!focusedApplicant.value.available
        focusedApplicant.value.confirmed = !!focusedApplicant.value.confirmed
        removeApplicantDialog.value = true;
    }
    const handleRemoveApplicant = async () => {
        await removeApplicant(focusedApplicant.value);
        confirmRemoveApplicant.value = false;
        removeApplicantDialog.value = false;
    }

    const addButtonDetails = computed(() => {
        switch (tab.value) {
            case 'applicants':
                return { text: 'Add student', component: 'add-student-card' }
            case 'surveys':
                return { text: 'New survey', component: 'add-survey-card' }
            case 'emails':
                return { text: 'New email', component: 'add-email-card' }
        }
    });

    const addDialog = ref(false);
    const temp = ref('');
    const showStudentDialog = () => {
        temp.value = 'student';
        addDialog.value = true;
    }
    const showSurveyDialog = () => {
        temp.value = 'survey';
        addDialog.value = true;
    }
    const showEmailDialog = () => {
        temp.value = 'email';
        addDialog.value = true;
    }
</script>