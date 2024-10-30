<template>
    <v-container>
        <v-tabs v-model="tab">
            <v-tab value="workshops">{{ $t('Workshops') }}</v-tab>
            <v-tab value="teachers">{{ $t('Teachers') }}</v-tab>
            <v-select :label="$t('Term')" :items="termOptions" variant="outlined" density="compact" class="mt-2 ml-5" max-width="250" v-model="term"/>
            <v-spacer/>
            <v-btn color="primary" :text="$t('Hours due per week')" class="mt-3" density="comfortable" @click="hoursDialog = true"/>
            <v-dialog width="400" v-model="hoursDialog">
                <v-card :title="$t('Hours due per week')">
                    <v-card-text class="d-flex flex-wrap" style="gap:0 20px;">
                        <v-text-field 
                            variant="outlined"
                            style="min-width:150px;"
                            v-for="teacher in teachers"
                            :key="teacher.id"
                            :label="teacher.name"
                            v-model="teacher.preferences.hoursDuePerWeek"
                        />
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer/>
                        <v-btn :text="$t('Close')" variant="tonal" color="error" :disabled="isLoading" @click="hoursDialog = false;"/>
                        <v-btn :text="$t('Confirm')" variant="flat" color="primary" :loading="isLoading" @click="updateTeachersHours"/>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-tabs>
        <div class="pa-4">
            <v-window v-model="tab">
                <v-window-item value="workshops">
                    <div class="pa-4 d-flex flex-wrap">
                        <workshop-card v-for="workshop in workshops[term-1]" :workshop="workshop" class="ma-2" :key="workshop.id" />
                    </div>
                </v-window-item>
                <v-window-item value="teachers">
                    <v-data-table hover :headers="headers" :items="teachers"  items-per-page="-1">
                        <template v-slot:item="{ item }">
                            <tr>
                                <td class="text-center">
                                    {{ item.name }}
                                    <span v-if="item.preferences.hoursDuePerWeek">
                                        ({{ item.preferences.hoursDuePerWeek }}h)
                                    </span>
                                </td>
                                <td class="text-center">
                                    {{ item.hoursDone[term-1].openDoorSessions }}
                                </td>
                                <td class="text-center">
                                    {{ item.hoursDone[term-1].workshopSessions }}
                                </td>
                                <td class="text-center">
                                    {{ item.hoursDone[term-1].hoursDone }}
                                    <span v-if="item.preferences.hoursDuePerWeek">
                                        / {{ item.preferences.hoursDuePerWeek*terms[term-1].nbWeeks }}
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </v-data-table>
                </v-window-item>
            </v-window>
        </div>
    </v-container>
</template>
<script setup>
    import { ref, computed } from 'vue';
    import { useAuthStore } from '@/stores/useAuthStore';
    import { useHodStore } from '@/stores/useHodStore';
    import { storeToRefs } from 'pinia';
    import { useRouter } from 'vue-router';
    import { useI18n } from 'vue-i18n';

    const { t } = useI18n();

    const { user } = useAuthStore();
    
    const hodStore = useHodStore();
    const { hodIndex, updateTeachersHours } = hodStore;
    const { workshops, teachers, terms, isLoading, isReady } = storeToRefs(hodStore);
    hodIndex();

    if(user == null || (!user.is.admin && !user.is.hod)){
        const router = useRouter();
        router.go(-1);
    }

    const tab = ref('workshops');
    const term = ref(1);

    const termOptions = computed(() => terms.value.map(currenTerm => ({title: `${t('Term')} ${currenTerm.nb} (${currenTerm.nbWeeks} ${t('weeks')})`, value: currenTerm.nb})));

    const headers = ref([
        { title: t('Name'), align: 'center', key: 'name' },
        { title: t('Open doors'), align: 'center', key: 'date' },
        { title: t('Workshop sessions'), align: 'center', key: 'start' },
        { title: t('Hours done'), align: 'center', key: 'hours' }
    ]);

    const hoursDialog = ref(false);
</script>