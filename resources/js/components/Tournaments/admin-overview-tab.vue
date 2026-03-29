<template>
    <v-card>
        <v-card-text>
            <p class="text-subtitle-1 font-weight-bold mb-4">Tournament Overview</p>
            <v-row>
                <v-col cols="12" sm="6" md="3">
                    <div class="text-center">
                        <v-icon size="40" color="primary" class="mb-2">mdi-account-multiple</v-icon>
                        <div class="text-captionColor text-caption mb-1">Total Players</div>
                        <div class="text-h6 font-weight-bold">{{ tournament.players_count }}</div>
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                    <div class="text-center">
                        <v-icon size="40" color="warning" class="mb-2">mdi-play-circle</v-icon>
                        <div class="text-captionColor text-caption mb-1">Total Rounds</div>
                        <div class="text-h6 font-weight-bold">{{ tournament.rounds_count }}</div>
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                    <div class="text-center">
                        <v-icon size="40" color="opposite" class="mb-2">mdi-checkerboard</v-icon>
                        <div class="text-captionColor text-caption mb-1">Total Games</div>
                        <div class="text-h6 font-weight-bold">{{ totalGames }}</div>
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                    <div class="text-center">
                        <v-icon size="40" color="info" class="mb-2">mdi-tournament</v-icon>
                        <div class="text-captionColor text-caption mb-1">Format</div>
                        <div class="text-h6 font-weight-bold text-capitalize">{{ tournament.format }}</div>
                    </div>
                </v-col>
            </v-row>
        </v-card-text>
    </v-card>
</template>

<script setup>
    import { computed } from 'vue';

    const props = defineProps({
        tournament: {
            type: Object,
            required: true
        }
    });

    const totalGames = computed(() => {
        return props.tournament?.rounds?.reduce((sum, round) => sum + (round.games?.length || 0), 0) || 0;
    });
</script>