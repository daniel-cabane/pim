<template>
    <v-card>
        <v-card-text>
            <p class="text-subtitle-1 font-weight-bold mb-4">Tournament Rounds</p>
            <v-list v-if="tournament.rounds && tournament.rounds.length > 0">
                <v-list-item 
                    v-for="(round, index) in tournament.rounds" 
                    :key="round.id"
                >
                    <template v-slot:prepend>
                        <v-chip 
                            :color="getRoundStatusColor(round.status)"
                            text-color="white"
                            size="small"
                        >
                            Round {{ index + 1 }}
                        </v-chip>
                    </template>
                    <v-list-item-title>Status: {{ round.status }}</v-list-item-title>
                    <v-list-item-subtitle>{{ round.games?.length || 0 }} games</v-list-item-subtitle>
                </v-list-item>
            </v-list>
            <div v-else class="text-center py-8">
                <p class="text-captionColor">No rounds yet</p>
            </div>
        </v-card-text>
    </v-card>
</template>

<script setup>
    defineProps({
        tournament: {
            type: Object,
            required: true
        }
    });

    const getRoundStatusColor = (status) => {
        const colorMap = {
            'draft': 'captionColor',
            'in_progress': 'warning',
            'completed': 'success'
        };
        return colorMap[status] || 'captionColor';
    };
</script>