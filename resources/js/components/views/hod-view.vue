<template>
    <v-container>
        <v-tabs v-model="tab">
            <v-tab value="workshops">{{ $t('Workshops') }}</v-tab>
            <v-tab value="teachers">{{ $t('Teachers') }}</v-tab>
            <v-select :label="$t('Term')" :items="termOptions" variant="outlined" density="compact" class="mt-2 ml-5" max-width="250" v-model="term"/>
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
                                </td>
                                <td class="text-center">
                                    {{ item.stats[term-1].openDoorSessions }}
                                </td>
                                <td class="text-center">
                                    {{ item.stats[term-1].workshopSessions }}
                                </td>
                                <td class="text-center">
                                    {{ item.stats[term-1].hoursDone }}
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
    const { hodIndex } = hodStore;
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
</script>