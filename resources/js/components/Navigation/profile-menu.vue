<template>
    <div>
        <v-menu :close-on-content-click="false">
            <template v-slot:activator="{ props }">
                <v-btn outline icon="mdi-account" v-bind="props"/>
            </template>
            <v-list>
                <v-list-subheader>
                    {{ user.name }} <span v-if="user.class_name">({{ user.class_name }})</span>
                </v-list-subheader>
                <profile-dialog :user="user" :forceOpen="forceOpen" />
                <v-divider />
                <theme-and-language-picker />
                <v-divider />
                <message-admin-dialog/>
                <v-list-item @click="goToDashboard" v-if="user.is.admin">
                    <template v-slot:prepend>
                        <v-icon icon="mdi-security"></v-icon>
                    </template>
                    <v-list-item-title>Admin</v-list-item-title>
                </v-list-item>
                <v-list-item @click="goToHod" v-if="user.is.admin || user.is.hod">
                    <template v-slot:prepend>
                        <v-icon icon="mdi-account-supervisor-circle-outline"></v-icon>
                    </template>
                    <v-list-item-title>{{ $t('HoD') }}</v-list-item-title>
                </v-list-item>
                <v-list-item @click="logout">
                    <template v-slot:prepend>
                        <v-icon icon="mdi-logout"></v-icon>
                    </template>
                    <v-list-item-title>{{ $t("Sign out") }}</v-list-item-title>
                </v-list-item>
            </v-list>
        </v-menu>
        <v-dialog v-model="initPrefsDialog" persistent width="380" v-if="forceOpen">
            <v-card width="380" prepend-icon="mdi-account" :title="$t('Preferences')">
                <v-card-text>
                    <div class="mb-4">
                        {{ $t('Please indicate your preferences') }}
                    </div>
                    <div>
                        <v-text-field variant="outlined" :label="$t('Title')" placeholder="M., Mr, Mme, Ms..."
                            v-model="initData.title" v-if="user.is.teacher" />
                        <v-text-field variant="outlined" :label="$t('First name')" maxLength="25"
                            v-model="initData.firstName" />
                        <v-text-field variant="outlined" :label="$t('Last name')" maxLength="25"
                            v-model="initData.lastName" />
                    </div>
                    <div style="display:flex;gap:15px;" v-if="user.is.student">
                        <v-select :label="$t('Class level')" variant="outlined" :items="levels" v-model="initData.classLevel" />
                        <v-select :label="$t('Class name')" variant="outlined" :items="['A', 'B', 'C', 'D', 'E']" v-model="initData.className" />
                    </div>
                    <div class="d-flex">
                        <div style="flex:1">
                            <div class="text-caption text-captionColor">{{ $t('Language') }}</div>
                            <v-radio-group v-model="initData.language">
                                <v-radio label="Français" value="fr"></v-radio>
                                <v-radio label="English" value="en"></v-radio>
                                <v-radio :label="$t('Both')" value="both"></v-radio>
                            </v-radio-group>
                        </div>
                        <div style="flex:1">
                            <div class="text-caption text-captionColor">Campus</div>
                            <v-radio-group v-model="initData.campus">
                                <v-radio label="BPR" value="BPR"></v-radio>
                                <v-radio label="TKO" value="TKO"></v-radio>
                                <v-radio :label="$t('Both') " value="both"></v-radio>
                            </v-radio-group>
                        </div>
                    </div>
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn color="primary" style="width:150px;" variant="flat" :loading="loading" @click="updatePreferences(initData)">
                        {{ $t('Submit') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>
<script setup>
    import { ref, computed } from 'vue';
    import { useAuthStore } from '@/stores/useAuthStore';
    import { storeToRefs } from 'pinia';
    import { useRouter } from 'vue-router';

    const authStore = useAuthStore();
    const { logout, updatePreferences } = authStore;
    const { user, loading } = storeToRefs(authStore);

    const router = useRouter();
    const goToDashboard = () => {
        router.push('/admin');
    }
    const goToHod = () => {
        router.push('/hod');
    }

    const initPrefsDialog = ref(true);
    const initData = ref({init: true});
    const forceOpen = computed(() => {
        return !user.value.preferences || !user.value.preferences.language || !user.value.preferences.campus;
    });

    const levels = ['6e', '5e', '4e', '3e', '2nde', '1re', 'Term', 'Y7', 'Y8', 'Y9', 'Y10', 'Y11', 'Y12'];
</script>