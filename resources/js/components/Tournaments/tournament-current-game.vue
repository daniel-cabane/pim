<template>
    <v-card class="mb-6" elevation="2" v-if="currentGame">
        <v-card-title class="bg-primary">
            <v-icon left color="white" class="mr-2">mdi-chess-king</v-icon>
            <span class="text-white">{{ $t('Your current game') }}</span>
        </v-card-title>
        <v-card-text class="pt-4">
            <v-row align="center" justify="center">
                <v-col cols="12" sm="2" class="text-center">
                    <v-avatar :color="isPlayer1 ? 'white' : 'black'" size="48" class="mb-2 elevation-2">
                        <v-icon color="grey">mdi-account</v-icon>
                    </v-avatar>
                    <div class="text-h6 font-weight-bold">{{ currentPlayerName }}</div>
                    <div class="text-caption text-captionColor">{{ isPlayer1 ? $t('White') : $t('Black') }}</div>
                </v-col>
                <v-col cols="12" sm="4" class="text-center">
                    <v-select
                        v-model="selectedResult"
                        :items="resultOptions"
                        item-title="label"
                        item-value="value"
                        :label="$t('Game result')"
                        variant="outlined"
                        density="comfortable"
                        :disabled="currentGame.status === 'completed' || tournamentStore.isLoading"
                        @update:model-value="confirmResult"
                    />
                    <v-chip v-if="currentGame.status === 'conflicted'" color="error" label size="small" class="mt-n2">
                        <v-icon start size="small">mdi-alert</v-icon>
                        {{ $t('Results conflict') }}
                    </v-chip>
                </v-col>
                <v-col cols="12" sm="2" class="text-center">
                    <v-avatar :color="isPlayer1 ? 'black' : 'white'" size="48" class="mb-2 elevation-2">
                        <v-icon color="grey">mdi-account</v-icon>
                    </v-avatar>
                    <div class="text-h6 font-weight-bold">{{ opponentName }}</div>
                    <div class="text-caption text-captionColor">{{ isPlayer1 ? $t('Black') : $t('White') }}</div>
                </v-col>
                <v-col cols="12" sm="2" class="text-center">
                    <v-chip 
                        :color="statusColor" 
                        label 
                        size="large"
                    >
                        <v-icon start>{{ statusIcon }}</v-icon>
                        {{ $t(currentGame.status) }}
                    </v-chip>
                </v-col>
            </v-row>
        </v-card-text>

        <v-dialog v-model="confirmDialog" max-width="400">
            <v-card :title="$t('Confirm result')">
                <v-card-text>
                    {{ confirmMessage }}
                </v-card-text>
                <v-card-actions class="pa-4">
                    <v-spacer />
                    <v-btn variant="tonal" color="error" @click="cancelResult">{{ $t('Cancel') }}</v-btn>
                    <v-btn color="primary" variant="flat" :loading="tournamentStore.isLoading" @click="submitResult">{{ $t('Confirm') }}</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-card>
</template>

<script setup>
    import { ref, computed, watch } from 'vue';
    import { useI18n } from 'vue-i18n';
    import { useAuthStore } from '@/stores/useAuthStore';
    import { useTournamentStore } from '@/stores/useTournamentStore';

    const { t } = useI18n();
    const authStore = useAuthStore();
    const tournamentStore = useTournamentStore();

    const props = defineProps({
        tournament: { type: Object, required: true }
    });

    const emit = defineEmits(['resultReported']);

    const confirmDialog = ref(false);
    const selectedResult = ref(null);
    const pendingResult = ref(null);

    const currentRound = computed(() => {
        if (!props.tournament?.rounds?.length) return null;
        if (props.tournament.format === 'round_robin') {
            const sorted = [...props.tournament.rounds].sort((a, b) => a.round_number - b.round_number);
            return sorted.find(r => !r.games?.every(g => g.status === 'completed')) || sorted[sorted.length - 1];
        }
        return [...props.tournament.rounds].sort((a, b) => b.round_number - a.round_number)[0];
    });

    const currentGame = computed(() => {
        if (!currentRound.value?.games) return null;
        return currentRound.value.games.find(g => 
            g.player1_id === authStore.user?.id || g.player2_id === authStore.user?.id
        );
    });

    const isPlayer1 = computed(() => {
        return currentGame.value?.player1_id === authStore.user?.id;
    });

    const currentPlayer = computed(() => {
        return props.tournament?.players?.find(player => player.id === authStore.user?.id) || authStore.user || null;
    });

    const playerName = (player, fallback = '') => {
        return player?.formal_name || player?.name || fallback;
    };

    const currentPlayerName = computed(() => {
        return playerName(currentPlayer.value);
    });

    const opponentName = computed(() => {
        if (!currentGame.value) return '';
        if (isPlayer1.value) {
            return playerName(currentGame.value.player2, t('Bye'));
        }
        return playerName(currentGame.value.player1, t('Unknown'));
    });

    const isKnockout = computed(() => props.tournament?.format === 'knockout');

    const resultOptions = computed(() => {
        const options = [
            { label: t('pending'), value: null },
            { label: t('Win'), value: 'win' },
            { label: t('Loss'), value: 'loss' },
        ];
        if (!isKnockout.value) {
            options.push({ label: t('Draw'), value: 'draw' });
        }
        return options;
    });

    const statusColor = computed(() => {
        const map = { 'pending': 'warning', 'conflicted': 'error', 'completed': 'success' };
        return map[currentGame.value?.status] || 'warning';
    });

    const statusIcon = computed(() => {
        const map = { 'pending': 'mdi-clock-outline', 'conflicted': 'mdi-alert-circle', 'completed': 'mdi-check-circle' };
        return map[currentGame.value?.status] || 'mdi-clock-outline';
    });

    const confirmMessage = computed(() => {
        const labels = { win: t('Win'), loss: t('Loss'), draw: t('Draw') };
        return t('Are you sure you want to report this game as a') + ' ' + (labels[pendingResult.value] || '') + '?';
    });

    // The result for the current user
    const myResult = computed(() => {
        if (!currentGame.value) return null;
        return isPlayer1.value ? currentGame.value.result1 : currentGame.value.result2;
    });

    // Set initial value based on existing result
    watch(myResult, (val) => {
        selectedResult.value = val || null;
    }, { immediate: true });

    const confirmResult = (val) => {
        if (val === null) return;
        pendingResult.value = val;
        confirmDialog.value = true;
    };

    const cancelResult = () => {
        confirmDialog.value = false;
        selectedResult.value = myResult.value || null;
        pendingResult.value = null;
    };

    const submitResult = async () => {
        await tournamentStore.reportGameResult(currentGame.value.id, pendingResult.value);
        confirmDialog.value = false;
        pendingResult.value = null;
        emit('resultReported');
    };
</script>
