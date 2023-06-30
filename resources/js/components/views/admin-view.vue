<template>
    <v-container>
        <v-text-field label="Search user" :loading="loading" variant="outlined" v-model="data" @keydown.enter="fetchUsers"/>
        <div style="display:flex;flex-wrap:wrap;">
            <admin-user-card v-for="user in users" :key="user.id" :user="user"/>
        </div>
    </v-container>
</template>
<script setup>
    import { ref, reactive } from 'vue';
    import { useAuthStore } from '@/stores/useAuthStore';
    import { useRouter } from 'vue-router';

    const authStore = useAuthStore();
    const { user } = authStore;

    if(user == null || !user.is.admin){
        const router = useRouter();
        router.go(-1);
    }

    let data = ref('');
    let loading = ref(false);
    let users = reactive([]);

    const fetchUsers = async () => {
        loading.value = true;
        const res = await axios.get(`/api/admin/fetchUsers/${data.value}`);
        users = res.data;
        loading.value = false;
    }
</script>