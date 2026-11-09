<template>
    <v-card class="mb-6" elevation="2" v-if="currentRound">
        <v-card-title class="bg-primary">
            <v-icon left color="white" class="mr-2">mdi-sword-cross</v-icon>
            <span class="text-white">{{ $t('Round') }} {{ currentRound.round_number }} — {{ $t('Pairings') }}</span>
            <v-chip class="ml-3" label size="small" color="white" text-color="primary">{{ $t(currentRound.status) }}</v-chip>
            <v-spacer />
            <v-btn
                v-if="canRepair && repairByeCount >= 2"
                size="small"
                color="white"
                variant="flat"
                @click="pairOpen = true"
            >
                {{ $t('Pair players') }}
            </v-btn>
        </v-card-title>
        <v-card-text class="pt-4">
            <v-table density="compact">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-left">{{ $t('White') }}</th>
                        <th class="text-center">{{ $t('Result') }}</th>
                        <th class="text-right">{{ $t('Black') }}</th>
                        <th class="text-center">{{ $t('Status') }}</th>
                        <th v-if="isOrganizer" class="text-center">{{ $t('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(game, index) in currentRound.games" :key="game.id">
                        <td class="text-center">{{ index + 1 }}</td>
                        <td class="text-left">
                            <span :class="{ 'font-weight-bold': game.result1 === 'win' && game.status === 'completed' }">
                                {{ playerName(game.player1) || '-' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <v-chip 
                                :color="resultColor(game)" 
                                label 
                                size="small"
                                :style="canEditUnpaired(game) ? 'cursor: pointer' : undefined"
                                @click="canEditUnpaired(game) && openUnpaired(game)"
                            >
                                {{ resultLabel(game) }}
                            </v-chip>
                        </td>
                        <td class="text-right">
                            <span v-if="game.player2" :class="{ 'font-weight-bold': game.result2 === 'win' && game.status === 'completed' }">
                                {{ playerName(game.player2) }}
                            </span>
                            <v-chip v-else size="small" label color="captionColor">{{ game.status === 'absent' ? $t('Absent') : $t('Bye') }}</v-chip>
                        </td>
                        <td class="text-center">
                            <v-icon :color="gameStatusColor(game)" size="small">{{ gameStatusIcon(game) }}</v-icon>
                        </td>
                        <td v-if="isOrganizer" class="text-center">
                            <v-btn
                                v-if="game.player2_id"
                                icon
                                size="x-small"
                                variant="text"
                                color="primary"
                                @click="openSetResult(game)"
                            >
                                <v-icon>mdi-pencil</v-icon>
                            </v-btn>
                            <v-btn
                                v-else-if="canEditUnpaired(game)"
                                icon
                                size="x-small"
                                variant="text"
                                color="primary"
                                @click="openUnpaired(game)"
                            >
                                <v-icon>mdi-pencil</v-icon>
                            </v-btn>
                        </td>
                    </tr>
                </tbody>
            </v-table>
        </v-card-text>

        <game-result-dialog
            v-model="setResultDialog"
            :game="editingGame"
            :tournament="tournament"
            title="Set result"
            @done="emit('resultSet')"
        />
        <swiss-repair-dialogs
            :tournament="tournament"
            :round="currentRound"
            v-model:pair-open="pairOpen"
            v-model:unpaired-game="editingUnpaired"
            @update:unpaired-game="onUnpairedClosed"
        />
    </v-card>
</template>

<script setup>
    import { ref, computed } from 'vue';
    import SwissRepairDialogs from '@/components/Tournaments/swiss-repair-dialogs.vue';
    import GameResultDialog from '@/components/Tournaments/game-result-dialog.vue';
    import { isUnpaired, unpairedPlayers } from '@/composables/useSwissRepair';

    const props = defineProps({
        tournament: { type: Object, required: true },
        isOrganizer: { type: Boolean, default: false }
    });

    const emit = defineEmits(['resultSet']);

    const setResultDialog = ref(false);
    const editingGame = ref(null);
    const pairOpen = ref(false);
    const editingUnpaired = ref(null);

    const canRepair = computed(() => {
        return props.isOrganizer && props.tournament?.format === 'swiss' && props.tournament?.status === 'ongoing';
    });

    const repairByeCount = computed(() => unpairedPlayers(currentRound.value, props.tournament?.players).length);

    const currentRound = computed(() => {
        if (!props.tournament?.rounds?.length) return null;
        if (props.tournament.format === 'round_robin') {
            const sorted = [...props.tournament.rounds].sort((a, b) => a.round_number - b.round_number);
            return sorted.find(r => !r.games?.every(g => g.status === 'completed' || g.status === 'cancelled' || g.status === 'absent')) || sorted[sorted.length - 1];
        }
        return [...props.tournament.rounds].sort((a, b) => b.round_number - a.round_number)[0];
    });

    const resultLabel = (game) => {
        if (game.status === 'absent') return '0';
        if (game.status === 'cancelled') return '✕ - ✕';
        if (game.status === 'completed') {
            if (game.result1 === 'win') return '1 - 0';
            if (game.result1 === 'loss') return '0 - 1';
            if (game.result1 === 'draw') return '½ - ½';
        }
        if (game.status === 'conflicted') return '⚠';
        return '—';
    };

    const resultColor = (game) => {
        if (game.status === 'cancelled') return 'default';
        if (game.status === 'completed') {
            if (game.result1 === 'draw') return 'info';
            return 'success';
        }
        if (game.status === 'conflicted') return 'error';
        return 'default';
    };

    const gameStatusColor = (game) => {
        const map = { 'pending': 'warning', 'conflicted': 'error', 'completed': 'success', 'cancelled': 'default', 'absent': 'default' };
        return map[game.status] || 'warning';
    };

    const gameStatusIcon = (game) => {
        const map = { 'pending': 'mdi-clock-outline', 'conflicted': 'mdi-alert-circle', 'completed': 'mdi-check-circle', 'cancelled': 'mdi-cancel', 'absent': 'mdi-account-off' };
        return map[game.status] || 'mdi-clock-outline';
    };

    const playerName = (player) => {
        return player?.formal_name || null;
    };

    const openSetResult = (game) => {
        editingGame.value = game;
        setResultDialog.value = true;
    };

    const canEditUnpaired = (game) => canRepair.value && isUnpaired(game);

    const openUnpaired = (game) => {
        editingUnpaired.value = game;
    };

    const onUnpairedClosed = (game) => {
        if (!game) {
            emit('resultSet');
        }
    };
</script>
