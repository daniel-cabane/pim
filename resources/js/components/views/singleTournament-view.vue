<template>
    <v-container v-if="!isLoading" class="pb-8">
        <div v-if="tournament.id" class="mt-6">
            <!-- Header with Admin Link and Player Status -->
            <div class="d-flex justify-space-between align-center mb-6">
                <div>
                    <div class="pimSubtitleFont" style="font-size: 56px;">
                        {{ tournament.name }}
                    </div>
                    <div class="text-captionColor text-subtitle-1 ml-2">
                        {{ tournament.description }}
                    </div>
                </div>
                <div class="d-flex gap-3 align-center">
                    <!-- Player Label -->
                    <v-chip 
                        v-if="authStore.user && isPlayerInTournament"
                        color="success"
                        text-color="white"
                        size="large"
                        label
                        class="text-h6 font-weight-bold px-4"
                    >
                        <v-icon left>mdi-chess-pawn</v-icon>
                        {{ $t('Player') }}
                    </v-chip>

                    <!-- Join Button -->
                    <v-btn 
                        v-else-if="authStore.user && !isPlayerInTournament && tournament.status === 'preparation'"
                        color="primary"
                        size="large"
                        @click="showJoinDialog = true"
                    >
                        <v-icon left>mdi-plus</v-icon>
                        {{ $t('Join Tournament') }}
                    </v-btn>

                    <!-- Admin Button -->
                    <v-btn 
                        v-if="isOrganizer" 
                        :to="`/tournaments/${tournament.slug}/admin`"
                        color="success"
                        icon
                        size="large"
                        class="ml-4"
                    >
                        <v-icon>mdi-cog</v-icon>
                        <v-tooltip activator="parent">{{ $t('Manage Tournament') }}</v-tooltip>
                    </v-btn>
                </div>
            </div>

            <!-- Tournament Info Cards -->
            <v-row class="mb-6">
                <v-col cols="12" sm="6" md="3">
                    <v-card class="h-100" elevation="2">
                        <v-card-text>
                            <div class="text-center">
                                <v-icon size="40" color="info" class="mb-2">mdi-tournament</v-icon>
                                <div class="text-h6 font-weight-bold text-capitalize">{{ $t(tournament.format) }}</div>
                                <div class="text-captionColor text-caption mb-1">{{ $t('Pairing') }}</div>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                    <v-card class="h-100" elevation="2">
                        <v-card-text>
                            <div class="text-center">
                                <v-icon size="40" color="success" class="mb-2">mdi-account-multiple</v-icon>
                                <div class="text-h6 font-weight-bold">{{ tournament.players_count }}</div>
                                <div class="text-captionColor text-caption mb-1">{{ $t('Players') }}</div>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                    <v-card class="h-100" elevation="2">
                        <v-card-text>
                            <div class="text-center">
                                <v-icon size="40" color="warning" class="mb-2">mdi-play-circle</v-icon>
                                <div class="text-h6 font-weight-bold">{{ tournament.rounds_count }}</div>
                                <div class="text-captionColor text-caption mb-1">{{ $t('Rounds')}}</div>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                    <v-card class="h-100" elevation="2">
                        <v-card-text>
                            <div class="text-center">
                                <v-icon 
                                    size="40" 
                                    :color="statusColor" 
                                    class="mb-2"
                                >{{ statusIcon }}</v-icon>
                                <div class="text-h6 font-weight-bold text-capitalize">{{ $t(tournament.status) }}</div>
                                <div class="text-captionColor text-caption mb-1">{{ $t('Status') }}</div>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>

            <!-- Leaderboard Section -->
            <v-card class="mb-6" elevation="2">
                <v-card-title class="bg-primary">
                    <v-icon left color="white" class="mr-2">mdi-trophy</v-icon>
                    <span class="text-white">{{ $t('Leaderboard') }}</span>
                </v-card-title>
                <v-card-text class="pt-4">
                    <v-table v-if="standings.length" density="compact">
                        <thead>
                            <tr>
                                <th class="text-left">#</th>
                                <th class="text-left">Player</th>
                                <th class="text-center">Points</th>
                                <th class="text-center">Wins</th>
                                <th class="text-center">Draws</th>
                                <th class="text-center">Losses</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="(player, index) in standings" 
                                :key="player.id"
                                :class="{ 'bg-light-blue': isCurrentUserPlayer(player.id) }"
                            >
                                <td>
                                    <v-chip 
                                        v-if="index === 0"
                                        color="gold"
                                        text-color="black"
                                        size="small"
                                        label
                                    >🥇 {{ index + 1 }}</v-chip>
                                    <v-chip 
                                        v-else-if="index === 1"
                                        color="silver"
                                        text-color="black"
                                        size="small"
                                        label
                                    >🥈 {{ index + 1 }}</v-chip>
                                    <v-chip 
                                        v-else-if="index === 2"
                                        color="bronze"
                                        text-color="black"
                                        size="small"
                                        label
                                    >🥉 {{ index + 1 }}</v-chip>
                                    <span v-else>{{ index + 1 }}</span>
                                </td>
                                <td>
                                    <div class="font-weight-bold">{{ player.name }}</div>
                                    <div v-if="isCurrentUserPlayer(player.id)" class="text-caption text-primary">(You)</div>
                                </td>
                                <td class="text-center font-weight-bold">{{ player.points }}</td>
                                <td class="text-center">
                                    <v-chip size="small" color="success" text-color="white">{{ player.wins }}</v-chip>
                                </td>
                                <td class="text-center">
                                    <v-chip size="small" color="warning" text-color="white">{{ player.draws }}</v-chip>
                                </td>
                                <td class="text-center">
                                    <v-chip size="small" color="error" text-color="white">{{ player.losses }}</v-chip>
                                </td>
                            </tr>
                        </tbody>
                    </v-table>
                    <div v-else class="text-center py-8">
                        <v-icon size="64" color="captionColor" class="mb-2">mdi-table</v-icon>
                        <p class="text-captionColor">{{ $t('No standings available yet') }}</p>
                    </div>
                </v-card-text>
            </v-card>

            <!-- User Stats Section (if player) -->
            <v-card v-if="isPlayerInTournament" class="mb-6" elevation="2">
                <v-card-title class="bg-blue">
                    <v-icon left color="white" class="mr-2">mdi-chart-line</v-icon>
                    <span class="text-white">Your Stats</span>
                </v-card-title>
                <v-card-text class="pt-4">
                    <v-row>
                        <v-col cols="12" sm="6" md="3">
                            <div class="text-center">
                                <div class="text-captionColor text-caption mb-2">Rank</div>
                                <div class="text-h4 font-weight-bold text-primary">
                                    {{ userRank || '-' }}
                                </div>
                            </div>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <div class="text-center">
                                <div class="text-captionColor text-caption mb-2">Points</div>
                                <div class="text-h4 font-weight-bold">{{ userStats.points || 0 }}</div>
                            </div>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <div class="text-center">
                                <div class="text-captionColor text-caption mb-2">Record</div>
                                <div class="text-subtitle-1">
                                    <span class="text-success font-weight-bold">{{ userStats.wins || 0 }}W</span>
                                    <span class="text-warning font-weight-bold mx-2">{{ userStats.draws || 0 }}D</span>
                                    <span class="text-error font-weight-bold">{{ userStats.losses || 0 }}L</span>
                                </div>
                            </div>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <div class="text-center">
                                <div class="text-captionColor text-caption mb-2">Win Rate</div>
                                <div class="text-h6 font-weight-bold">
                                    {{ calculateWinRate() }}%
                                </div>
                            </div>
                        </v-col>
                    </v-row>
                </v-card-text>
            </v-card>

            <!-- Tournament Statistics -->
            <v-card elevation="2">
                <v-card-title class="bg-secondary">
                    <v-icon left color="white" class="mr-2">mdi-information</v-icon>
                    <span class="text-white">{{ $t('Tournament Info') }}</span>
                </v-card-title>
                <v-card-text class="pt-4">
                    <v-row>
                        <v-col cols="12" sm="6">
                            <div class="mb-4">
                                <label class="text-captionColor text-caption">{{ $t('Organizer') }}</label>
                                <p class="text-body1 font-weight-bold">{{ organizer?.name || 'Unknown' }}</p>
                            </div>
                        </v-col>
                        <v-col cols="12" sm="6">
                            <div class="mb-4">
                                <label class="text-captionColor text-caption">{{ $t('Created') }}</label>
                                <p class="text-body1">{{ formatDate(tournament.created_at) }}</p>
                            </div>
                        </v-col>
                        <v-col cols="12" sm="6" v-if="tournament.started_at">
                            <div class="mb-4">
                                <label class="text-captionColor text-caption">Started</label>
                                <p class="text-body1">{{ formatDate(tournament.started_at) }}</p>
                            </div>
                        </v-col>
                        <v-col cols="12" sm="6" v-if="tournament.ended_at">
                            <div class="mb-4">
                                <label class="text-captionColor text-caption">Ended</label>
                                <p class="text-body1">{{ formatDate(tournament.ended_at) }}</p>
                            </div>
                        </v-col>
                    </v-row>
                </v-card-text>
            </v-card>

        </div>
        <div v-else class="text-center py-16">
            <v-icon size="64" color="error" class="mb-4">mdi-alert-circle</v-icon>
            <p class="text-h6">Tournament not found</p>
        </div>
    </v-container>
    <!-- Loading State -->
    <v-container v-else class="d-flex justify-center align-center" style="min-height: 400px;">
        <v-progress-circular indeterminate color="primary" size="60"></v-progress-circular>
    </v-container>

    <!-- Join Tournament Dialog -->
    <v-dialog v-model="showJoinDialog" width="500">
        <v-card>
            <v-card-title class="bg-primary text-white">
                <v-icon left>mdi-chess-pawn</v-icon>
                Join {{ tournament.name }}
            </v-card-title>
            <v-card-text class="pt-6">
                <p class="mb-6 text-subtitle-2">Please enter your chess rating to join the tournament.</p>
                
                <v-text-field
                    v-model.number="playerRating"
                    label="Chess Rating"
                    type="number"
                    min="1000"
                    max="2800"
                    outlined
                    dense
                    class="mb-4"
                    hint="Rating should be between 1000 and 2800"
                    persistent-hint
                />

                <p class="text-caption text-grey">
                    Your rating will be recorded with your application and used for pairing in the tournament.
                </p>
            </v-card-text>
            
            <v-card-actions class="pa-4">
                <v-spacer></v-spacer>
                <v-btn 
                    variant="tonal"
                    @click="showJoinDialog = false"
                    :disabled="isJoiningTournament"
                >
                    Cancel
                </v-btn>
                <v-btn 
                    color="primary"
                    @click="confirmJoinTournament"
                    :loading="isJoiningTournament"
                    :disabled="!playerRating || playerRating < 1000 || playerRating > 2800"
                >
                    Join Tournament
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
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
    const playerRating = ref(800);

    // Computed properties
    const isOrganizer = computed(() => {
        return authStore.user && tournament.value && tournament.value.created_by === authStore.user.id;
    });

    const isPlayerInTournament = computed(() => {
        return tournament.value?.players?.some(p => p.id === authStore.user?.id);
    });

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

    const statusIcon = computed(() => {
        const iconMap = {
            'draft': 'mdi-pencil',
            'ongoing': 'mdi-play-circle',
            'completed': 'mdi-check-circle'
        };
        return iconMap[tournament.value?.status] || 'mdi-help-circle';
    });

    const statusColor = computed(() => {
        const colorMap = {
            'draft': 'captionColor',
            'ongoing': 'warning',
            'completed': 'success'
        };
        return colorMap[tournament.value?.status] || 'captionColor';
    });

    // Methods
    const isCurrentUserPlayer = (playerId) => {
        return authStore.user && authStore.user.id === playerId;
    };

    const applyTournament = async () => {
        try {
            isJoiningTournament.value = true;
            const slug = route.params.slug;
            await tournamentStore.joinTournament(slug, { rating: playerRating.value });
            // Refresh tournament data to update player list
            await tournamentStore.getTournament(slug);
            showJoinDialog.value = false;
            playerRating.value = 1600;
        } catch (error) {
            console.error('Error joining tournament:', error);
        } finally {
            isJoiningTournament.value = false;
        }
    };

    const confirmJoinTournament = () => {
        if (playerRating.value && playerRating.value >= 1000 && playerRating.value <= 2800) {
            applyTournament();
        }
    };
    

    const calculateWinRate = () => {
        const totalGames = (userStats.value.wins || 0) + (userStats.value.draws || 0) + (userStats.value.losses || 0);
        if (totalGames === 0) return 0;
        return Math.round(((userStats.value.wins || 0) / totalGames) * 100);
    };

    const formatDate = (date) => {
        if (!date) return '-';
        return new Date(date).toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
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

<style scoped>
.bg-light-blue {
    background-color: rgba(25, 118, 210, 0.05) !important;
}

.bg-primary {
    background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
}

.bg-blue {
    background: linear-gradient(135deg, #42a5f5 0%, #1976d2 100%);
}

.bg-secondary {
    background: linear-gradient(135deg, #7c4dff 0%, #512da8 100%);
}
</style>