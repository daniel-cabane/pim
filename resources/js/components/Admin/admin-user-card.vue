<template>
    <v-card class="pa-2" width="350" :title="user.name" :subtitle="user.email">
        <div class="px-4 d-flex">
            <v-switch color="primary" label="Teacher" v-model="teacher"/>
            <v-switch color="primary" label="HoD" v-model="hod"/>
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

    const teacher = ref(props.user.is.teacher);
    const hod = ref(props.user.is.hod);

    let isDirty = computed(() => {
        return teacher.value != props.user.is.teacher || hod.value != props.user.is.hod;
    });

    let loading = ref(false);
    const submit = async () => {
        loading.value = true;
        const res = await post(`/api/admin/users/${props.user.id}/updateRoles`, {teacher: teacher.value, hod: hod.value});
        console.log(res.data);
        addNotification({text: 'User updated', type: 'success'});
        loading.value = false;
    }
</script>