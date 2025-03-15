<template>
    <v-container style="max-width:1168px;">
        <div class="d-flex justify-space-between align-center mb-4">
            <span class="text-h5 text-grey">
                {{ $t('π room open doors') }}
            </span>
            <v-btn color="primary" @click="showAddDialog" :disabled="isLoading">
                {{ $t('Add visit') }}
            </v-btn>
        </div>
        <v-data-table :items="visits" :headers="headers" :items-per-page="25" :items-per-page-options="ippo">
            <template v-slot:item="{ item }">
                <tr>
                    <td>{{ new Date(item.dateTime).toLocaleDateString(locale, { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) }}</td>
                    <td>{{ item.tagNb }}</td>
                    <td>{{ item.user.name ? `${item.user.name} (${item.user.level}${item.user.section})` : '--' }}</td>
                    <td>{{ item.session ? item.session.teacher.name : '--' }}</td>
                    <td>
                        <v-icon color="primary" icon="mdi-pencil" class="mr-3" :disabled="isLoading" @click="showEditDialog(item)"/>
                        <v-icon color="error" icon="mdi-delete" :disabled="isLoading" @click="showDeleteDialog(item)"/>
                        <v-chip size="x-small" :text="$t('Edited')" class="ml-3" color="success" variant="flat" v-if="item.data && item.data.edited"/>
                    </td>
                </tr>
            </template>
        </v-data-table>

        <v-dialog width="500" v-model="addDialog">
            <v-card :title="$t('Add visit')">
                <v-card-text>
                    <div class="text-center text-grey text-subheading mb-3">
                        {{ $t('Fill at least one field') }}
                    </div>
                    <v-text-field :label="$t('Email')" variant="outlined" v-model="focusedVisit.email"/>
                    <v-text-field :label="$t('Tag Number')" variant="outlined" v-model="focusedVisit.tagNb"/>
                </v-card-text>
                <v-card-actions>
                    <v-spacer/>
                    <v-btn :text="$t('Close')" color="error" variant="tonal" @click="addDialog=false"/>
                    <v-btn :text="$t('Save')" color="success" variant="flat" @click="processNewVisit"/>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog width="500" v-model="editDialog">
            <v-card :title="$t('Edit visit')">
                <v-card-text>
                    <v-tabs density="compact" v-model="visitTab">
                        <v-tab value="email">{{ $t('Email') }}</v-tab>
                        <v-tab value="tagNb">{{ $t('Tag Number') }}</v-tab>
                    </v-tabs>
                    <v-tabs-window v-model="visitTab" class="pt-4" style="min-height:95px;max-height:95px;">
                        <v-tabs-window-item value="email">
                            <v-text-field :label="$t('Email')" variant="outlined" v-model="focusedVisit.email"/>
                        </v-tabs-window-item>
                        <v-tabs-window-item value="tagNb">
                            <v-text-field :label="$t('Tag Number')" variant="outlined" v-model="focusedVisit.tagNb"/>
                        </v-tabs-window-item>
                    </v-tabs-window>
                </v-card-text>
                <v-card-actions>
                    <v-spacer/>
                    <v-btn :text="$t('Close')" color="error" variant="tonal" @click="editDialog=false"/>
                    <v-btn :text="$t('Confirm')" color="primary" variant="flat" @click="processEditVisit"/>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog width="500" v-model="deleteDialog">
            <v-card :title="$t('Delete visit')" :subtitle="focusedVisit.tagNb">
                <v-card-text>
                    <div class="text-subheading">
                        {{ focusedVisit.user ? focusedVisit.user.name : '' }}
                    </div>
                    <div class="mb-3">
                        {{ new Date(focusedVisit.dateTime).toLocaleDateString(locale, { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) }}
                    </div>
                    <div>
                        {{ $t('Are you sure you want to delete this visit ?') }}
                    </div>
                </v-card-text>
                <v-card-actions>
                    <v-spacer/>
                    <v-btn :text="$t('Close')" color="primary" variant="tonal" @click="deleteDialog=false"/>
                    <v-btn :text="$t('Delete')" color="error" variant="flat" @click="deleteVisit(focusedVisit.id);deleteDialog=false"/>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>
<script setup>
    import { ref } from "vue";
    import { useOpenDoorStore } from '@/stores/useOpenDoorStore';
    import { storeToRefs } from 'pinia';
    import { useI18n } from 'vue-i18n';

    const { locale } = useI18n();

    const openDoorStore = useOpenDoorStore();
    const { getRecentVisits, deleteVisit, editVisitByEmail, editVisitByTagNb, newVisit } = openDoorStore;
    const { visits, isLoading } = storeToRefs(openDoorStore);

    getRecentVisits();

    const headers = ref([
        { title: 'Date and time', key: 'dateTime', align: 'start'},
        { title: 'Tag number', key: 'tagNb', align: 'start'},
        { title: 'Student', key: 'user.name'},
        { title: 'Teacher', key: 'teacher.name'},
        { title: '', key: '', sortable: false}
    ]);

    const ippo = [{value: 25, title: '25'}, {value: 50, title: '50'}, {value: -1, title: 'All'}];

    const focusedVisit = ref(null);
    
    const addDialog = ref(false);
    const showAddDialog = () => {
        focusedVisit.value = {name: '', email: '', tag_number: ''};
        addDialog.value = true;
    }
    const processNewVisit = async () => {
        await newVisit(focusedVisit.value);
        addDialog.value = false;
    }
    
    const editDialog = ref(false);
    const visitTab = ref('email');
    const showEditDialog = visit => {
        focusedVisit.value = {
            id: visit.id,
            email: visit.user ? visit.user.email : '',
            tagNb: visit.tagNb
        };
        editDialog.value = true;
    }
    const processEditVisit = async () => {
        if(visitTab.value == 'email'){
            await editVisitByEmail(focusedVisit.value)
        } else {
            await editVisitByTagNb(focusedVisit.value)
        }
        editDialog.value = false;
    }

    const deleteDialog = ref(false);
    const showDeleteDialog = visit => {
        focusedVisit.value = visit;
        deleteDialog.value = true;
    }
</script>