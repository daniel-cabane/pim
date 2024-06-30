<template>
    <div style="display:flex;flex-wrap:nowrap;max-width:100%">
        <div style="flex:1;">
            <v-card-title class="pb-0" style="text-overflow:ellipsis;">
                {{ title }}
            </v-card-title>
            <v-card-subtitle class="font-italic">
                {{ $t('By') }} {{ workshop.teacher }}
            </v-card-subtitle>
        </div>
        <div class="text-right pa-2">
            <div class="d-flex align-center">
                <v-img src="/images/flag fr.png" :width="30" class="mr-2"
                    v-if="['fr', 'both'].includes(workshop.language)" />
                <v-img src="/images/flag en.png" :width="30" class="mr-2"
                    v-if="['en', 'both'].includes(workshop.language)" />
                <v-chip variant="elevated" theme="dark" size="small"
                    :color="workshop.campus == 'BPR' ? 'blue' : 'red'">
                    {{ workshop.campus }}
                </v-chip>
            </div>
            <v-chip label :variant="workshop.status == 'draft' ? 'tonal' : 'elevated'" theme="dark" class="mt-1"
                :color="statusColor[workshop.status]" :text="$t(workshop.status) " v-if="workshop.editable" />
        </div>
    </div>
    <v-card-text>
        <div v-html="description" />
        <v-container fluid class="px-0">
            <v-row>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-captionColor">
                        {{ $t('Sessions') }}
                    </div>
                    <div v-for="(session, index) in workshop.details.schedule" :key="index">
                        {{ $t(session.day) }} {{ $t('from') }} {{ session.start }} {{ $t('to') }} {{ session.finish }}
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-captionColor">
                        {{ $t('Start date') }}
                    </div>
                    <div>
                        {{ workshop.startDate ? workshop.formatedStartDate : $t(workshop.formatedStartDate) }}
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-captionColor">
                        {{ $t('Nb of sessions') }}
                    </div>
                    <div>
                        {{ workshop.details.nbSessions }}
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-captionColor">
                        {{ $t('Room nb') }}
                    </div>
                    <div>
                        {{ workshop.details.roomNb }}
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-captionColor">
                        {{ $t('Language') }}
                    </div>
                    <div>
                        {{ $t(spellLanguage) }}
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-captionColor">
                        {{ $t('Themes') }}
                    </div>
                    <div>
                        <workshop-themes-chips :themes="workshop.themes" size="small" />
                    </div>
                </v-col>
            </v-row>
            <v-row v-if="user.is.teacher">
                <v-col cols="12" class="text-h6 text-captionColor pb-0">
                    {{ $t('Students enrolled') }} ({{ workshop.applicants.length }})
                </v-col>
                <v-col cols="12" class="pt=0">
                    <v-data-table :headers="headers" :items="workshop.applicants" item-value="name">
                        <template v-slot:item="{ item }">
                            <tr>
                                <td class="pr-0">
                                    <v-icon class="mr-3" size="small" @click="seeApplicant(item)">
                                        mdi-eye
                                    </v-icon>
                                </td>
                                <td>{{ item.name }}</td>
                                <td>{{ item.available ? 'Yes' : 'No' }}</td>
                                <td>{{ item.confirmed ? 'Yes' : 'No' }}</td>
                                <td>{{ item.comment }}</td>
                            </tr>
                        </template>
                    </v-data-table>
                </v-col>
            </v-row>
            <v-dialog v-model="focusedApplicantDialog" width="400">
                <v-card :title="focusedApplicant.name" :subtitle="focusedApplicant.email">
                    <v-card-text>
                        <v-text-field :label="$t('Name')" v-model="focusedApplicant.name" />
                    </v-card-text>
                    <div style="display:flex;justify-content:flex-end;" class="pa-3">
                        <v-btn variant="tonal" class="mr-3" color="error" @click="focusedApplicantDialog = false">
                            {{ $t('Cancel') }}
                        </v-btn>
                        <v-btn color="success" theme="dark"
                            @click="emit('editApplicantName', { id: focusedApplicant.id, name: focusedApplicant.name }); focusedApplicantDialog = false;">
                            {{ $t('Save') }}
                        </v-btn>
                    </div>
                </v-card>
            </v-dialog>
        </v-container>
    </v-card-text>
</template>
<script setup>
    import { computed, ref, defineEmits } from 'vue';
    import usePickWorkshopLg from '@/composables/usePickWorkshopLg';
    import { useAuthStore } from '@/stores/useAuthStore';

    const props = defineProps({ workshop: Object });

    const { title, description } = usePickWorkshopLg(props.workshop);

    const { user } = useAuthStore();

    const spellLanguage = computed(() => {
        if(props.workshop.language == 'fr'){
            return 'French'
        }
        if (props.workshop.language == 'en') {
            return 'English'
        }

        return 'Both'
    });

    const statusColor = { draft: 'secondary', submitted: 'warning', confirmed: 'success' }

    const headers = [
        { title: '', sortable: false, width: 10 },
        { title: 'Student', align: 'start', key: 'name' },
        { title: 'Available', key: 'available' },
        { title: 'Confirmed', key: 'confirmed' },
        { title: 'Comment', key: 'comment' }
    ];

    const focusedApplicant = ref(null);
    const focusedApplicantDialog = ref(false);
    const seeApplicant = applicant => {
        focusedApplicant.value = applicant;
        focusedApplicantDialog.value = true;
    }
    const emit = defineEmits(['editApplicantName']);
</script>