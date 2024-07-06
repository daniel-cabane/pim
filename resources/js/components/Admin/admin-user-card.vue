<template>
    <v-card class="pa-2" width="250" :title="user.name" :subtitle="user.email">
        <div class="px-4">
            <v-switch color="primary" density="compact" hide-details label="Student" v-model="student"
                @update:modelValue="teacher = student ? false : teacher; hod = student ? false : hod" />
            <v-switch color="primary" density="compact" hide-details label="Publisher" v-model="publisher" />
            <v-switch color="primary" density="compact" hide-details label="Teacher" v-model="teacher"
                @update:modelValue="student = teacher ? false : student;hod = teacher ? hod:false" />
            <v-switch color="primary" density="compact" hide-details label="HoD" v-model="hod"
                @update:modelValue="teacher = hod ? true:teacher;student = teacher ? false : student" />
        </div>
        <div class="px-4 d-flex justify-end">
            <v-btn :loading="loading" :disabled="!isDirty" color="primary" @click="submit">
                Submit
            </v-btn>
        </div>
    </v-card>
</template>
<script setup>
    import { ref, computed } from 'vue';
    import useAPI from '@/composables/useAPI';

    const { post } = useAPI();

    const props = defineProps({ user: Object });

    const student = ref(props.user.is.student);
    const publisher = ref(props.user.is.publisher);
    const teacher = ref(props.user.is.teacher);
    const hod = ref(props.user.is.hod);

    let isDirty = computed(() => {
        return teacher.value != props.user.is.teacher || hod.value != props.user.is.hod || publisher.value != props.user.is.publisher || student.value != props.user.is.student;
    });

    let loading = ref(false);
    const submit = async () => {
        loading.value = true;
        const res = await post(`/api/admin/users/${props.user.id}/updateRoles`, {teacher: teacher.value, hod: hod.value, publisher: publisher.value, student: student.value});
        loading.value = false;
    }
</script>