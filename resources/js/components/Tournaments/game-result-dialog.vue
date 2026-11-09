<template>
    <v-dialog :model-value="modelValue" max-width="450" @update:model-value="emit('update:modelValue', $event)">
        <v-card v-if="game">
            <v-card-title class="d-flex align-center pr-2">
                <span>{{ $t(title) }}</span>
                <v-spacer />
                <v-menu v-if="showMenu" location="bottom end">
                    <template #activator="{ props: menuProps }">
                        <v-btn icon variant="text" v-bind="menuProps" :aria-label="$t('Action')">
                            <v-icon>mdi-dots-vertical</v-icon>
                        </v-btn>
                    </template>
                    <v-list density="compact">
                        <v-list-item v-if="!isKnockout" @click="openStep('cancel')">
                            <v-list-item-title>{{ $t('Cancel game') }}</v-list-item-title>
                        </v-list-item>
                        <v-list-item v-if="canBreakGame" @click="openStep('break')">
                            <v-list-item-title>{{ $t('Break game') }}</v-list-item-title>
                        </v-list-item>
                        <v-list-item v-if="canResetGame" @click="openStep('reset')">
                            <v-list-item-title>{{ $t('Reset game') }}</v-list-item-title>
                        </v-list-item>
                    </v-list>
                </v-menu>
            </v-card-title>

            <v-window v-model="step">
                <v-window-item value="result">
                    <v-card-text>
                        <div class="d-flex align-center mb-4">
                            <div class="font-weight-medium text-truncate" style="width: 33%; text-align: left;">{{ playerLabel(game.player1) }}</div>
                            <div class="text-caption text-captionColor" style="width: 34%; text-align: center;">vs</div>
                            <div class="font-weight-medium text-truncate" style="width: 33%; text-align: right;">{{ playerLabel(game.player2) }}</div>
                        </div>
                        <v-btn-toggle v-model="selectedResultOption" color="primary" class="d-flex" style="width: 100%;">
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
                        <v-btn variant="tonal" color="error" @click="close">{{ $t('Close') }}</v-btn>
                        <v-btn color="primary" variant="flat" :loading="tournamentStore.isLoading" :disabled="!selectedResultOption" @click="submitResult">
                            {{ $t('Confirm') }}
                        </v-btn>
                    </v-card-actions>
                </v-window-item>

                <v-window-item value="cancel">
                    <v-card-text>
                        <p>{{ $t('Cancel game explanation') }}</p>
                        <v-checkbox v-model="acknowledged" :label="$t('I understand')" hide-details color="primary" />
                    </v-card-text>
                    <v-card-actions class="pa-4">
                        <v-btn variant="text" @click="openStep('result')">{{ $t('Back') }}</v-btn>
                        <v-spacer />
                        <v-btn color="primary" variant="flat" :disabled="!acknowledged" :loading="tournamentStore.isLoading" @click="submitCancel">
                            {{ $t('Confirm') }}
                        </v-btn>
                    </v-card-actions>
                </v-window-item>

                <v-window-item value="break">
                    <v-card-text>
                        <p class="mb-4">{{ $t('Break game explanation') }}</p>
                        <div class="mb-4">
                            <div class="font-weight-medium mb-2">{{ playerLabel(game.player1) }}</div>
                            <v-btn-toggle v-model="player1Outcome" mandatory divided color="primary" class="d-flex">
                                <v-btn value="bye" class="flex-grow-1">{{ $t('Bye') }} · 1</v-btn>
                                <v-btn value="absent" class="flex-grow-1">{{ $t('Absent') }} · 0</v-btn>
                            </v-btn-toggle>
                        </div>
                        <div>
                            <div class="font-weight-medium mb-2">{{ playerLabel(game.player2) }}</div>
                            <v-btn-toggle v-model="player2Outcome" mandatory divided color="primary" class="d-flex">
                                <v-btn value="bye" class="flex-grow-1">{{ $t('Bye') }} · 1</v-btn>
                                <v-btn value="absent" class="flex-grow-1">{{ $t('Absent') }} · 0</v-btn>
                            </v-btn-toggle>
                        </div>
                        <v-checkbox v-model="acknowledged" :label="$t('I understand')" hide-details color="primary" />
                    </v-card-text>
                    <v-card-actions class="pa-4">
                        <v-btn variant="text" @click="openStep('result')">{{ $t('Back') }}</v-btn>
                        <v-spacer />
                        <v-btn color="primary" variant="flat" :disabled="!acknowledged" :loading="tournamentStore.isLoading" @click="submitBreak">
                            {{ $t('Confirm') }}
                        </v-btn>
                    </v-card-actions>
                </v-window-item>

                <v-window-item value="reset">
                    <v-card-text>
                        <p>{{ $t('Reset game explanation') }}</p>
                        <v-checkbox v-model="acknowledged" :label="$t('I understand')" hide-details color="primary" />
                    </v-card-text>
                    <v-card-actions class="pa-4">
                        <v-btn variant="text" @click="openStep('result')">{{ $t('Back') }}</v-btn>
                        <v-spacer />
                        <v-btn color="primary" variant="flat" :disabled="!acknowledged" :loading="tournamentStore.isLoading" @click="submitReset">
                            {{ $t('Confirm') }}
                        </v-btn>
                    </v-card-actions>
                </v-window-item>
            </v-window>
        </v-card>
    </v-dialog>
</template>

<script setup>
    import { computed, ref, watch } from 'vue';
    import { useTournamentStore } from '@/stores/useTournamentStore';
    import { isUnset } from '@/composables/useSwissRepair';

    const tournamentStore = useTournamentStore();

    const props = defineProps({
        modelValue: { type: Boolean, default: false },
        game: { type: Object, default: null },
        tournament: { type: Object, required: true },
        title: { type: String, default: 'Set game result' },
    });

    const emit = defineEmits(['update:modelValue', 'done']);

    const step = ref('result');
    const acknowledged = ref(false);
    const selectedResultOption = ref(null);
    const player1Outcome = ref('bye');
    const player2Outcome = ref('bye');

    const isKnockout = computed(() => props.tournament?.format === 'knockout');

    const canBreakGame = computed(() => {
        if (!props.game || !isUnset(props.game)) return false;
        if (props.tournament?.format !== 'swiss' || props.tournament?.status !== 'ongoing') return false;
        const rounds = props.tournament.rounds || [];
        if (!rounds.length) return false;
        const latest = rounds.reduce((current, round) => round.round_number > current.round_number ? round : current);
        return props.game.round_id === latest.id;
    });

    const canResetGame = computed(() => {
        if (!props.game || props.game.player2_id == null) return false;
        return props.game.result1 != null || props.game.result2 != null;
    });

    const showMenu = computed(() => !isKnockout.value || canBreakGame.value || canResetGame.value);

    const resultMap = {
        white_wins: { result1: 'win', result2: 'loss' },
        black_wins: { result1: 'loss', result2: 'win' },
        draw: { result1: 'draw', result2: 'draw' },
    };

    watch(() => props.modelValue, (open) => {
        if (!open) return;
        step.value = 'result';
        acknowledged.value = false;
        selectedResultOption.value = initialOption(props.game);
    });

    function initialOption(game) {
        if (!game || game.status === 'cancelled') return null;
        if (game.result1 === 'win') return 'white_wins';
        if (game.result1 === 'loss' && game.result2 === 'win') return 'black_wins';
        if (game.result1 === 'draw') return 'draw';
        return null;
    }

    function playerLabel(player) {
        return player?.formal_name || player?.name || '—';
    }

    function openStep(name) {
        acknowledged.value = false;
        if (name === 'break') {
            player1Outcome.value = 'bye';
            player2Outcome.value = 'bye';
        }
        step.value = name;
    }

    function close() {
        emit('update:modelValue', false);
    }

    function finish() {
        close();
        emit('done');
    }

    async function submitResult() {
        const result = resultMap[selectedResultOption.value];
        if (!result || !props.game) return;
        const res = await tournamentStore.setGameResult(props.game.id, result.result1, result.result2);
        if (res?.game) finish();
    }

    async function submitCancel() {
        if (!acknowledged.value || !props.game) return;
        const res = await tournamentStore.setGameResult(props.game.id, 'loss', 'loss');
        if (res?.game) finish();
    }

    async function submitBreak() {
        if (!acknowledged.value || !props.game) return;
        const res = await tournamentStore.breakGame(props.game.id, player1Outcome.value, player2Outcome.value);
        if (res?.tournament) finish();
    }

    async function submitReset() {
        if (!acknowledged.value || !props.game) return;
        const res = await tournamentStore.resetGame(props.game.id);
        if (res?.game) finish();
    }
</script>
