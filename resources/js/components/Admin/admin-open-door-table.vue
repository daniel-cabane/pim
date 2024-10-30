<template>
    <div>
        <div class="text-right">
            <v-btn color="primary" class="mb-3" append-icon="mdi-plus" @click="showEditDialog('creating', newSession)">
                {{ $t('Add a session') }}
            </v-btn>
            <v-dialog width="500" v-model="editDialog">
                <v-card :title="$t('Add a session')">
                    <v-card-text>
                        <div class="d-flex" style="gap:10px;">
                            <v-text-field :label="$t('Date')" variant="outlined" type="date"
                                v-model="focusedSession.date" />
                            <v-switch :label="editAction == 'creating' ? $t('Recurring') : $t('Include recurring')"
                                color="primary" v-model="focusedSession.isRecurring" />
                        </div>
                        <v-text-field :label="$t('Finish date')" variant="outlined" type="date"
                            v-model="focusedSession.finishDate" v-if="focusedSession.isRecurring" />
                        <div class="d-flex" style="gap:10px;">
                            <v-text-field :label="$t('Start')" variant="outlined" type="time"
                                v-model="focusedSession.start" />
                            <v-text-field :label="$t('Finish')" variant="outlined" type="time"
                                v-model="focusedSession.finish" />
                        </div>
                        <v-select label="Campus" :items="['BPR', 'TKO']" v-model="focusedSession.campus"
                            variant="outlined" />
                        <div class="d-flex" style="gap:10px;">
                            <v-text-field v-model="focusedSession.roomNb" :label="$t('Room')" variant="outlined"
                                clearable />
                            <v-btn icon="mdi-pi" @click='focusedSession.roomNb = "π (314 BPR)"' class="mt-1"
                                :disabled="focusedSession.campus != 'BPR'" />
                        </div>
                        <div class="d-flex" style="gap:10px;">
                            <v-text-field :label="$t('Type')" variant="outlined" v-model="focusedSession.type"
                                clearable />
                            <v-btn icon="mdi-refresh" @click='focusedSession.type = "Open doors"' class="mt-1" />
                        </div>
                        <v-select v-model="focusedSession.teacher_id" :items="teachersOptions" :label="$t('Teacher')"
                            variant="outlined" />
                    </v-card-text>
                    <div style="display:flex;justify-content:flex-end;" class="pa-3">
                        <v-btn variant="tonal" class="mr-3" color="error" :disabled="isLoading"
                            @click="addHolidayDialog = false">
                            {{ $t('Cancel') }}
                        </v-btn>
                        <v-btn color="success" :loading="isLoading" @click="handleCreate"
                            v-if="editAction == 'creating'">
                            {{ $t('Add') }}
                        </v-btn>
                        <v-btn color="primary" :loading="isLoading" @click="handleUpdate" v-else>
                            {{ $t('Update') }}
                        </v-btn>
                    </div>
                </v-card>
            </v-dialog>
            <v-dialog width="500" v-model="deleteDialog">
                <v-card :title="$t('Delete holiday')">
                    <v-card-text>
                        <div>
                            {{ t('Are you sure you want to delete this session') }} ?
                        </div>
                        <div class="d-flex mt-3 pt-2" style="gap:10px;">
                            <v-switch :label="$t('Include recurring')" color="primary"
                                v-model="deleteOptions.isRecurring" />
                            <v-text-field :label="$t('Finish date')" variant="outlined" type="date"
                                v-model="deleteOptions.finishDate" v-if="deleteOptions.isRecurring" />
                        </div>
                    </v-card-text>
                    <div style="display:flex;justify-content:flex-end;" class="pa-3">
                        <v-btn variant="tonal" class="mr-3" color="primary" :disabled="isLoading"
                            @click="deleteDialog = false">
                            {{ $t('Cancel') }}
                        </v-btn>
                        <v-btn color="error" :loading="isLoading" @click="handleDelete">
                            {{ $t('Delete') }}
                        </v-btn>
                    </div>
                </v-card>
            </v-dialog>
            <div>
                <v-data-table hover :headers="headers" :items="openDoors" item-value="name" items-per-page="25" :items-per-page-options="ipp">
                    <template v-slot:item="{ item }">
                        <tr>
                            <td class="text-center">
                                {{ $t(item.type) }}
                            </td>
                            <td class="text-center">
                                {{ new Date(item.date).toLocaleDateString(locale, {day: '2-digit', month:'short', year:
                                'numeric'}) }}
                            </td>
                            <td class="text-center">
                                {{ item.start }}
                            </td>
                            <td class="text-center">
                                {{ item.finish }}
                            </td>
                            <td class="text-center">
                                {{ item.campus }}
                            </td>
                            <td class="text-center">
                                {{ item.roomNb }}
                            </td>
                            <td class="text-center">
                                {{ item.teacher.name }}
                            </td>
                            <td class="pr-0 text-center">
                                <v-icon size="small" class="mr-3" color="primary" @click="showEditDialog('updating', item)">
                                    mdi-pencil
                                </v-icon>
                                <v-icon size="small" color="error" @click="showDeleteDialog(item)">
                                    mdi-delete
                                </v-icon>
                            </td>
                        </tr>
                    </template>
                </v-data-table>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { ref, reactive, computed } from "vue";
    import { useEventStore } from '@/stores/useEventStore';
    import { useAuthStore } from '@/stores/useAuthStore';
    import { storeToRefs } from 'pinia';
    import { useI18n } from 'vue-i18n';

    const { t, locale } = useI18n();

    const eventStore = useEventStore();
    const { getOpenDoors, createOpenDoor, updateOpenDoor, deleteOpenDoor } = eventStore;
    const { openDoors, isLoading } = storeToRefs(eventStore);
    getOpenDoors();

    const { getTeachers } = useAuthStore();
    const teachers = await getTeachers();
    const teachersOptions = computed(() => teachers.map(t => ({ title: t.name, value: t.id })));

    const newSession = {
        date: null, isRecurring: false, finishDate: null, start: null, finish: null, type: 'Open doors', teacher_id: null, roomNb: 'π (314 BPR)', campus: 'BPR'
    };

    // const addOpenDoorDialog = ref(false);
    // const handleCreateOpenDoor = async () => {
    //     await createOpenDoor(newSession);
    //     addOpenDoorDialog.value = false;
    // }

    const focusedSession = ref(null);
    const editAction = ref('updating');
    const editDialog = ref(false);
    const showEditDialog = (action, session) => {
        editAction.value = action;
        focusedSession.value = { isRecurring: false, finishDate: null, ...session };
        // focusedSession.value = action == 'updating' ? session : newSession;
        editDialog.value = true;
    }
    const handleCreate = async () => {
        await createOpenDoor(focusedSession.value);
        editDialog.value = false;
    }
    const handleUpdate = async () => {
        await updateOpenDoor(focusedSession.value);
        editDialog.value = false;
    }
    const deleteDialog = ref(false);
    const deleteOptions = reactive({id: null, isRecurring: false, finishDate: null});
    const showDeleteDialog = session => {
        deleteOptions.id = session.id;
        deleteDialog.value = true;
    }
    const handleDelete = async () => {
        await deleteOpenDoor(deleteOptions);
        deleteDialog.value = false;
    }

    const headers = ref([
        { title: t('Type'), align: 'center', key: 'type' },
        { title: t('Date'), align: 'center', key: 'date' },
        { title: t('Start'), align: 'center', key: 'start' },
        { title: t('End'), align: 'center', key: 'end' },
        { title: t('Campus'), align: 'center', key: 'campus' },
        { title: t('Room'), align: 'center', key: 'roomNb' },
        { title: t('Teacher'), align: 'center', key: 'teacher.name' },
        { title: t('Action'), align: 'center', sortable: false, width: 100 }
    ]);
    const ipp = [25, 50, 100];
</script>