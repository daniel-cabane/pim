<template>
    <v-card class="mb-6" elevation="2" v-if="currentRound">
        <v-card-title class="bg-primary">
            <v-icon left color="white" class="mr-2">mdi-sword-cross</v-icon>
            <span class="text-white">{{ $t('Round') }} {{ currentRound.round_number }} — {{ $t('Pairings') }}</span>
            <v-chip class="ml-3" label size="small" color="white" text-color="primary">{{ $t(currentRound.status) }}</v-chip>
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
                                size="small""
                            >
                                {{ resultLabel(game) }}
                            </v-chip>
                        </td>
                        <td class="text-right">
                            <span v-if="game.player2" :class="{ 'font-weight-bold': game.result2 === 'win' && game.status === 'completed' }">
                                {{ playerName(game.player2) }}
                            </span>
                            <v-chip v-else size="small" label color="captionColor">{{ $t('Bye') }}</v-chip>
                        </td>
                        <td class="text-center">
                            <v-icon :color="gameStatusColor(game)" size="small">{{ gameStatusIcon(game) }}</v-icon>
                        </td>
                        <td v-if="isOrganizer" class="text-center">
                            <v-btn
                                v-if="game.status !== 'completed'"
                                icon
                                size="x-small"
                                variant="text"
                                color="primary"
                                @click="openSetResult(game)"
                            >
                                <v-icon>mdi-pencil</v-icon>
                            </v-btn>
                        </td>
                    </tr>
                </tbody>
            </v-table>
        </v-card-text>

        <v-dialog v-model="setResultDialog" max-width="450">
            <v-card :title="$t('Set result')">
                <v-card-text class="mt-2">
                    <v-row>
                        <v-col cols="6">
                            <v-select
                                v-model="editResult1"
                                :items="resultItems"
                                item-title="label"
                                item-value="value"
                                :label="playerName(editingGame?.player1) || '-'"
                                variant="outlined"
                                density="comfortable"
                                hide-details
                            />
                        </v-col>
                        <v-col cols="6">
                            <v-select
                                v-model="editResult2"
                                :items="resultItems"
                                item-title="label"
                                item-value="value"
                                :label="playerName(editingGame?.player2) || $t('Bye')"
                                variant="outlined"
                                density="comfortable"
                                hide-details
                            />
                        </v-col>
                    </v-row>
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="tonal" color="error" @click="setResultDialog = false">{{ $t('Cancel') }}</v-btn>
                    <v-btn 
                        color="primary" 
                        variant="flat"
                        :loading="tournamentStore.isLoading" 
                        :disabled="!editResult1 || !editResult2"
                        @click="submitSetResult"
                    >
                        {{ $t('Confirm') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-card>
</template>

<script setup>
    import { ref, computed } from 'vue';
    import { useI18n } from 'vue-i18n';
    import { useTournamentStore } from '@/stores/useTournamentStore';

    const { t } = useI18n();
    const tournamentStore = useTournamentStore();

    const props = defineProps({
        tournament: { type: Object, required: true },
        isOrganizer: { type: Boolean, default: false }
    });

    const emit = defineEmits(['resultSet']);

    const setResultDialog = ref(false);
    const editingGame = ref(null);
    const editResult1 = ref(null);
    const editResult2 = ref(null);

    const isKnockout = computed(() => props.tournament?.format === 'knockout');

    const resultItems = computed(() => {
        const items = [
            { label: t('Win'), value: 'win' },
            { label: t('Loss'), value: 'loss' },
        ];
        if (!isKnockout.value) {
            items.push({ label: t('Draw'), value: 'draw' });
        }
        return items;
    });

    const currentRound = computed(() => {
        if (!props.tournament?.rounds?.length) return null;
        if (props.tournament.format === 'round_robin') {
            const sorted = [...props.tournament.rounds].sort((a, b) => a.round_number - b.round_number);
            return sorted.find(r => !r.games?.every(g => g.status === 'completed')) || sorted[sorted.length - 1];
        }
        return [...props.tournament.rounds].sort((a, b) => b.round_number - a.round_number)[0];
    });

    const resultLabel = (game) => {
        if (game.status === 'completed') {
            if (game.result1 === 'win') return '1 - 0';
            if (game.result1 === 'loss') return '0 - 1';
            if (game.result1 === 'draw') return '½ - ½';
        }
        if (game.status === 'conflicted') return '⚠';
        return '—';
    };

    const resultColor = (game) => {
        if (game.status === 'completed') {
            if (game.result1 === 'draw') return 'info';
            return 'success';
        }
        if (game.status === 'conflicted') return 'error';
        return 'default';
    };

    const gameStatusColor = (game) => {
        const map = { 'pending': 'warning', 'conflicted': 'error', 'completed': 'success' };
        return map[game.status] || 'warning';
    };

    const gameStatusIcon = (game) => {
        const map = { 'pending': 'mdi-clock-outline', 'conflicted': 'mdi-alert-circle', 'completed': 'mdi-check-circle' };
        return map[game.status] || 'mdi-clock-outline';
    };

    const playerName = (player) => {
        return player?.formal_name || player?.name || null;
    };

    const openSetResult = (game) => {
        editingGame.value = game;
        editResult1.value = game.result1;
        editResult2.value = game.result2;
        setResultDialog.value = true;
    };

    const submitSetResult = async () => {
        await tournamentStore.setGameResult(editingGame.value.id, editResult1.value, editResult2.value);
        setResultDialog.value = false;
        emit('resultSet');
    };
</script>
