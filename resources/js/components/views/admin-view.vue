<template>
    <v-container>
        <v-tabs v-model="tab">
            <v-tab value="workshops">{{ $t('Workshops') }}</v-tab>
            <v-tab value="posts">{{ $t('Blog posts') }}</v-tab>
            <v-tab value="users">{{ $t('Users') }}</v-tab>
            <!-- <v-tab value="sessions">{{ $t('Sessions') }}</v-tab> -->
            <v-tab value="openDoors">{{ $t('Open doors') }}</v-tab>
            <v-tab value="holidays">{{ $t('Holidays') }}</v-tab>
        </v-tabs>
        <div class="pa-4">
            <v-window v-model="tab">
                <v-window-item value="workshops">
                    <div class="d-flex flex-wrap ">
                        <workshop-card v-for="workshop in workshops" :workshop="workshop" class="ma-2"
                            :key="workshop.id" />
                    </div>
                </v-window-item>
                <v-window-item value="posts">
                    posts
                </v-window-item>
                <v-window-item value="users">
                    <v-text-field label="Search user" :loading="loading" variant="outlined" v-model="data"
                        @keydown.enter="fetchUsers" />
                    <div style="display:flex;flex-wrap:wrap;">
                        <admin-user-card v-for="user in users" :key="user.id" :user="user" />
                    </div>
                </v-window-item>
                <v-window-item value="openDoors">
                    <admin-open-door-table />
                </v-window-item>
                <v-window-item value="holidays">
                    <div>
                        <admin-holidays-table />
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
    // import { useEventStore } from '@/stores/useEventStore';
    import { storeToRefs } from 'pinia';

    const workshopStore = useWorkshopStore();
    const { adminGetAllWorkshops } = workshopStore;
    const { workshops } = storeToRefs(workshopStore);
    adminGetAllWorkshops();

    // const eventStore = useEventStore();
    // const { getHolidays, createHoliday, editHoliday, deleteHoliday } = eventStore;
    // const { holidays } = storeToRefs(eventStore);

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