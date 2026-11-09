<template>
    <v-dialog :model-value="pairOpen" max-width="450" @update:model-value="emit('update:pairOpen', $event)">
        <v-card :title="$t('Pair players')">
            <v-card-text>
                <p class="mb-4">{{ $t('White is chosen at random') }}</p>
                <v-select
                    v-model="playerA"
                    :items="options"
                    item-title="title"
                    item-value="value"
                    :label="$t('Players')"
                    class="mb-3"
                    hide-details
                />
                <v-select
                    v-model="playerB"
                    :items="options"
                    item-title="title"
                    item-value="value"
                    :label="$t('Players')"
                    hide-details
                />
                <v-alert
                    v-if="rematch"
                    type="warning"
                    variant="tonal"
                    density="compact"
                    class="mt-4"
                >
                    {{ $t('These players have already played each other.') }}
                </v-alert>
            </v-card-text>
            <v-card-actions class="pa-4">
                <v-spacer />
                <v-btn variant="tonal" color="error" @click="emit('update:pairOpen', false)">{{ $t('Close') }}</v-btn>
                <v-btn color="primary" variant="flat" :disabled="!canPair" :loading="tournamentStore.isLoading" @click="submitPair">
                    {{ $t('Confirm') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>

    <v-dialog :model-value="!!unpairedGame" max-width="450" @update:model-value="onUnpairedDialog">
        <v-card v-if="unpairedGame" :title="playerLabel(unpairedGame.player1)">
            <v-card-text>
                <v-btn-toggle v-model="kind" mandatory divided color="primary" class="d-flex">
                    <v-btn value="bye" class="flex-grow-1">
                        {{ $t('Bye') }}
                        <span class="text-caption ml-1">1</span>
                    </v-btn>
                    <v-btn value="absent" class="flex-grow-1">
                        {{ $t('Absent') }}
                        <span class="text-caption ml-1">0</span>
                    </v-btn>
                </v-btn-toggle>
            </v-card-text>
            <v-card-actions class="pa-4">
                <v-spacer />
                <v-btn variant="tonal" color="error" @click="emit('update:unpairedGame', null)">{{ $t('Close') }}</v-btn>
                <v-btn color="primary" variant="flat" :loading="tournamentStore.isLoading" @click="saveKind">
                    {{ $t('Confirm') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script setup>
    import { computed, ref, watch } from 'vue';
    import { useI18n } from 'vue-i18n';
    import { useTournamentStore } from '@/stores/useTournamentStore';
    import { unpairedPlayers, havePlayed } from '@/composables/useSwissRepair';

    const { t } = useI18n();

    const tournamentStore = useTournamentStore();

    const props = defineProps({
        tournament: { type: Object, required: true },
        round: { type: Object, default: null },
        pairOpen: { type: Boolean, default: false },
        unpairedGame: { type: Object, default: null },
    });

    const emit = defineEmits(['update:pairOpen', 'update:unpairedGame']);

    const playerA = ref(null);
    const playerB = ref(null);
    const kind = ref('bye');

    const options = computed(() => {
        return unpairedPlayers(props.round, props.tournament?.players).map((game) => ({
            title: `${playerLabel(game.player1)} — ${game.status === 'absent' ? t('Absent') : t('Bye')}`,
            value: Number(game.player1_id),
        }));
    });

    const canPair = computed(() => {
        return playerA.value != null && playerB.value != null && Number(playerA.value) !== Number(playerB.value);
    });

    const rematch = computed(() => havePlayed(props.tournament, playerA.value, playerB.value));

    watch(() => props.pairOpen, (open) => {
        if (open) {
            playerA.value = null;
            playerB.value = null;
        }
    });

    watch(() => props.unpairedGame, (game) => {
        kind.value = game?.status === 'absent' ? 'absent' : 'bye';
    });

    function onUnpairedDialog(open) {
        if (!open) {
            emit('update:unpairedGame', null);
        }
    }

    function playerLabel(player) {
        return player?.formal_name || player?.name || '—';
    }

    async function submitPair() {
        if (!canPair.value || !props.round) {
            return;
        }
        const res = await tournamentStore.pairByes(props.tournament.slug, props.round.id, playerA.value, playerB.value);
        if (res?.tournament) {
            emit('update:pairOpen', false);
        }
    }

    async function saveKind() {
        if (!props.unpairedGame) {
            return;
        }
        const res = await tournamentStore.setUnpairedKind(props.unpairedGame.id, kind.value);
        if (res?.game) {
            emit('update:unpairedGame', null);
        }
    }
</script>
