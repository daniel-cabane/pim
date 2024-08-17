<template>
    <div class="text-right pb-3">
        <div>
            <v-dialog width="500" v-model="addHolidayDialog">
                <template v-slot:activator="{ props: activatorProps }">
                    <v-btn color="primary" class="mb-3" v-bind="activatorProps" append-icon="mdi-plus">
                        {{ $t('Add a holiday') }}
                    </v-btn>
                </template>
                <template v-slot:default="{ isActive }">
                    <v-card :title="$t('Add a holiday')">
                        <v-card-text>
                            <v-text-field :label="$t('Start date')" variant="outlined" type="date" v-model="start"
                                clearable />
                            <v-text-field :label="$t('Finish date')" variant="outlined" type="date" v-model="finish"
                                clearable />
                            <v-text-field :label="$t('Name')" :rules="nameRules" variant="outlined" v-model="name"
                                max="50" clearable />
                        </v-card-text>
                        <div style="display:flex;justify-content:flex-end;" class="pa-3">
                            <v-btn variant="tonal" class="mr-3" color="error" :disabled="isLoading"
                                @click="addHolidayDialog=false">
                                {{ $t('Cancel') }}
                            </v-btn>
                            <v-btn color="success" :loading="isLoading" @click="handleAddHoliday">
                                {{ $t('Add') }}
                            </v-btn>
                        </div>
                    </v-card>
                </template>
            </v-dialog>
            <v-dialog width="500" v-model="editHolidayDialog">
                <v-card :title="$t('Edit holiday')">
                    <v-card-text>
                        <v-text-field :label="$t('Start date')" variant="outlined" type="date"
                            v-model="focusedHoliday.start" clearable />
                        <v-text-field :label="$t('Finish date')" variant="outlined" type="date"
                            v-model="focusedHoliday.finish" clearable />
                        <v-text-field :label="$t('Name')" :rules="nameRules" variant="outlined"
                            v-model="focusedHoliday.name" max="50" clearable />
                    </v-card-text>
                    <div style="display:flex;justify-content:flex-end;" class="pa-3">
                        <v-btn variant="tonal" class="mr-3" color="error" :disabled="isLoading"
                            @click="editHolidayDialog = false">
                            {{ $t('Cancel') }}
                        </v-btn>
                        <v-btn color="primary" :loading="isLoading" @click="handleEditHoliday">
                            {{ $t('Submit') }}
                        </v-btn>
                    </div>
                </v-card>
            </v-dialog>
            <v-dialog width="500" v-model="deleteHolidayDialog">
                <v-card :title="$t('Delete holiday')">
                    <v-card-text>
                        {{ t('Are you sure you want to delete') }} <b>{{ focusedHoliday.name }}</b> ?
                    </v-card-text>
                    <div style="display:flex;justify-content:flex-end;" class="pa-3">
                        <v-btn variant="tonal" class="mr-3" color="primary" :disabled="isLoading"
                            @click="deleteHolidayDialog = false">
                            {{ $t('Cancel') }}
                        </v-btn>
                        <v-btn color="error" :loading="isLoading" @click="handleDeleteHoliday">
                            {{ $t('Delete') }}
                        </v-btn>
                    </div>
                </v-card>
            </v-dialog>
        </div>
        <v-data-table hover :headers="headers" :items="holidays" item-value="name" items-per-page="25"
            :items-per-page-options="ipp">
            <template v-slot:item="{ item }">
                <tr>
                    <td class="text-center">{{ new Date(item.start).toLocaleDateString(locale, { day: '2-digit', month:
                        'short', year: 'numeric'
                        }) }}</td>
                    <td class="text-center">{{ new Date(item.finish).toLocaleDateString(locale, {
                        day: '2-digit', month:
                        'short', year: 'numeric' }) }}</td>
                    <td class="text-center">{{ $t(item.name) }}</td>
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
    </div>
</template>
<script setup>
    import { ref } from "vue";
    import { useEventStore } from '@/stores/useEventStore';
    import { storeToRefs } from 'pinia';
    import { useI18n } from 'vue-i18n';

    const { locale, t } = useI18n();

    const eventStore = useEventStore();
    const { getHolidays, createHoliday, editHoliday, deleteHoliday } = eventStore;
    const { holidays, isLoading } = storeToRefs(eventStore);
    getHolidays();

    // const props = defineProps({ holidays: Array });

    const start = ref(null);
    const finish = ref(null);
    const name = ref('');
    const nameRules = [value => {
        if (value?.length >= 3) return true
        return 'The name must be at least 3 characters.'
    }];

    const headers = ref([
        { title: t('Start'), align:'center', key: 'start' },
        { title: t('End'), align: 'center', key: 'end' },
        { title: t('Name'), align: 'center', key: 'name' },
        { title: t('Action'), align: 'center', sortable: false, width: 100 }
    ]);
    const ipp = [25, 50, 100];

    const addHolidayDialog = ref(false);
    const handleAddHoliday = async () => {
        const newHoliday = await createHoliday({ start: start.value, finish: finish.value, name: name.value });
        if(newHoliday){
            addHolidayDialog.value = false;
            start.value = null;
            finish.value = null;
            name.value = '';
        }
    }

    const focusedHoliday = ref(null);
    const editHolidayDialog = ref(false);
    const showEditDialog = holiday => {
        focusedHoliday.value = holiday;
        editHolidayDialog.value = true;
    }
    const handleEditHoliday = async () => {
        const updatedHoliday = await editHoliday(focusedHoliday.value);
        if(updatedHoliday){
            editHolidayDialog.value = false;
        }
    }

    const deleteHolidayDialog = ref(false);
    const showDeleteDialog = holiday => {
        focusedHoliday.value = holiday;
        deleteHolidayDialog.value = true;
    }
    const handleDeleteHoliday = async () => {
        const deletedHoliday = await deleteHoliday(focusedHoliday.value);
        if (deletedHoliday) {
            deleteHolidayDialog.value = false;
        }
    }
</script>