<template>
    <v-card class="mb-6" elevation="2">
        <v-card-title class="bg-blue">
            <v-icon left color="white" class="mr-2">mdi-chart-line</v-icon>
            <span class="text-white">Your Stats</span>
        </v-card-title>
        <v-card-text class="pt-4">
            <v-row>
                <v-col cols="12" sm="6" md="3">
                    <div class="text-center">
                        <div class="text-captionColor text-caption mb-2">{{ $t('Rank') }}</div>
                        <div class="text-h4 font-weight-bold text-primary">
                            {{ userRank || '-' }}
                        </div>
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                    <div class="text-center">
                        <div class="text-captionColor text-caption mb-2">Points</div>
                        <div class="text-h4 font-weight-bold">{{ userStats.points || 0 }}</div>
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                    <div class="text-center">
                        <div class="text-captionColor text-caption mb-2">{{ $t('Record') }}</div>
                        <div class="text-subtitle-1">
                            <span class="text-success font-weight-bold">{{ userStats.wins || 0 }}W</span>
                            <span class="text-warning font-weight-bold mx-2">{{ userStats.draws || 0 }}D</span>
                            <span class="text-error font-weight-bold">{{ userStats.losses || 0 }}L</span>
                        </div>
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                    <div class="text-center">
                        <div class="text-captionColor text-caption mb-2">{{ $t('Win Rate') }}</div>
                        <div class="text-h6 font-weight-bold">
                            {{ winRate }}%
                        </div>
                    </div>
                </v-col>
            </v-row>
        </v-card-text>
    </v-card>
</template>
<script setup>
    import { computed } from 'vue';

    const props = defineProps({
        userStats: Object,
        userRank: Number,
    });

    const winRate = computed(() => {
        const totalGames = (props.userStats.wins || 0) + (props.userStats.draws || 0) + (props.userStats.losses || 0);
        if (totalGames === 0) return 0;
        return Math.round(((props.userStats.wins || 0) / totalGames) * 100);
    });
</script>
<style scoped>
.bg-blue {
    background: linear-gradient(135deg, #42a5f5 0%, #1976d2 100%);
}
</style>
