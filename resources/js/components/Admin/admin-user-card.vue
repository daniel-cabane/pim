<template>
    <v-card class="pa-2" width="350">
        <v-card-title class="d-flex justify-space-between pb-0 text-truncate">
            <span>{{ user.name }} ({{ user.className }})</span>
            <v-icon icon="mdi-pencil" size="small" color="primary" @click="editDialog=true" />
        </v-card-title>
        <v-card-subtitle class="pb-3">
            {{ user.email }}
        </v-card-subtitle>
        <div class="px-4 d-flex">
            <div class="mr-4">
                <v-switch color="primary" density="compact" hide-details label="Student" v-model="student"
                    @update:modelValue="teacher = student ? false : teacher; hod = student ? false : hod" />
                <v-switch color="primary" density="compact" hide-details label="Publisher" v-model="publisher" />
            </div>
            <div>
                <v-switch color="primary" density="compact" hide-details label="Teacher" v-model="teacher"
                    @update:modelValue="student = teacher ? false : student;hod = teacher ? hod:false" />
                <v-switch color="primary" density="compact" hide-details label="HoD" v-model="hod"
                    @update:modelValue="teacher = hod ? true:teacher;student = teacher ? false : student" />
            </div>
        </div>
        <div class="px-4 d-flex justify-end">
            <v-btn :loading="loading" :disabled="!isDirty" color="primary" @click="submit">
                Submit
            </v-btn>
        </div>
        <v-dialog width="350" v-model="editDialog">
            <v-card :title="$t('Edit user')">
                <v-card-text>
                    <v-text-field variant="outlined" :label="$t('Name')" v-model="userName" />
                    <v-text-field variant="outlined" :label="$t('Class name')" v-model="className" />
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn class="mr-2" color="error" :disabled="editLoading" @click="editDialog=false">
                        {{ $t('Cancel') }}
                    </v-btn>
                    <v-btn color="primary" :loading="editLoading" @click="updateUserNameAndClass">
                        {{ $t('Submit') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-card>
</template>
<script setup>
    import { ref, computed } from 'vue';
    import useAPI from '@/composables/useAPI';

    const { post, patch } = useAPI();

    const props = defineProps({ user: Object });
    const emit = defineEmits(['userNameUpdated']);

    const userName = ref(props.user.name);
    const className = ref(props.user.className);
    const editDialog = ref(false);
    const editLoading = ref(false);
    const updateUserNameAndClass = async () => {
        editLoading.value = true;
        const res = await patch(`/api/admin/users/${props.user.id}/name`, {name: userName.value, className: className.value});
        emit('userNameUpdated', res.user);
        editLoading.value = false;
        editDialog.value = false;
    }

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