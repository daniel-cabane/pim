<template>
    <v-card class="pa-2">
        <v-card-text>
            <v-row>
                <v-col cols="12" sm="6" md="3">
                    <div class="text-center">
                        <v-icon size="40" color="primary" class="mb-2">mdi-account-multiple</v-icon>
                        <div class="text-captionColor text-caption mb-1">{{ $t('Total Players') }}</div>
                        <div class="text-h6 font-weight-bold">{{ tournament.players_count }}</div>
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                    <div class="text-center">
                        <v-icon size="40" color="warning" class="mb-2">mdi-table</v-icon>
                        <div class="text-captionColor text-caption mb-1">{{ $t('Total Rounds') }}</div>
                        <div class="text-h6 font-weight-bold">{{ tournament.rounds_count }}</div>
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                    <div class="text-center">
                        <v-icon size="40" color="opposite" class="mb-2">mdi-checkerboard</v-icon>
                        <div class="text-captionColor text-caption mb-1">{{ $t('Total Games') }}</div>
                        <div class="text-h6 font-weight-bold">{{ totalGames }}</div>
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                    <div class="text-center">
                        <v-icon size="40" color="info" class="mb-2">mdi-tournament</v-icon>
                        <div class="text-captionColor text-caption mb-1">{{ $t('Pairing system') }}</div>
                        <div class="text-h6 font-weight-bold text-capitalize">{{ $t(pairingSystem) }}</div>
                    </div>
                </v-col>
            </v-row>
            <v-row class="mt-4">
                <v-dialog v-model="statusDialog" max-width="500">
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn 
                            block 
                            color="primary" 
                            size="x-large" 
                            v-bind="activatorProps"
                            v-if="nextStatuses.length"
                        >
                            {{ $t('Update status') }}
                            <v-chip class="ml-3" label size="small" color="white" text-color="primary">{{ $t(tournament.status) }}</v-chip>
                        </v-btn>
                    </template>
                    <v-card :title="$t('Update status')">
                        <v-card-text>
                            <v-radio-group v-model="selectedStatus">
                                <v-radio 
                                    v-for="s in nextStatuses" 
                                    :key="s" 
                                    :label="$t(s)" 
                                    :value="s"
                                    class="text-capitalize"
                                />
                            </v-radio-group>
                            <v-alert 
                                v-if="selectedStatus === 'ongoing'" 
                                type="warning" 
                                variant="tonal" 
                                class="mb-3"
                            >
                                {{ $t('This will generate the first round pairings and start the tournament') }}
                            </v-alert>
                            <v-alert 
                                v-if="selectedStatus === 'completed'" 
                                type="warning" 
                                variant="tonal" 
                                class="mb-3"
                            >
                                {{ $t('This will end the tournament. This action cannot be undone') }}
                            </v-alert>
                            <v-checkbox 
                                v-if="requiresConfirmation" 
                                v-model="confirmed" 
                                :label="$t('Yes, I am sure')" 
                                color="primary"
                            />
                        </v-card-text>
                        <v-card-actions class="pa-4">
                            <v-spacer />
                            <v-btn variant="tonal" color="error" :disabled="tournamentStore.isLoading" @click="statusDialog = false">
                                {{ $t('Cancel') }}
                            </v-btn>
                            <v-btn 
                                color="primary" 
                                variant="flat"
                                :loading="tournamentStore.isLoading" 
                                :disabled="!selectedStatus || (requiresConfirmation && !confirmed)"
                                @click="handleUpdateStatus"
                            >
                                {{ $t('Confirm') }}
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
            </v-row>
        </v-card-text>
    </v-card>
</template>

<script setup>
    import { ref, computed, watch } from 'vue';
    import { useTournamentStore } from '@/stores/useTournamentStore';

    const tournamentStore = useTournamentStore();

    const props = defineProps({
        tournament: {
            type: Object,
            required: true
        }
    });

    const statusDialog = ref(false);
    const selectedStatus = ref(null);
    const confirmed = ref(false);

    const totalGames = computed(() => {
        return props.tournament?.rounds?.reduce((sum, round) => sum + (round.games?.length || 0), 0) || 0;
    });

    const nextStatuses = computed(() => {
        const transitions = {
            'draft': ['preparation'],
            'preparation': ['draft', 'ongoing'],
            'ongoing': ['completed'],
        };
        return transitions[props.tournament?.status] || [];
    });

    const requiresConfirmation = computed(() => {
        return selectedStatus.value === 'ongoing' || selectedStatus.value === 'completed';
    });

    watch(statusDialog, (val) => {
        if (!val) {
            selectedStatus.value = null;
            confirmed.value = false;
        }
    });

    watch(selectedStatus, () => {
        confirmed.value = false;
    });

    const handleUpdateStatus = async () => {
        await tournamentStore.updateTournamentStatus(props.tournament.slug, selectedStatus.value);
        statusDialog.value = false;
    };

    const pairingSystem = computed(() => {
        return props.tournament.format == 'round_robin' ? 'Round robin' : props.tournament.format
    });
</script>