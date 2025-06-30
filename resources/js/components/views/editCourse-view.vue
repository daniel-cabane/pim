<template>
    <v-container v-if="isReady">
        <v-card>
            <v-card-text>
                <v-layout>
                    <v-row>
                        <v-col cols="12" class="text-h5 text-captionColor">
                            {{ $t('Title and description') }}
                        </v-col>
                        <v-col cols="12" sm="6">
                            <v-text-field variant="outlined" label="Titre (fr)" v-model="course.title.fr"/>
                            <v-textarea variant="outlined" label="Description (fr)" v-model="course.description.fr"/>
                        </v-col>
                        <v-col cols="12" sm="6">
                            <v-text-field variant="outlined" label="Title (en)" v-model="course.title.en"/>
                            <v-textarea variant="outlined" label="Description (en)" v-model="course.description.en"/>
                        </v-col>
                    </v-row>
                </v-layout>
                <v-divider class="my-6"/>
                <v-layout>
                    <v-row>
                        <v-col cols="12" class="text-h5 text-captionColor">
                            {{ $t('Rewards') }}
                        </v-col>
                        <v-col cols="12" md="6">
                            <div class="text-captionColor mb-2">
                                {{ $t('Objectives') }}
                            </div>
                            <div class="text-h5 mb-6 d-flex align-center">
                                <v-icon icon="mdi-check-decagram" color="primary" class="mr-1"/>
                                {{ course.rewards.objectives.blue }}%
                                <v-slider hide-details v-model="course.rewards.objectives.blue" min="0" max="100" step="1"/>
                            </div>
                            <div class="text-h5 mb-6 d-flex align-center">
                                <v-icon icon="mdi-check-decagram" color="success" class="mr-1"/>
                                {{ course.rewards.objectives.green }}%
                                <v-slider hide-details v-model="course.rewards.objectives.green" min="0" max="100" step="1"/>
                            </div>
                        </v-col>
                        <v-col cols="12" md="6">
                            <div class="text-captionColor mb-2">
                                {{ $t('Levels') }}
                            </div>
                            <div v-for="level in course.rewards.levels" class="d-flex ga-2">
                                <v-text-field style="flex:2" variant="outlined" label="Nom (fr)" v-model="level.name.fr"/>
                                <v-text-field style="flex:2" variant="outlined" label="Name (en)" v-model="level.name.en"/>
                                <v-text-field style="flex:1" variant="outlined" label="Score" type="number" min="0" step="1" v-model="level.points"/>
                            </div>
                            <div class="d-flex justify-center">
                                <v-btn color="primary" density="compact" prepend-icon="mdi-plus" @click="addLevel">
                                    {{ $t('Add level') }}
                                </v-btn>
                            </div>
                        </v-col>
                    </v-row>
                </v-layout>
                <v-divider class="my-6"/>
                <div class="d-flex justify-space-between align-center">
                    <div class="text-h5 text-captionColor">
                        Sections
                    </div>
                    <v-btn color="primary" append-icon="mdi-star-four-points-box" @click="addSection">
                        {{ $t('Add section') }}
                    </v-btn>
                </div>
                <div v-for="(section, index) in course.sections" class="my-4">
                    <v-divider class="my-2 " color="captionColor">
                        <span style="white-space: nowrap;" class="text-caption text-captionColor">
                            Section {{ index+1 }}
                        </span>
                    </v-divider>
                    <section-edit-form 
                        :section="section"
                        @editSectionRessource="editRessource"
                        @addObjective="AddObjectiveToSection(section)"
                        @moveObjectiveUp="moveObjectiveUpInSection"
                    />
                </div>
                <div class="d-flex mt-4">
                    <v-btn variant="tonal" color="error" :disabled="isLoading" @click="goBack">
                                {{ $t('Quit') }}
                            </v-btn>
                    <v-spacer />
                    <v-btn color="success" :loading="isLoading" @click="updateCourse">
                        {{ $t('Save') }}
                    </v-btn>
                </div>
            </v-card-text>
        </v-card>
        <v-dialog v-model="editDialog" width="800">
            <v-card :title="$t('Edit title and description')">
                <v-card-text class="pb-0">
                    <v-layout class="pt-2">
                        <v-row>
                            <v-col cols="12" sm="6">
                                <v-text-field variant="outlined" label="Titre (fr)" v-model="titleFr"/>
                                <v-textarea variant="outlined" label="Description (fr)" v-model="descriptionFr"/>
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-text-field variant="outlined" label="Title (en)" v-model="titleEn"/>
                                <v-textarea variant="outlined" label="Description (en)" v-model="descriptionEn"/>
                            </v-col>
                            <v-col cols="12" class="pt-0" v-if="ressourcePoints">
                                <v-text-field variant="outlined" type="number" min="0" max="100" step="1" :label="$t('Value')" v-model="ressourcePoints"/>
                            </v-col>
                        </v-row>
                    </v-layout>
                </v-card-text>
                <div class="d-flex pa-4">
                    <v-spacer />
                    <v-btn variant="tonal" color="error" class="mr-2" @click="cancelEditDialog">
                        {{ $t('Cancel') }}
                    </v-btn>
                    <v-btn color="primary" @click="confirmEdit">
                        {{ $t('Confirm') }}
                    </v-btn>
                </div>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup>
    import { ref } from 'vue';
    import { useRoute, useRouter } from 'vue-router';
    import { useCourseStore } from '@/stores/useCourseStore';
    import { storeToRefs } from 'pinia';
    // import { useI18n } from 'vue-i18n';

    // const { locale } = useI18n();

    const route = useRoute();
    const router = useRouter();
    const courseStore = useCourseStore();
    const { getCourse, addSection, updateCourse } = courseStore;
    const { course, isLoading, isReady } = storeToRefs(courseStore)

    await getCourse(route.params.id);

    const quitDialog = ref(false);
    const goBack = () => router.go(-1);

    const titleFr = ref(null);
    const titleEn = ref(null);
    const descriptionFr = ref(null);
    const descriptionEn = ref(null);
    const ressourcePoints = ref(null);

    const focusedRessource = ref(null);
    const editRessource = ressource => {
        focusedRessource.value = ressource;
        titleFr.value = ressource.title.fr;
        titleEn.value = ressource.title.en;
        descriptionFr.value = ressource.description.fr;
        descriptionEn.value = ressource.description.en;
        ressourcePoints.value = focusedRessource.value.points;
        editDialog.value = true;
    };
    const editDialog = ref(false);
    const cancelEditDialog = () => {
        editDialog.value = false;
    };
    const confirmEdit = () => {
        focusedRessource.value.title.fr = titleFr.value;
        focusedRessource.value.title.en = titleEn.value;
        focusedRessource.value.description.fr = descriptionFr.value;
        focusedRessource.value.description.en = descriptionEn.value;
        if(ressourcePoints.value){
            focusedRessource.value.points = ressourcePoints.value;
        }
        editDialog.value = false;
    }
    const AddObjectiveToSection = section => {
        let maxId = 0;
        course.value.sections.forEach(section => {
            section.objectives.forEach(objective => {
                maxId = Math.max(maxId, objective.id);
            })
        });
        section.objectives.push({
            id: maxId + 1,
            title: {fr: 'Nouvel objectif', en: 'New objective'},
            description: {fr: 'Nouvelle description', en: 'New description'},
            points: 10,
            new: true
        });
    }
    const moveObjectiveUpInSection = data => {
        const index = data.section.objectives.findIndex(item => item.id == data.objective.id);
        if (index > 0) {
            const [item] = data.section.objectives.splice(index, 1);
            data.section.objectives.splice(index - 1, 0, item);
        }
    }

    const addLevel = () => {
    if (!course.value.rewards.levels) {
        course.value.rewards.levels = [];
    }
    const maxPoints = course.value.rewards.levels.length
        ? Math.max(...course.value.rewards.levels.map(lvl => Number(lvl.points) || 0))
        : 0;
    course.value.rewards.levels.push({
        name: { fr: 'Nouveau niveau', en: 'New level' },
        points: maxPoints * 2
    });
}
</script>
