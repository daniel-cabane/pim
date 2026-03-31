<template>
    <v-card width="320" class="pa-3" @click="seeTournament">
        <div class="d-flex gap-2 justify-space-between mb-2">
            <div>
                <div class="pimSubtitleFont" style="font-size:28px;">
                    {{ tournament.name }}
                </div>
                <div class="text-caption text-captionColor pl-1">
                    {{ tournament.description }}
                </div>
            </div>
            <v-icon color="opposite" size="x-large" icon="mdi-checkerboard" style="margin-top:5px"/>
        </div>
        <div class="d-flex justify-space-between">
            <div>
                <div class="text-center">
                    <v-icon size="40" color="primary" class="mb-2">mdi-account-multiple</v-icon>
                    <div class="text-h6 font-weight-bold">{{ tournament.players_count }}</div>
                    <div class="text-captionColor text-caption mb-1">{{ $t('Players')}} </div>
                </div>
            </div>
            <div>
                <div class="text-center">
                    <v-icon size="40" color="warning" class="mb-2">mdi-table</v-icon>
                    <div class="text-h6 font-weight-bold">{{ tournament.rounds_count }}</div>
                    <div class="text-captionColor text-caption mb-1">{{ $t('Rounds')}} </div>
                </div>
            </div>
            <div>
                <div class="text-center">
                    <v-icon size="40" color="opposite" class="mb-2">mdi-chess-pawn</v-icon>
                    <div class="text-h6 font-weight-bold">{{ totalGames }}</div>
                    <div class="text-captionColor text-caption mb-1">{{ $t('Games')}} </div>
                </div>
            </div>
            <div>
                <div class="text-center">
                    <v-icon size="40" color="info" class="mb-2">mdi-tournament</v-icon>
                    <div class="text-h6 font-weight-bold text-capitalize">{{ $t(pairingSystem) }}</div>
                    <div class="text-captionColor text-caption mb-1">{{ $t('Pairing')}} </div>
                </div>
            </div>
        </div>
    </v-card>
</template>

<script setup>
    import { useRouter } from 'vue-router';
    import { computed } from 'vue';

    const props = defineProps({tournament: Object});
    const router = useRouter();

    const seeTournament = () => {
        router.push(`/tournaments/${props.tournament.slug}`);
    }

    const totalGames = computed(() => {
        return props.tournament?.rounds?.reduce((sum, round) => sum + (round.games?.length || 0), 0) || 0;
    });

    const pairingSystem = computed(() => {
        return props.tournament.format == 'round_robin' ? 'Round robin' : props.tournament.format
    });
</script>

<style scoped>
    .twolines {
        overflow: hidden;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        line-clamp: 2;
        -webkit-line-clamp: 2; 
        white-space: pre-wrap;
        min-height: 60px;
        max-height: 60px;
    }
    .threelines {
        overflow: hidden;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        line-clamp: 3;
        -webkit-line-clamp: 3;
        white-space: pre-wrap;
        min-height: 75px;
        max-height: 75px;
    }
</style>
