<template>
    <v-card class="pa-2" width="350">
        <v-card-title class="d-flex justify-space-between pb-0 text-truncate">
            <span>{{ user.name }}
                (
                <span v-if="user.is.teacher">{{ user.preferences.title }}</span>
                <span v-else>{{ user.class_name }}</span>
                )
            </span>
            <v-icon icon="mdi-pencil" size="small" color="primary" @click="editDialog=true" />
        </v-card-title>
        <v-card-subtitle class="pb-3">
            {{ user.email }}<br/>
            {{ user.two_factor_secret ? user.two_factor_secret : 'No tag' }}
        </v-card-subtitle>
        <div class="px-4 d-flex">
            <div class="mr-15">
                <v-switch color="primary" density="compact" hide-details label="Student" v-model="student"
                    @update:modelValue="teacher = student ? false : teacher; hod = student ? false : hod" />
                <v-switch color="primary" density="compact" hide-details label="Publisher" v-model="publisher" />
                <v-switch color="primary" density="compact" hide-details label="Instructor" v-model="instructor"/>
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
            <v-card :title="$t('Edit user')" :subtitle="user.email">
                <v-card-text>
                    <v-text-field variant="outlined" :label="$t('Name')" v-model="userName" />
                    <div v-if="user.is.teacher">
                        <v-text-field variant="outlined" :label="$t('Title')" v-model="userTitle" />
                    </div>
                    <div v-else>
                        <v-select :label="$t('Class level')" variant="outlined" :items="levels" v-model="classLevel" />
                        <v-select :label="$t('Class name')" variant="outlined" :items="['A', 'B', 'C', 'D', 'E']" v-model="className"/>
                    </div>
                    <div>
                        <v-text-field variant="outlined" :label="$t('Rfid tag')" v-model="userTag" />
                    </div>
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
    const userTitle = ref(props.user.preferences.title);
    const userTag = ref(props.user.two_factor_secret);
    const className = ref(props.user.section);
    const classLevel = ref(props.user.level);
    const editDialog = ref(false);
    const editLoading = ref(false);
    const updateUserNameAndClass = async () => {
        editLoading.value = true;
        const res = await patch(`/api/admin/users/${props.user.id}/name`, {name: userName.value, title: userTitle.value, section: className.value, level: classLevel.value, two_factor_secret: userTag.value});
        emit('userNameUpdated', res.user);
        editLoading.value = false;
        editDialog.value = false;
    }

    const student = ref(props.user.is.student);
    const publisher = ref(props.user.is.publisher);
    const teacher = ref(props.user.is.teacher);
    const hod = ref(props.user.is.hod);
    const instructor = ref(props.user.is.instructor);

    let isDirty = computed(() => {
        return teacher.value != props.user.is.teacher || hod.value != props.user.is.hod || publisher.value != props.user.is.publisher || student.value != props.user.is.student || instructor.value != props.user.is.instructor;
    });

    let loading = ref(false);
    const submit = async () => {
        loading.value = true;
        const res = await post(`/api/admin/users/${props.user.id}/updateRoles`, {teacher: teacher.value, hod: hod.value, publisher: publisher.value, student: student.value, instructor: instructor.value});
        loading.value = false;
    }

    const levels = ['6e', '5e', '4e', '3e', '2nde', '1re', 'Term', 'Y7', 'Y8', 'Y9', 'Y10', 'Y11', 'Y12'];
</script>