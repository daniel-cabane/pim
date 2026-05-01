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
                                    :color="game.status === 'completed' ? 'default' : 'primary'"
                                    @click="openResultDialog(game)"
                                >
                                    <span v-if="game.status === 'completed'" class="font-weight-bold">
                                        {{ resultDisplay(game) }}
                                    </span>
                                    <span v-else>
                                        <v-icon size="small" class="mr-1">mdi-pencil</v-icon>
                                        {{ $t('Set') }}
                                    </span>
                                </v-btn>
                                <span v-else class="font-weight-bold">1 - 0</span>
                            </td>
                            <td class="text-right">
                                <span class="font-weight-medium" v-if="game.player2">{{ displayName(game.player2) }}</span>
                                <span class="font-weight-bold text-captionColor" v-else>- {{  $t('Bye') }} -</span>
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

        <!-- Set result dialog -->
        <v-dialog v-model="resultDialog" max-width="450">
            <v-card :title="$t('Set game result')">
                <v-card-text v-if="editingGame">
                    <div class="d-flex align-center mb-4">
                        <div class="font-weight-medium text-truncate" style="width: 33%; text-align: left;">{{ displayName(editingGame.player1) }}</div>
                        <div class="text-caption text-captionColor" style="width: 34%; text-align: center;">vs</div>
                        <div class="font-weight-medium text-truncate" style="width: 33%; text-align: right;">{{ displayName(editingGame.player2) }}</div>
                    </div>
                    <v-btn-toggle v-model="selectedResultOption" mandatory color="primary" class="d-flex" style="width: 100%;">
                        <v-btn value="white_wins" :style="{ width: isKnockout ? '50%' : '33.33%' }" stacked>
                            <span class="font-weight-bold">1 - 0</span>
                            <div class="text-caption">{{ $t('White wins') }}</div>
                        </v-btn>
                        <v-btn v-if="!isKnockout" value="draw" style="width: 33.34%;" stacked>
                            <span class="font-weight-bold">½ - ½</span>
                            <div class="text-caption">{{ $t('Draw') }}</div>
                        </v-btn>
                        <v-btn value="black_wins" :style="{ width: isKnockout ? '50%' : '33.33%' }" stacked>
                            <span class="font-weight-bold">0 - 1</span>
                            <div class="text-caption">{{ $t('Black wins') }}</div>
                        </v-btn>
                    </v-btn-toggle>
                </v-card-text>
                <v-card-actions class="pa-4">
                    <v-spacer />
                    <v-btn variant="tonal" color="error" @click="resultDialog = false">{{ $t('Cancel') }}</v-btn>
                    <v-btn color="primary" variant="flat" :loading="tournamentStore.isLoading" :disabled="!selectedResultOption" @click="submitGameResult">
                        {{ $t('Confirm') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script setup>
    import { ref, computed } from 'vue';
    import { useTournamentStore } from '@/stores/useTournamentStore';

    const tournamentStore = useTournamentStore();

    const props = defineProps({
        tournament: { type: Object, required: true }
    });

    const nextRoundDialog = ref(false);
    const resultDialog = ref(false);
    const editingGame = ref(null);
    const selectedResultOption = ref(null);

    const isRoundRobin = computed(() => props.tournament?.format === 'round_robin');
    const isKnockout = computed(() => props.tournament?.format === 'knockout');

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

    const completedGames = (round) => {
        return round.games?.filter(g => g.status === 'completed').length || 0;
    };

    const allGamesCompleted = (round) => {
        if (!round.games?.length) return false;
        return round.games.every(g => g.status === 'completed');
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
        if (game.result1 === 'win') return '1 - 0';
        if (game.result1 === 'loss') return '0 - 1';
        if (game.result1 === 'draw') return '½ - ½';
        return '—';
    };

    const gameStatusColor = (status) => {
        const map = { pending: 'warning', conflicted: 'error', completed: 'success' };
        return map[status] || 'captionColor';
    };

    const gameStatusIcon = (status) => {
        const map = { pending: 'mdi-clock-outline', conflicted: 'mdi-alert-circle', completed: 'mdi-check-circle' };
        return map[status] || 'mdi-clock-outline';
    };

    const displayName = (player) => {
        return player?.formal_name || player?.name || '';
    };

    const createNextRound = async () => {
        await tournamentStore.createNextRound(props.tournament.slug);
        nextRoundDialog.value = false;
    };

    const openResultDialog = (game) => {
        editingGame.value = game;
        if (game.status === 'completed') {
            if (game.result1 === 'win') selectedResultOption.value = 'white_wins';
            else if (game.result1 === 'loss') selectedResultOption.value = 'black_wins';
            else if (game.result1 === 'draw') selectedResultOption.value = 'draw';
        } else {
            selectedResultOption.value = null;
        }
        resultDialog.value = true;
    };

    const submitGameResult = async () => {
        const resultMap = {
            white_wins: { result1: 'win', result2: 'loss' },
            black_wins: { result1: 'loss', result2: 'win' },
            draw: { result1: 'draw', result2: 'draw' },
        };
        const { result1, result2 } = resultMap[selectedResultOption.value];
        await tournamentStore.setGameResult(editingGame.value.id, result1, result2);
        resultDialog.value = false;
        editingGame.value = null;
    };
</script>