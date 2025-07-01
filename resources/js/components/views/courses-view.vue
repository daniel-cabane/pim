<template>
    <v-container style="max-width:1168px;">
        <div class="d-flex justify-space-between align-center">
            <span class="text-h5">
                {{ $t('Courses') }}
            </span>
            <span >
                <v-dialog max-width="500" v-model="createDialog" v-if="user.is.teacher">
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn color="primary" prepend-icon="mdi-plus" class="mr-2" v-bind="activatorProps">
                            {{ $t('Create Course') }}
                        </v-btn>
                    </template>

                    <template v-slot:default="{ isActive }">
                        <v-card :title="$t('Create Course')" :subtitle="$t('Indicate the course title in at least one language')">
                        <v-card-text>
                            <v-text-field v-model="title_fr" label="Titre (fr)" variant="outlined" />
                            <v-text-field v-model="title_en" label="Title (en)" variant="outlined" />
                        </v-card-text>

                        <v-card-actions>
                            <v-spacer/>
                            <v-btn variant="tonal" :disabled="isLoading" class="mr-2" min-width="150" color="error" @click="createDialog=false">{{ $t('Cancel') }}</v-btn>
                            <v-btn variant="flat" color="primary" :loading="isLoading" min-width="150" @click="confirmCreate">{{ $t('Create') }}</v-btn>
                        </v-card-actions>
                        </v-card>
                    </template>
                </v-dialog>
                <v-dialog max-width="500" v-model="joinDialog" v-if="user.is.student">
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn color="primary" prepend-icon="mdi-account-school" :text="$t('Join Course')" v-bind="activatorProps"/>
                    </template>
                    <template v-slot:default="{ isActive }">
                        <v-card :title="$t('Join Course')">
                        <v-card-text>
                            <v-text-field 
                                class="mt-6"
                                variant="outlined"
                                label="Code"
                                :counter="6"
                                v-model="joinCode"
                                style="text-transform: uppercase;"
                                @input="joinCode = joinCode.slice(0, 6).toUpperCase()"
                            />
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer/>
                            <v-btn variant="tonal" color="error" :text="$t('Close')" @click="isActive.value = false"/>
                            <v-btn 
                                variant="flat"
                                color="primary"
                                :text="$t('Confirm')"
                                @click="joinCourse(joinCode)"
                                :disabled="joinCode.length != 6"
                                :loading="isLoading"
                            />
                        </v-card-actions>
                        </v-card>
                    </template>
                </v-dialog>
            </span>
        </div>
        <div class="d-flex flex-wrap mt-4" v-if="courses.length">
            <course-card v-for="course in courses" :course="course"/>
        </div>
        <div class="text-h3 text-captionColor d-flex justify-center pa-12" v-else>
            {{ $t('No active course') }}
        </div>
    </v-container>
</template>

<script setup>
    import { ref } from 'vue';
    import { useCourseStore } from '@/stores/useCourseStore';
    import { storeToRefs } from 'pinia';
    import { useAuthStore } from '@/stores/useAuthStore';

    const authStore = useAuthStore();
    const { user } = storeToRefs(authStore);

    const courseStore = useCourseStore();
    const { createCourse, myCourses, join } = courseStore;
    const { courses, isLoading } = storeToRefs(courseStore);
    myCourses();

    const createDialog = ref(false);
    const joinDialog = ref(false);
    const joinCode = ref('');

    const title_fr = ref('');
    const title_en = ref('');

    const confirmCreate = async () => {
        await createCourse(title_fr.value, title_en.value);
        title_fr.value = '';
        title_en.value = '';
        createDialog.value = false; 
    }

    const joinCourse = async () => {
        const res = await join(joinCode.value);
        if(res){
            joinDialog.value = false;
        }
    }
</script>
