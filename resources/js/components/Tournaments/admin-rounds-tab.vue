<template>
    <div>
        <!-- Next round button (hidden for round robin) -->
        <v-btn
            v-if="tournament.status === 'ongoing' && tournament.format !== 'round_robin'"
            block
            size="large"
            color="primary"
            class="mb-4"
            prepend-icon="mdi-plus"
            :disabled="!canStartNextRound || tournamentStore.isLoading"
            @click="nextRoundDialog = true"
        >
            {{ $t('Start next round') }}
        </v-btn>
        <v-alert v-if="tournament.status === 'ongoing' && tournament.format !== 'round_robin' && !canStartNextRound && sortedRounds.length > 0" 
            type="info" variant="tonal" density="compact" class="my-2"
        >
            {{ $t('All games in the current round must be completed before starting a new round') }}
        </v-alert>

        <!-- Rounds list -->
        <v-card v-if="sortedRounds.length > 0" v-for="round in sortedRounds" :key="round.id" 
            class="mb-4" 
            :elevation="isCurrentRound(round) ? 4 : 1"
            :border="isCurrentRound(round) ? 'warning md' : undefined"
        >
            <v-card-title class="d-flex align-center" :class="isCurrentRound(round) ? 'bg-warning' : 'bg-surface'">
                <v-icon class="mr-2" :color="isCurrentRound(round) ? 'black' : undefined">
                    {{ isCurrentRound(round) ? 'mdi-play-circle' : 'mdi-checkbox-marked-circle' }}
                </v-icon>
                <span :class="{ 'text-black': isCurrentRound(round) }">
                    {{ $t('Round') }} {{ round.round_number }}
                </span>
                <v-chip 
                    class="ml-3" 
                    :color="getRoundStatusColor(round)" 
                    size="small" 
                    label
                >
                    {{ isCurrentRound(round) ? $t('Current') : $t(roundStatusLabel(round)) }}
                </v-chip>
                <v-spacer />
                <span class="text-caption" :class="isCurrentRound(round) ? 'text-black' : 'text-captionColor'">
                    {{ completedGames(round) }}/{{ round.games?.length || 0 }} {{ $t('games completed') }}
                </span>
            </v-card-title>

            <v-card-text class="pa-0">
                <div v-if="isRepairRound(round) && repairByeCount(round) >= 2" class="pa-4 pb-0">
                    <v-btn color="primary" variant="tonal" prepend-icon="mdi-account-multiple-plus" @click="pairOpen = true">
                        {{ $t('Pair players') }}
                    </v-btn>
                </div>
                <v-table density="comfortable">
                    <thead>
                        <tr>
                            <th class="font-weight-bold">#</th>
                            <th class="font-weight-bold">{{ $t('White') }}</th>
                            <th class="text-center font-weight-bold">{{ $t('Result') }}</th>
                            <th class="text-right font-weight-bold">{{ $t('Black') }}</th>
                            <th class="text-center font-weight-bold">{{ $t('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(game, gIdx) in round.games" :key="game.id">
                            <td class="text-captionColor">{{ gIdx + 1 }}</td>
                            <td>
                                <span class="font-weight-medium" v-if="game.player1">{{ displayName(game.player1) }}</span>
                                <span class="font-weight-bold text-captionColor" v-else>- {{  $t('Bye') }} -</span>
                            </td>
                            <td class="text-center">
                                <v-btn 
                                    v-if="game.player2_id"
                                    variant="text" 
                                    size="small"
                                    :color="(game.status === 'completed' || game.status === 'cancelled') ? 'default' : 'primary'"
                                    @click="openResultDialog(game)"
                                >
                                    <span v-if="game.status === 'completed' || game.status === 'cancelled'" class="font-weight-bold">
                                        {{ resultDisplay(game) }}
                                    </span>
                                    <span v-else>
                                        <v-icon size="small" class="mr-1">mdi-pencil</v-icon>
                                        {{ $t('Set') }}
                                    </span>
                                </v-btn>
                                <v-btn
                                    v-else-if="isRepairRound(round) && isUnpaired(game)"
                                    variant="text"
                                    size="small"
                                    @click="editingUnpaired = game"
                                >
                                    <v-icon size="small" class="mr-1">mdi-pencil</v-icon>
                                    {{ game.status === 'absent' ? '0' : '1 - 0' }}
                                </v-btn>
                                <span v-else class="font-weight-bold">{{ game.status === 'absent' ? '0' : '1 - 0' }}</span>
                            </td>
                            <td class="text-right">
                                <span class="font-weight-medium" v-if="game.player2">{{ displayName(game.player2) }}</span>
                                <span class="font-weight-bold text-captionColor" v-else>- {{ game.status === 'absent' ? $t('Absent') : $t('Bye') }} -</span>
                            </td>
                            <td class="text-center">
                                <v-icon :color="gameStatusColor(game.status)" size="small">
                                    {{ gameStatusIcon(game.status) }}
                                </v-icon>
                            </td>
                        </tr>
                    </tbody>
                </v-table>

                <!-- Round progress bar -->
                <v-progress-linear
                    :model-value="roundProgress(round)"
                    :color="allGamesCompleted(round) ? 'success' : 'warning'"
                    height="4"
                />
            </v-card-text>
        </v-card>

        <!-- Empty state -->
        <v-card v-if="sortedRounds.length === 0">
            <v-card-text class="text-center py-8">
                <v-icon size="48" color="captionColor" class="mb-3">mdi-tournament</v-icon>
                <p class="text-captionColor">{{ $t('No rounds yet') }}</p>
            </v-card-text>
        </v-card>

        <!-- Confirmation dialog -->
        <v-dialog v-model="nextRoundDialog" max-width="450">
            <v-card :title="$t('Start next round')">
                <v-card-text>
                    {{ $t('This will generate pairings for round') }} {{ nextRoundNumber }}.
                    {{ $t('Are you sure?') }}
                </v-card-text>
                <v-card-actions class="pa-4">
                    <v-spacer />
                    <v-btn variant="tonal" color="error" @click="nextRoundDialog = false">{{ $t('Cancel') }}</v-btn>
                    <v-btn color="primary" variant="flat" :loading="tournamentStore.isLoading" @click="createNextRound">
                        {{ $t('Confirm') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <game-result-dialog
            v-model="resultDialog"
            :game="editingGame"
            :tournament="tournament"
        />

        <swiss-repair-dialogs
            :tournament="tournament"
            :round="repairRound"
            v-model:pair-open="pairOpen"
            v-model:unpaired-game="editingUnpaired"
        />
    </div>
</template>

<script setup>
    import { ref, computed } from 'vue';
    import { useTournamentStore } from '@/stores/useTournamentStore';
    import SwissRepairDialogs from '@/components/Tournaments/swiss-repair-dialogs.vue';
    import GameResultDialog from '@/components/Tournaments/game-result-dialog.vue';
    import { isUnpaired, unpairedPlayers } from '@/composables/useSwissRepair';

    const tournamentStore = useTournamentStore();

    const props = defineProps({
        tournament: { type: Object, required: true }
    });

    const nextRoundDialog = ref(false);
    const resultDialog = ref(false);
    const editingGame = ref(null);
    const pairOpen = ref(false);
    const editingUnpaired = ref(null);

    const isRoundRobin = computed(() => props.tournament?.format === 'round_robin');

    const sortedRounds = computed(() => {
        if (!props.tournament?.rounds?.length) return [];
        if (isRoundRobin.value) {
            return [...props.tournament.rounds].sort((a, b) => a.round_number - b.round_number);
        }
        return [...props.tournament.rounds].sort((a, b) => b.round_number - a.round_number);
    });

    const currentRound = computed(() => {
        if (!sortedRounds.value.length) return null;
        if (isRoundRobin.value) {
            // First unfinished round
            return sortedRounds.value.find(r => !allGamesCompleted(r)) || null;
        }
        return sortedRounds.value[0];
    });

    const isCurrentRound = (round) => {
        return props.tournament.status === 'ongoing' && currentRound.value?.id === round.id && !allGamesCompleted(round);
    };

    const repairRound = computed(() => {
        if (props.tournament?.format !== 'swiss' || props.tournament?.status !== 'ongoing') return null;
        const rounds = props.tournament.rounds || [];
        if (!rounds.length) return null;
        return rounds.reduce((latest, round) => round.round_number > latest.round_number ? round : latest);
    });

    const isRepairRound = (round) => repairRound.value?.id === round.id;

    const repairByeCount = (round) => unpairedPlayers(round, props.tournament?.players).length;

    const isGameDone = (g) => g.status === 'completed' || g.status === 'cancelled' || g.status === 'absent';

    const completedGames = (round) => {
        return round.games?.filter(isGameDone).length || 0;
    };

    const allGamesCompleted = (round) => {
        if (!round.games?.length) return false;
        return round.games.every(isGameDone);
    };

    const roundProgress = (round) => {
        const total = round.games?.length || 0;
        if (total === 0) return 0;
        return (completedGames(round) / total) * 100;
    };

    const canStartNextRound = computed(() => {
        if (!currentRound.value) return true;
        return allGamesCompleted(currentRound.value);
    });

    const nextRoundNumber = computed(() => {
        return (sortedRounds.value.length || 0) + 1;
    });

    const getRoundStatusColor = (round) => {
        if (isCurrentRound(round)) return 'warning';
        if (allGamesCompleted(round)) return 'success';
        return 'captionColor';
    };

    const roundStatusLabel = (round) => {
        if (allGamesCompleted(round)) return 'Completed';
        return 'Pending';
    };

    const resultDisplay = (game) => {
        if (game.status === 'cancelled') return '✕ - ✕';
        if (game.result1 === 'win') return '1 - 0';
        if (game.result1 === 'loss') return '0 - 1';
        if (game.result1 === 'draw') return '½ - ½';
        return '—';
    };

    const gameStatusColor = (status) => {
        const map = { pending: 'warning', conflicted: 'error', completed: 'success', cancelled: 'default', absent: 'default' };
        return map[status] || 'captionColor';
    };

    const gameStatusIcon = (status) => {
        const map = { pending: 'mdi-clock-outline', conflicted: 'mdi-alert-circle', completed: 'mdi-check-circle', cancelled: 'mdi-cancel', absent: 'mdi-account-off' };
        return map[status] || 'mdi-clock-outline';
    };

    const displayName = (player) => {
        return player?.name || '';
    };

    const createNextRound = async () => {
        await tournamentStore.createNextRound(props.tournament.slug);
        nextRoundDialog.value = false;
    };

    const openResultDialog = (game) => {
        editingGame.value = game;
        resultDialog.value = true;
    };
</script>