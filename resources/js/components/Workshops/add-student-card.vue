<template>
    <v-card :title="$t('Add student')">
        <v-card-text>
            <v-text-field variant="outlined" label="Search name" :loading="isLoading" v-model="name"
                append-inner-icon="mdi-magnify" @keydown="userTyping" />
            <div>
                <v-card variant="tonal" class="pa-3 my-2" v-for="student in students">
                    <div class="d-flex justify-space-between">
                        <div>
                            <div class="text-subheading">
                                {{ student.name }}
                            </div>
                            <div class="text-caption text-captionColor">
                                {{ student.email }}
                            </div>
                        </div>
                        <v-btn icon="mdi-plus" size="small" color="primary" variant="elevated"
                            :loading="addStudentLoading == student.id"
                            :disabled="addStudentLoading != null && addStudentLoading != student.id"
                            @click="handleAddStudent(student)" />
                    </div>
                    <div class="d-flex justify-space-around">
                        <v-switch :label="$t('Available')" density="compact" hide-details v-model="student.available" />
                        <v-switch :label="$t('Confirmed')" density="compact" hide-details v-model="student.confirmed" />
                    </div>
                </v-card>
            </div>
        </v-card-text>
        <v-card-actions>
            <v-spacer />
            <v-btn color="primary" variant="tonal" :disabled="isLoading" @click="emit('closeDialog')">
                {{ $t('Close') }}
            </v-btn>
        </v-card-actions>
    </v-card>
</template>
<script setup>
    import { ref } from "vue";
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    import { storeToRefs } from 'pinia';

    const emit = defineEmits(['closeDialog']);

    const WorkshopStore = useWorkshopStore();
    const { searchStudent, addStudent } = WorkshopStore;
    const { students, isLoading } = storeToRefs(WorkshopStore);

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