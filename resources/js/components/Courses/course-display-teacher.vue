<template>
    <v-container>
        <v-row>
            <v-col cols="12" class="text-h4 d-flex justify-space-between align-center">
                <span>
                    {{ pickLg(course.title)}}
                </span>
                <v-dialog max-width="90%" width="450" v-model="manageStudentDialog">
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn variant="tonal" color="primary" :text="$t('Student management')" v-bind="activatorProps"/>
                    </template>

                    <template v-slot:default="{ isActive }">
                        <v-card :title="$t('Student management')">
                            <v-tabs v-model="manageStudentTab" align-tabs="center" density="compact">
                                <v-tab value="code">Code</v-tab>
                                <v-tab value="search">{{ $t('Search') }}</v-tab>
                                <v-tab value="remove">{{ $t('Remove') }}</v-tab>
                            </v-tabs>
                            <v-tabs-window v-model="manageStudentTab">
                                <v-tabs-window-item value="code">
                                    <div class="text-h1 d-flex justify-center py-12">
                                        {{ course.joinCode }}
                                    </div>
                                </v-tabs-window-item>
                                <v-tabs-window-item value="search">
                                    <search-student-card/>
                                </v-tabs-window-item>
                                <v-tabs-window-item value="remove">
                                    <div class="pa-2">
                                        <v-table>
                                            <tbody>
                                                <tr v-for="student in course.students">
                                                    <td>{{ student.name }}</td>
                                                    <td class="d-flex align-center ga-2 pa-8">
                                                        <span
                                                            :class="!removeList.includes(student.id) ? 'text-primary font-weight-bold' : 'text-captionColor'"
                                                        >
                                                            Keep
                                                        </span>
                                                        <v-switch hide-details color="error" :value="student.id" v-model="removeList"/>
                                                        <span 
                                                            :class="removeList.includes(student.id) ? 'text-error font-weight-bold' : 'text-captionColor'"
                                                        >
                                                            Remove
                                                        </span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </v-table>
                                    </div>
                                </v-tabs-window-item>
                                <v-tabs-window-item value="confirm">
                                    <div class="px-4 py-3">
                                        Are you sure you want to remove these students ?
                                    </div>
                                    <div class="px-3 mb-3">
                                        <v-chip
                                            v-for="student in studentsToRemove"
                                            color="error"
                                            class="mr-2"
                                            :text="student.name"
                                        />
                                    </div>
                                </v-tabs-window-item>
                            </v-tabs-window>
                            <div class="d-flex pa-4">
                                <v-btn color="primary" variant="tonal" size="small" icon="mdi-chevron-left" @click="manageStudentTab = 'remove'" v-if="manageStudentTab == 'confirm'"/>
                                <v-spacer/>
                                <v-btn 
                                    color="primary" 
                                    variant="tonal" 
                                    :disabled="isLoading"
                                    :text="$t('Close')"
                                    @click="isActive.value = false"
                                />
                                <v-btn
                                    color="error"
                                    variant="tonal"
                                    :disabled="removeList.length == 0"
                                    :text="$t('Remove')"
                                    @click="proceedRemove"
                                    class="ml-2"
                                    v-if="manageStudentTab == 'remove'"
                                />
                                <v-btn
                                    color="error"
                                    variant="flat"
                                    :loading="isLoading"
                                    :text="$t('Confirm')"
                                    @click="confirmRemove"
                                    class="ml-2"
                                    v-if="manageStudentTab == 'confirm'"
                                />
                            </div>
                        </v-card>
                    </template>
                </v-dialog>
            </v-col>
            <v-col cols="12" class="text-captionColor">
                {{ pickLg(course.description)}}
            </v-col>
        </v-row>
        <div>
            <v-tabs v-model="tab" color="primary">
                <v-tab 
                    v-for="(section, index) in course.sections"
                    class="font-weight-bold"
                    prepend-icon="mdi-star-four-points-box" 
                    :text="pickLg(section.title)"
                    :value="index"
                />
                <v-tab text="Bonus" class="font-weight-bold" value="bonus"/>
            </v-tabs>
            <v-data-table :headers="headers" :items="students" :search="search" v-if="tab != 'bonus'">
                <template v-slot:top>
                    <v-toolbar class="pa-2" flat>
                        <v-text-field class="mt-6" :label="$t('Search name')" variant="outlined" v-model="search"/>
                        <v-spacer/>
                        <!-- KEEP THIS DIALOG !!! -->
                         <!-- KEEP THIS DIALOG !!! -->
                          <!-- KEEP THIS DIALOG !!! -->
                        <!-- <v-dialog max-width="400" v-model="scoreByObjectiveDialog">
                            <template v-slot:activator="{ props: activatorProps }">
                                <v-btn color="primary" variant="outlined" size="large" :text="$t('By objective')" v-bind="activatorProps"/>
                            </template>
                            <template v-slot:default="{ isActive }">
                                <v-card :title="$t('Score by objective')">
                                <v-card-text>
                                    <v-select :items="objectiveOptions" v-model="selectedObjective" variant="outlined"/>
                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                                </v-card-text>
                                <v-card-actions>
                                    <v-spacer/>
                                    <v-btn :text="$t('Close')" color="error" @click="isActive.value = false"/>
                                    <v-btn variant="flat" color="primary" :loading="isLoading" :text="$t('Submit')" @click="submitScoreByObjectiveChange"/>
                                </v-card-actions>
                                </v-card>
                            </template>
                        </v-dialog> -->
                    </v-toolbar>
                </template>
                <template v-slot:item.actions="{ item }">
                    <v-btn size="small" variant="flat" icon="mdi-eye" @click="seeStudent(item)"/>
                </template>
            </v-data-table>
            <v-card title="Bonuses will come here" color="surface" flat v-else/>
        </div>
        <v-dialog v-model="seeStudentDialog" width="400">
            <v-card :title="$t('Student progress')">
                <v-card-text>
                    <v-text-field
                        v-for="(objective, index) in course.sections[tab].objectives" 
                        variant="outlined"
                        type="number"
                        min="0"
                        step="1"
                        :label="`${pickLg(objective.title)} (${objective.points}pts)`"
                        v-model="focusedStudent.objectives[index]"
                    />
                    <v-card-actions>
                        <v-spacer/>
                        <v-btn variant="tonal" color="error" :disabled="isLoading" :text="$t('Cancel')" @click="seeStudentDialog = false"/>
                        <v-btn variant="flat" color="primary" :loading="isLoading" :text="$t('Submit')" @click="submitScoreChange"/>
                    </v-card-actions>
                </v-card-text>
            </v-card>
        </v-dialog>
    </v-container>
</template>
<script setup>
    import { ref, computed } from "vue";
    import { useI18n } from 'vue-i18n';
    import { useCourseStore } from '@/stores/useCourseStore';
    import { storeToRefs } from 'pinia';

    const { locale, t } = useI18n();

    const courseStore = useCourseStore();
    const { updateScores, removeStudent } = courseStore;
    const { isLoading } = storeToRefs(courseStore);

    const props = defineProps({ course: Object });
    const emits = defineEmits(['addStudent']);

    const tab = ref(0);
    const manageStudentTab = ref('code');
    const manageStudentDialog = ref(false);
    const pickLg = o => {
        if(o){
            if(locale.value == 'en'){
                return o.en && o.en.length ? o.en : o.fr
            }
            return o.fr && o.fr.length ? o.fr : o.en
        }
        return '';
    }

    const headers = computed(() => {
        const headers = [
            { title: t('Name'), key: 'name', align: 'start' },
            { title: t('Class'), key: 'className' },
        ];
        if(props.course.sections[tab.value] && props.course.sections[tab.value].objectives){
            props.course.sections[tab.value].objectives.forEach((o, index) => {
                headers.push({
                     title: o.title[locale.value], key: `objectives[${index}]`, align: 'center'
                });
            });
        }
        return [...headers, { title: 'Actions', key: 'actions', sortable: false }];
    });
    const students = computed(() => {
        const students = [];
        props.course.students.forEach(s => {
            const objectives = [];
            if(props.course.sections[tab.value]){
                props.course.sections[tab.value].objectives.forEach(o => {
                    const record = s.objectives.find(obj => obj.id == o.id);
                    objectives.push(record ? record.score : 0);
                });
                students.push({id: s.id, name: s.name, className: s.className, objectives});
            }
        });

        return students;
    })
    const search = ref('');

    const seeStudentDialog = ref(false);
    const scoreByObjectiveDialog = ref(false);
    const focusedStudent = ref(null);
    const seeStudent = student => {
        focusedStudent.value = student;
        seeStudentDialog.value = true;
    }
    const submitScoreChange = async () => {
        const objNewValues = [];
        props.course.sections[tab.value].objectives.forEach((objective, index) => {
            objNewValues.push({id: objective.id, score: focusedStudent.value.objectives[index]});
        });
        await updateScores({studentId: focusedStudent.value.id, newValues: objNewValues});
        seeStudentDialog.value = false;
    }

    const removeList = ref([]);
    const studentsToRemove = computed(() => {
        return props.course.students.filter(s => removeList.value.includes(s.id));
    })
    const proceedRemove = () => manageStudentTab.value = 'confirm';
    const confirmRemove = async () => {
        await removeStudent(removeList.value);
        manageStudentDialog.value = false;
    }

    //////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////
    //////////////////////      KEEP THIS      //////////////////////
    //////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////
    // const selectedObjective = ref(null);
    // const objectiveOptions = computed(() => {
    //     const objectives = [];
    //     props.course.sections.forEach(s => {
    //         s.objectives.forEach(o => {
    //             objectives.push({
    //                 title: o.title[locale.value], value: o.id
    //             })
    //         });
    //     });
        
    //     return objectives;
    // });

    // const submitScoreByObjectiveChange = () => {
    //     console.log('ok');
    // }

</script>