<template>
    <v-card :title="$t('Add student')">
        <v-card-text>
            <v-text-field 
                variant="outlined" 
                :label="$t('Name')" 
                :loading="isLoading"
                v-model="name"
                append-inner-icon="mdi-magnify"
                @keydown="userTyping" 
            />
            <div>
                <v-card variant="tonal" class="pa-3 my-2" v-for="student in students">
                    <div class="d-flex justify-space-between align-center">
                        <div>
                            <div class="text-subheading">
                                {{ student.name }}
                            </div>
                            <div class="text-caption text-captionColor">
                                {{ student.email }}
                            </div>
                        </div>
                        <v-btn 
                            append-icon="mdi-plus" 
                            density="compact"
                            color="primary"
                            variant="outlined"
                            :loading="addStudentLoading == student.id"
                            :disabled="addStudentLoading != null && addStudentLoading != student.id"
                            :text="$t('Add')"
                            @click="handleAddStudent(student)" />
                    </div>
                </v-card>
            </div>
        </v-card-text>
        <v-card-actions>
            <v-spacer />
            <v-btn color="error" variant="tonal" :disabled="isLoading" @click="emit('closeDialog')">
                {{ $t('Close') }}
            </v-btn>
        </v-card-actions>
    </v-card>
</template>
<script setup>
    import { ref } from "vue";
    import { useCourseStore } from '@/stores/useCourseStore';
    import { storeToRefs } from 'pinia';

    const emit = defineEmits(['closeDialog']);

    const CourseStore = useCourseStore();
    const { searchStudent, addStudent } = CourseStore;
    const { students, isLoading } = storeToRefs(CourseStore);

    const name = ref('');
    let timer = null;
    const userTyping = () => {
        clearTimeout(timer);
        if(name.value.length > 1){
            timer = setTimeout(() => {
                searchStudent(name.value);
            }, 500);
        }
    }

    const addStudentLoading = ref(null);
    const handleAddStudent = async student => {
        addStudentLoading.value = student.id;
        await addStudent(student);
        addStudentLoading.value = null;
    }
</script>