<template>
    <v-container>
        <v-tabs v-model="tab">
            <v-tab value="workshops">{{ $t('Workshops') }}</v-tab>
            <v-tab value="surveys">{{ $t('Surveys') }}</v-tab>
            <v-tab value="posts">Blog</v-tab>
            <v-tab value="users">{{ $t('Users') }}</v-tab>
            <v-tab value="openDoors">{{ $t('Open doors') }}</v-tab>
            <v-tab value="holidays">{{ $t('Holidays') }}</v-tab>
            <v-tab value="messages">Messages</v-tab>
            <v-tab value="newYear">{{ $t('New Year') }}</v-tab>
        </v-tabs>
        <div class="pa-4">
            <v-window v-model="tab">
                <v-window-item value="workshops">
                    <admin-workshops-tabs/>
                </v-window-item>
                <v-window-item value="surveys">
                    <survey-table admin />
                </v-window-item>
                <v-window-item value="posts">
                    <admin-posts-tabs />
                </v-window-item>
                <v-window-item value="users">
                    <v-alert title="Account linked" :text="alert.text" :type="alert.type" v-if="alert" closable/>
                    <div class="d-flex mt-2 ga-3">
                        <v-text-field label="Search user" :loading="loading" variant="outlined" v-model="data" @keydown.enter="fetchUsers" />
                        <admin-assign-tagnb-dialog/>
                        <admin-add-students-dialog/>
                    </div>
                    <div class="d-flex flex-wrap">
                        <admin-visit-card v-for="visit in visits" :visit="visit" class="ma-2"/>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;">
                        <admin-user-card v-for="user in users" class="ma-2" :key="user.id" :user="user" @userNameUpdated="fetchUsers" />
                    </div>
                </v-window-item>
                <v-window-item value="openDoors">
                    <admin-open-door-table />
                </v-window-item>
                <v-window-item value="holidays">
                    <div>
                        <admin-holidays-table />
                        <admin-terms-display />
                    </div>
                </v-window-item>
                <v-window-item value="messages">
                    <admin-messages-center />
                </v-window-item>
                <v-window-item value="newYear">
                    <admin-new-year />
                </v-window-item>
            </v-window>
        </div>
    </v-container>
</template>
<script setup>
    import { ref, reactive } from 'vue';
    import { useAuthStore } from '@/stores/useAuthStore';
    import { useOpenDoorStore } from '@/stores/useOpenDoorStore';
    import { storeToRefs } from 'pinia';
    import { useRouter } from 'vue-router';

    const authStore = useAuthStore();
    const { user } = authStore;

    const openDoorStore = useOpenDoorStore();
    const { getVisitsToReview, reviewVisit } = openDoorStore;
    const { visits, alert } = storeToRefs(openDoorStore);

    getVisitsToReview()

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