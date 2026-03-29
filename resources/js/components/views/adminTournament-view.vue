<template>
    <v-container class="pb-8">
        <div v-if="tournament.id && isOrganizer" class="mt-6">
            <v-row class="mb-6">
                <v-col cols="12">
                    <v-btn :to="`/tournaments/${tournament.slug}`" variant="tonal" color="primary" prepend-icon="mdi-arrow-left">
                        Back to Tournament
                    </v-btn>
                </v-col>
            </v-row>

            <h1 class="text-h3 font-weight-bold mb-2">Manage Tournament: {{ tournament.name }}</h1>

            <!-- Admin Actions Tabs -->
            <v-tabs v-model="activeTab" class="mt-6">
                <v-tab value="overview">Overview</v-tab>
                <v-tab value="rounds">Rounds</v-tab>
                <v-tab value="players">Players</v-tab>
                <v-tab value="settings">Settings</v-tab>
            </v-tabs>

            <v-window v-model="activeTab" class="mt-4">
                <!-- Overview Tab -->
                <v-window-item value="overview">
                    <AdminOverviewTab :tournament="tournament" />
                </v-window-item>

                <!-- Rounds Tab -->
                <v-window-item value="rounds">
                    <AdminRoundsTab :tournament="tournament" />
                </v-window-item>

                <!-- Players Tab -->
                <v-window-item value="players">
                    <AdminPlayersTab :tournament="tournament" />
                </v-window-item>

                <!-- Settings Tab -->
                <v-window-item value="settings">
                    <AdminSettingsTab ref="settingsTabRef" :tournament="tournament" :slug="tournament.slug" />
                </v-window-item>
            </v-window>
        </div>

        <!-- Access Denied -->
        <div v-else class="text-center py-16">
            <v-icon size="64" color="error" class="mb-4">mdi-lock</v-icon>
            <p class="text-h6">{{ tournament.id ? 'You do not have permission to manage this tournament' : 'Tournament not found' }}</p>
        </div>
    </v-container>

    <!-- Loading State -->
    <!-- <v-container v-else class="d-flex justify-center align-center" style="min-height: 400px;">
        <v-progress-circular indeterminate color="primary" size="60"></v-progress-circular>
    </v-container> -->
</template>

<script setup>
    import { ref, computed, onMounted } from 'vue';
    import { useRoute } from 'vue-router';
    import { useAuthStore } from '@/stores/useAuthStore';
    import { useTournamentStore } from '@/stores/useTournamentStore';
    import { storeToRefs } from 'pinia';
    import AdminOverviewTab from '@/components/Tournaments/admin-overview-tab.vue';
    import AdminRoundsTab from '@/components/Tournaments/admin-rounds-tab.vue';
    import AdminPlayersTab from '@/components/Tournaments/admin-players-tab.vue';
    import AdminSettingsTab from '@/components/Tournaments/admin-settings-tab.vue';

    const route = useRoute();
    const authStore = useAuthStore();
    const tournamentStore = useTournamentStore();
    const { tournament, isLoading } = storeToRefs(tournamentStore);
    const settingsTabRef = ref(null);

    const activeTab = ref('overview');

    const isOrganizer = computed(() => {
        if (!authStore.user || !tournament.value) return false;
        
        // Check if user is the creator
        if (tournament.value.created_by === authStore.user.id) return true;
        
        return false;
    });

    onMounted(async () => {
        const slug = route.params.slug;
        await tournamentStore.getTournament(slug);
    });
</script>
