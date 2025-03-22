<template>
    <div>
        <v-data-table :headers="headers" :items="missions" :items-per-page-options="ippo" :items-per-page="25">
            <template v-slot:top>
            <v-toolbar flat>
              <v-toolbar-title>
                Missions
              </v-toolbar-title>
              <v-spacer/>
              <v-dialog width="450" v-model="dialog">
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn color="primary" :text="$t('Add mission')" variant="flat" prepend-icon="mdi-plus" class="mr-3" v-bind="activatorProps" @click="showDialog"/>
                    </template>
                    <v-card :title="$t('Add mission')">
                        <v-card-text>
                            <v-text-field variant="outlined" :label="$t('Title')" v-model="focusedMission.title"/>
                            <v-select v-model="focusedMission.teacherId" :items="teachersOptions" :label="$t('Teacher')" variant="outlined" />
                            <div class="d-flex ga-3">
                                <v-text-field  variant="outlined" type="number" min="0" max="100" :label="$t('Teaching hours')" v-model="focusedMission.lessonHours"/>
                                <v-text-field  variant="outlined" type="number" min="0" max="100" :label="$t('Prep hours')" v-model="focusedMission.prepHours"/>
                            </div>
                            <v-textarea variant="outlined" :label="$t('Comment')" v-model="focusedMission.comment"/>
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer/>
                            <v-btn :text="$t('Close')" variant="tonal" color="error" :disabled="isLoading" @click="dialog = false;"/>
                            <v-btn :text="$t('Confirm')" variant="flat" color="primary" :loading="isLoading" @click="handleConfirm"/>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
            </v-toolbar>
          </template>
          <template v-slot:item="{ item }">
                <tr>
                    <td>{{ item.teacher.name }}</td>
                    <td>{{ item.title }}</td>
                    <td>{{ item.approver.name }}</td>
                    <td class="text-center">{{ formatDate(item.date) }}</td>
                    <td class="text-center">{{ item.lessonHours }}</td>
                    <td class="text-center">{{ item.prepHours }}</td>
                    <td class="pr-0 text-center">
                        <v-icon size="small" class="mr-3" color="primary" @click="showEditDialog(item)">
                            mdi-pencil
                        </v-icon>
                        <v-icon size="small" color="error" @click="showDeleteDialog(item)">
                            mdi-delete
                        </v-icon>
                    </td>
                </tr>
            </template>
        </v-data-table>
        <v-dialog width="500" v-model="deleteDialog">
            <v-card :title="`${$t('Delete')} ${focusedMission.title}`" :subtitle="focusedMission.teacher.name">
                <v-card-text>
                    <div>
                        {{ t('Are you sure you want to delete this mission') }} ?
                    </div>
                    <v-checkbox :label="$t('Yes I want to delete it')" color="primary" v-model="confirmDelete" />
                </v-card-text>
                <div style="display:flex;justify-content:flex-end;" class="pa-3">
                    <v-btn variant="tonal" class="mr-3" color="primary" :disabled="isLoading" @click="deleteDialog = false">
                        {{ $t('Cancel') }}
                    </v-btn>
                    <v-btn color="error" :disabled="!confirmDelete" :loading="isLoading" @click="handleDelete">
                        {{ $t('Delete') }}
                    </v-btn>
                </div>
            </v-card>
        </v-dialog>
    </div>
</template>
<script setup>
    import { ref, computed } from 'vue';
    import { useHodStore } from '@/stores/useHodStore';
    import { storeToRefs } from 'pinia';
    import { useI18n } from 'vue-i18n';

    const { t, locale } = useI18n();

    const hodStore = useHodStore();
    const { addMission, getMissions, deleteMission, updateMission } = hodStore;
    const { missions, teachers, isLoading } = storeToRefs(hodStore);

    getMissions();

    const formatDate = dateString => {
        const date = new Date(dateString);
        const formattedDate = date.toLocaleDateString(locale.value, { day: '2-digit', month: '2-digit', year: '2-digit' });

        return formattedDate.charAt(0).toUpperCase() + formattedDate.slice(1);
    }

    const teachersOptions = computed(() => {
        return teachers.value.map(t => ({title: t.name, value: t.id}));
    });

    const headers = [
        { title: t('Teacher'), value: 'teacher' },
        { title: t('Title'), value: 'title' },
        { title: t('Approved by'), value: 'approver.id' },
        { title: t('Approved on'), value: 'created_at', align: 'center' },
        { title: t('Hours'), align: 'center', children: [{ title: t('Teaching'), value: 'lessonHours', align: 'center' }, { title: `${t('Preparation')} (×0.5)`, value: 'prepHours', align: 'center' }]},
        { title: '' }
    ];
    const ippo = [{value: 25, title: '25'}, {value: 50, title: '50'}, {value: -1, title: '$vuetify.dataFooter.itemsPerPageAll'}];

    const defaultMission = () => ({title: '', teacherId: null, lessonHours: null, prepHours: null, comment: ''});
    const focusedMission = ref({});

    const dialog = ref(false);

    const showDialog = () => {
        focusedMission.value = defaultMission();
    }

    const handleConfirm = async () => {
        if(editing.value){
            editing.value = false;
            const res = await updateMission(focusedMission.value);
            if(res){
                dialog.value = false;
            }
        } else {
            const res = await addMission(focusedMission.value);
            if(res){
                dialog.value = false;
            }
        }
    }

    const editing = ref(false);
    const showEditDialog = mission => {
        editing.value = true;
        focusedMission.value = mission;
        dialog.value = true;
    }

    const deleteDialog = ref(false);
    const confirmDelete = ref(false);
    const showDeleteDialog = mission => {
        confirmDelete.value = false;
        focusedMission.value = mission;
        deleteDialog.value = true;
    }
    const handleDelete = async () => {
        await deleteMission(focusedMission.value.id);
        deleteDialog.value = false;
    }
</script>