<template>
    <v-card class="mb-6" elevation="2">
        <v-card-title class="bg-primary">
            <v-icon left color="white" class="mr-2">mdi-trophy</v-icon>
            <span class="text-white">{{ $t('Leaderboard') }}</span>
        </v-card-title>
        <v-card-text class="pt-4">
            <v-table v-if="standings.length" density="compact">
                <thead>
                    <tr>
                        <th class="text-left">#</th>
                        <th class="text-left">{{ $t('Player') }}</th>
                        <th class="text-center">Points</th>
                        <th class="text-center">{{ $t('Wins') }}</th>
                        <th class="text-center">{{ $t('Draws') }}</th>
                        <th class="text-center">{{ $t('Losses') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr 
                        v-for="(player, index) in standings" 
                        :key="player.id"
                        :class="{ 'bg-light-blue': isCurrentUserPlayer(player.id) }"
                    >
                        <td>
                            <v-chip 
                                v-if="index === 0"
                                color="gold"
                                text-color="black"
                                size="small"
                                label
                            >🥇 {{ index + 1 }}</v-chip>
                            <v-chip 
                                v-else-if="index === 1"
                                color="silver"
                                text-color="black"
                                size="small"
                                label
                            >🥈 {{ index + 1 }}</v-chip>
                            <v-chip 
                                v-else-if="index === 2"
                                color="bronze"
                                text-color="black"
                                size="small"
                                label
                            >🥉 {{ index + 1 }}</v-chip>
                            <span v-else>{{ index + 1 }}</span>
                        </td>
                        <td class="d-flex align-center">
                            <span class="font-weight-bold">
                                {{ playerName(player) }}
                            </span>
                            <span v-if="isCurrentUserPlayer(player.id)" class="text-caption text-primary ml-2">
                                (You)
                            </span>
                        </td>
                        <td class="text-center font-weight-bold">{{ player.points }}</td>
                        <td class="text-center">
                            <v-chip size="small" color="success" text-color="white">{{ player.wins }}</v-chip>
                        </td>
                        <td class="text-center">
                            <v-chip size="small" color="warning" text-color="white">{{ player.draws }}</v-chip>
                        </td>
                        <td class="text-center">
                            <v-chip size="small" color="error" text-color="white">{{ player.losses }}</v-chip>
                        </td>
                    </tr>
                </tbody>
            </v-table>
            <div v-else class="text-center py-8">
                <v-icon size="64" color="captionColor" class="mb-2">mdi-table</v-icon>
                <p class="text-captionColor">{{ $t('No standings available yet') }}</p>
            </div>
        </v-card-text>
    </v-card>
</template>
<script setup>
    import { useAuthStore } from '@/stores/useAuthStore';

    const authStore = useAuthStore();

    const props = defineProps({ standings: Array });

    const playerName = (player) => {
        return player?.formal_name || player?.name || '-';
    };

    const isCurrentUserPlayer = (playerId) => {
        return authStore.user && authStore.user.id === playerId;
    };
</script>
<style scoped>
.bg-light-blue {
    background-color: rgba(25, 118, 210, 0.05) !important;
}

.bg-primary {
    background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
}
</style>
