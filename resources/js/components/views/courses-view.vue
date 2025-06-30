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
                <v-btn color="primary" prepend-icon="mdi-account-school" v-if="user.is.student && false">
                    {{ $t('Join Course') }}
                </v-btn>
            </span>
        </div>
        <div class="d-flex flex-wrap mt-4">
            <course-card v-for="course in courses" :course="course"/>
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
    const { createCourse, myCourses} = courseStore;
    const { courses, isLoading } = storeToRefs(courseStore);
    myCourses();

    const createDialog = ref(false);

    const title_fr = ref('');
    const title_en = ref('');

    const confirmCreate = async () => {
        await createCourse(title_fr.value, title_en.value);
        title_fr.value = '';
        title_en.value = '';
        createDialog.value = false; 
    }

</script>
