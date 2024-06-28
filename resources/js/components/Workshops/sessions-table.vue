<template>
    <div>
        <div class="text-h5 text-grey d-flex align-center justify-space-between">
            <span>
                <span>
                    {{ workshop.sessions.length }} {{ $t('Sessions') }}
                </span>
                <v-btn :disabled="isLoading" size="x-small" color="primary" class="ml-4" icon="mdi-plus"
                    @click="emit('createSession');" />
            </span>
            <!-- <v-btn :disabled="isLoading" size="small" color="primary" variant="tonal" @click="emit('orderSessions')">
                {{ $t('Order sessions') }}
            </v-btn> -->
            <v-btn :disabled="isLoading" size="x-small" color="success" theme="dark" class="ml-4" icon="mdi-refresh"
                @click="emit('refreshSessions')" v-if="false && workshop.status == 'confirmed'" />
            <!-- |||||||||||||||||||||||| FIX THIS |||||||||||||||||||||||| -->
        </div>
        <v-data-table :sort-by="['day']" :headers="headers" :items="workshop.sessions" items-per-page="-1"
            hide-default-footer hover>
            <template v-slot:item="{ item }">
                <tr>
                    <td class="text-center">{{ dayoftheWeek(item.date) }}</td>
                    <td class="text-center">{{ formatDate(item.date) }}</td>
                    <td class="text-center">{{ item.start }}</td>
                    <td class="text-center"
                        :class="isAfter(item.finish, item.start) ? '' : 'text-error font-weight-bold'">{{ item.finish }}
                    </td>
                    <td class="text-center">
                        <v-icon theme="dark" size="large" :color="item.status == 'confirmed' ? 'success' : 'warning'">
                            {{ item.status == 'confirmed' ? 'mdi-check' :
                            'mdi-close-circle-outline' }}
                        </v-icon>
                    </td>
                    <td class="pr-0 text-center">
                        <v-icon size="small" class="mr-3" color="primary" @click="showEditSessionDialog(item)">
                            mdi-pencil
                        </v-icon>
                        <v-icon size="small" color="error" @click="showDeleteSessionDialog(item)">
                            mdi-delete
                        </v-icon>
                    </td>
                </tr>
            </template>
            <template #bottom></template>
        </v-data-table>
        <v-dialog width="350" v-model="deleteSessionDialog">
            <v-card :title="$t('Delete session')">
                <v-card-text>
                    <div>
                        {{ $t('Are you sure you want to delete this session ?') }}
                    </div>
                    <div>
                        <b>{{ dayoftheWeek(focusedSession.date) }} {{ formatDate(focusedSession.date) }} {{
                            focusedSession.start }} → {{ focusedSession.finish }}</b>
                    </div>
                </v-card-text>
                <div style="display:flex;justify-content:flex-end;" class="pa-3">
                    <v-btn variant="tonal" class="mr-3" color="primary" @click="deleteSessionDialog = false">
                        {{ $t('Cancel') }}
                    </v-btn>
                    <v-btn color="error" @click="deleteSession">
                        {{ $t('Delete') }}
                    </v-btn>
                </div>
            </v-card>
        </v-dialog>
        <v-dialog width="550" v-model="editSessionDialog">
            <v-card :title="$t('Edit session')">
                <v-card-text>
                    <v-text-field label="Date" variant="outlined" v-model="focusedSession.date" type="date" />
                    <div class="d-flex">
                        <v-text-field class="mr-1" :label="$t('Start')" variant="outlined"
                            v-model="focusedSession.start" type="time" />
                        <v-text-field class="ml-1" :label="$t('Finish')" variant="outlined"
                            v-model="focusedSession.finish"
                            :error="isAfter(focusedSession.start, focusedSession.finish)" type="time" />
                    </div>
                    <v-select variant="outlined" label="Status" v-model="focusedSession.status" :items="statusItems" />
                </v-card-text>
                <div class="d-flex justify-end px-4 pb-4">
                    <v-btn variant="tonal" class="mr-3" color="error" @click="editSessionDialog=false">
                        {{ $t('Close') }}
                    </v-btn>
                    <v-btn color="primary" @click="editSession">
                        {{ $t('Submit') }}
                    </v-btn>
                </div>
            </v-card>
        </v-dialog>
    </div>
</template>
<script setup>
    import { ref, reactive } from "vue";
    import { useI18n } from 'vue-i18n';

const emit = defineEmits(['deleteSession', 'refreshSessions', 'createSession', 'editSession', 'orderSessions']);
    const props = defineProps({ workshop: Object, isLoading: Boolean });

    const { t } = useI18n();

    const statusItems = [
        { title: t('Confirmed'), value: 'confirmed' }, { title: t('Pending'), value: 'pending' }
    ]
    const headers = ref([
        { title: t('Day'), align: 'center', key: 'day' },
        { title: t('Date'), align: 'center', key: 'date' },
        { title: t('Start'), align: 'center', key: 'start' },
        { title: t('Finish'), align: 'center', key: 'finish' },
        { title: t('Confirmed'), align: 'center', key: 'status' },
        { title: t('Actions'), align: 'center', sortable: false, width: 100 }
    ]);
    const formatDate = d => {
        const date = new Date(d);
        return date.getDate().toString().padStart(2, '0') + '-' +
            (date.getMonth() + 1).toString().padStart(2, '0') + '-' +
            date.getFullYear().toString().slice(2);
    }
    const daysOfTheWeek = reactive([t('Sunday'), t('Monday'), t('Tuesday'), t('Wednesday'), t('Thursday'), t('Friday'), t('Saturday')]);
    const dayoftheWeek = d => {
        const date = new Date(d);
        return daysOfTheWeek[date.getDay()];
    }
    const isAfter = (time1, time2) => {
        const [hours1, minutes1] = time1.split(':').map(Number);
        const [hours2, minutes2] = time2.split(':').map(Number);

        if (hours1 > hours2) {
            return true;
        } else if (hours1 < hours2) {
            return false;
        }

        if (minutes1 > minutes2) {
            return true;
        } else {
            return false;
        }
    }

    const focusedSession = ref(null);
    const deleteSessionDialog = ref(false);
    const showDeleteSessionDialog = session => {
        focusedSession.value = session;
        deleteSessionDialog.value = true;
    }
    const deleteSession = () => {
        emit('deleteSession', focusedSession.value.id);
        deleteSessionDialog.value = false;
    }

    const editSessionDialog = ref(false);
    const showEditSessionDialog = session => {
        focusedSession.value = {...session};
        editSessionDialog.value = true;
    }
    const editSession = () => {
        emit('editSession', focusedSession.value);
        editSessionDialog.value = false;
    }
</script>