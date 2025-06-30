<template>
    <v-container>
        <div class="pb-4">
            <back-btn />
        </div>
        <div v-if="isReady">
            <course-display-student 
                :course="course" 
                v-if="user.is.student"
            />
            <course-display-teacher 
                :course="course" 
                @addStudent="addStudent"
                v-else
            />
        </div>
    </v-container>
</template>

<script setup>
    import { useRoute } from 'vue-router';
    import { useCourseStore } from '@/stores/useCourseStore';
    import { storeToRefs } from 'pinia';
    import { useAuthStore } from '@/stores/useAuthStore';

    const authStore = useAuthStore();
    const { user } = authStore;
    const route = useRoute();
    const courseStore = useCourseStore();
    const { getCourse, addStudent } = courseStore;
    const { course, isReady } = storeToRefs(courseStore);

    getCourse(route.params.id);
</script>
