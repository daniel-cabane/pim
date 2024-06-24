<template>
    <v-container>
            <v-tabs v-model="tab">
                <v-tab value="workshops">{{ $t('Workshops') }}</v-tab>
                <v-tab value="posts">{{ $t('Blog posts') }}</v-tab>
                <v-tab value="users">{{ $t('Users') }}</v-tab>
            </v-tabs>
            <div>
                <v-window v-model="tab">
                    <v-window-item class="pt-2" value="workshops">
                        <workshop-card v-for="workshop in workshops" :workshop="workshop" class="my-2" :key="workshop.id" />
                        <!-- <div v-for="workshop in workshops" :key="workshop.id">{{ workshop.id }}</div> -->
                    </v-window-item>
                    <v-window-item class="pt-2" value="posts">
                        posts
                    </v-window-item>
                    <v-window-item class="pt-2" value="users">
                        <v-text-field label="Search user" :loading="loading" variant="outlined" v-model="data"
                            @keydown.enter="fetchUsers" />
                        <div style="display:flex;flex-wrap:wrap;">
                            <admin-user-card v-for="user in users" :key="user.id" :user="user" />
                        </div>
                    </v-window-item>
                </v-window>
            </div>
    </v-container>
</template>
<script setup>
    import { ref, reactive } from 'vue';
    import { useAuthStore } from '@/stores/useAuthStore';
    import { useRouter } from 'vue-router';
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    import { storeToRefs } from 'pinia';

    // const { workshops, isReady, adminGetAllWorkshops } = useWorkshopStore();
    const workshopStore = useWorkshopStore();
    const { adminGetAllWorkshops } = workshopStore;
    const { workshops } = storeToRefs(workshopStore);
    adminGetAllWorkshops();

    const authStore = useAuthStore();
    const { user } = authStore;

    if(user == null || !user.is.admin){
        const router = useRouter();
        router.go(-1);
    }

    const tab = ref('workshops');

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