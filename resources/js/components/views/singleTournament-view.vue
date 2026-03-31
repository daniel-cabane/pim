<template>
    <v-container class="pb-8">
        <div v-if="tournament.id" class="mt-6">
            <tournament-header 
                :tournament="tournament"
                :isPlayerInTournament="isPlayerInTournament"
                :isPlayerBanned="isPlayerBanned"
                :isOrganizer="isOrganizer"
                @joinTournament="showJoinDialog = true"
            />

            <tournament-current-game 
                v-if="isPlayerInTournament && !isPlayerBanned"
                :tournament="tournament"
                @resultReported="refreshTournament"
            />

            <tournament-info-cards :tournament="tournament" />

            <tournament-round-pairings 
                :tournament="tournament" 
                :isOrganizer="isOrganizer"
                @resultSet="refreshTournament"
            />

            <tournament-leaderboard :standings="standings" />

            <tournament-user-stats 
                v-if="isPlayerInTournament && !isPlayerBanned"
                :userStats="userStats"
                :userRank="userRank"
            />

            <tournament-info-section 
                :tournament="tournament"
                :organizer="organizer"
            />
        </div>
        <div v-else class="text-center py-16">
            <v-icon size="64" color="error" class="mb-4">mdi-alert-circle</v-icon>
            <p class="text-h6">Tournament not found</p>
        </div>
    </v-container>

    <tournament-join-dialog 
        v-model="showJoinDialog"
        :tournamentName="tournament.name"
        :isJoining="isJoiningTournament"
        @confirm="applyTournament"
    />
</template>

<script setup>
    import { ref, computed, onMounted } from 'vue';
    import { useRoute } from 'vue-router';
    import { useAuthStore } from '@/stores/useAuthStore';
    import { useTournamentStore } from '@/stores/useTournamentStore';
    import { storeToRefs } from 'pinia';

    const route = useRoute();
    const authStore = useAuthStore();
    const tournamentStore = useTournamentStore();
    const { tournament, isLoading } = storeToRefs(tournamentStore);

    const standings = ref([]);
    const organizer = ref(null);
    const isJoiningTournament = ref(false);
    const showJoinDialog = ref(false);

    // Computed properties
    const isOrganizer = computed(() => {
        return authStore.user && (tournament.value && tournament.value.created_by === authStore.user.id || tournament.value.organisers.some(u => u.id == authStore.user.id));
    });

    const isPlayerInTournament = computed(() => {
        return tournament.value?.players?.some(p => p.id === authStore.user?.id);
    });
    const isPlayerBanned = computed(() => {
        const player = tournament.value?.players?.find(p => p.id === authStore.user?.id);
        return player ? !!player.pivot.banned : false;
    })

    const userStats = computed(() => {
        if (!isPlayerInTournament.value || !tournament.value?.players) {
            return { points: 0, wins: 0, draws: 0, losses: 0 };
        }
        const player = tournament.value.players.find(p => p.id === authStore.user.id);
        const pivot = player?.pivot || {};
        return { 
            points: pivot.score || 0, 
            wins: pivot.wins || 0, 
            draws: pivot.draws || 0, 
            losses: pivot.losses || 0 
        };
    });

    const userRank = computed(() => {
        const index = standings.value.findIndex(p => p.id === authStore.user?.id);
        return index >= 0 ? index + 1 : null;
    });

    // Methods
    const refreshTournament = async () => {
        const slug = route.params.slug;
        await tournamentStore.getTournament(slug);
        const standingsData = await tournamentStore.getTournamentStandings(slug);
        standings.value = standingsData || [];
    };

    const applyTournament = async (rating) => {
        try {
            isJoiningTournament.value = true;
            const slug = route.params.slug;
            await tournamentStore.joinTournament(slug, { rating });
            await tournamentStore.getTournament(slug);
            showJoinDialog.value = false;
        } catch (error) {
            console.error('Error joining tournament:', error);
        } finally {
            isJoiningTournament.value = false;
        }
    };

    // Lifecycle
    onMounted(async () => {
        const slug = route.params.slug;
        await tournamentStore.getTournament(slug);
        
        // Fetch standings
        const standingsData = await tournamentStore.getTournamentStandings(slug);
        standings.value = standingsData || [];
        
        // Get organizer info
        if (tournament.value) {
            organizer.value = tournament.value.creator || { name: 'Unknown' };
        }
    });
</script>