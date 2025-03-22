<template>
    <v-container>
        <v-tabs v-model="tab">
            <v-tab value="workshops">{{ $t('Workshops') }}</v-tab>
            <v-tab value="teachers">{{ $t('Teachers') }}</v-tab>
            <v-tab value="missions">Missions</v-tab>            
        </v-tabs>
        <div class="pa-4" v-if="isReady">
            <v-window v-model="tab">
                <v-window-item value="workshops">
                    <v-row class="px-4 mt-2">
                        <v-col cols="12" sm="6" md="5" lg="4">
                            <v-btn 
                                class="mr-2 mt-1"
                                v-for="term in terms" 
                                :text="term.title" 
                                :variant="term.display ? 'elevated' : 'tonal'" 
                                color="primary" 
                                size="large"
                                @click="term.display = !term.display"
                            />
                        </v-col>
                        <v-col cols="12" sm="6" md="7" lg="8">
                            <v-text-field prepend-inner-icon="mdi-magnify" variant="outlined" hide-details :label="$t('Search title')" v-model="search"/>
                        </v-col>
                    </v-row>
                    <div class="pa-4 d-flex flex-wrap">
                        <workshop-card v-for="workshop in displayWorkshops" :workshop="workshop" class="ma-2" :key="workshop.id" />
                    </div>
                </v-window-item>
                <v-window-item value="teachers">
                    <div class="d-flex justify-space-between mb-4">
                        <v-dialog width="400" v-model="hoursDialog">
                            <template v-slot:activator="{ props: activatorProps }">
                                <v-btn color="primary" :text="$t('Hours due per week')" class="mt-3" density="comfortable" v-bind="activatorProps"/>
                            </template>
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
                        <teacher-activity-legend/>
                    </div>
                    <div v-for="teacher in teachers">
                        <teacher-activity :teacher="teacher" showName/>
                        <v-divider class="my-4"/>
                    </div>
                </v-window-item>
                <v-window-item value="missions">
                    <missions-table/>
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

    const { user } = useAuthStore();
    
    const hodStore = useHodStore();
    const { hodIndex, updateTeachersHours } = hodStore;
    const { workshops, teachers, isLoading, isReady } = storeToRefs(hodStore);
    hodIndex();

    if(user == null || (!user.is.admin && !user.is.hod)){
        const router = useRouter();
        router.go(-1);
    }

    const tab = ref('workshops');

    const terms = ref([{title: 'T1', display: true}, {title: 'T2', display: true}, {title: 'T3', display: true}]);
    const search = ref('');
    const displayWorkshops = computed(() => {
        const displayWorkshops = [];
        workshops.value.forEach(w => {
            const termCheck = terms.value[w.term-1].display;
            let titleCheck = true;// search.value == '' || w.title.en.indexOf(search.value) > -1 || w.title.fr.indexOf(search.value) > -1;
            if(search.value != ''){
                ['fr', 'en'].forEach(lg => {
                    if(w.title[lg] && w.title[lg].toLowerCase().indexOf(search.value.toLowerCase()) < 0){
                        titleCheck = false;
                    }
                });
            }
            if(termCheck && titleCheck){
                displayWorkshops.push(w);
            }
        });
        return displayWorkshops;
    });

    const hoursDialog = ref(false);
</script>