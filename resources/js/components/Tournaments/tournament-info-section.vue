<template>
    <v-card elevation="2">
        <v-card-title class="bg-secondary">
            <v-icon left color="white" class="mr-2">mdi-information</v-icon>
            <span class="text-white">{{ $t('Tournament Info') }}</span>
        </v-card-title>
        <v-card-text class="pt-4">
            <v-row>
                <v-col cols="12" sm="6">
                    <div class="mb-4">
                        <label class="text-captionColor text-caption">{{ $t('Organizer') }}</label>
                        <p class="text-body1 font-weight-bold">{{ organizer?.name || 'Unknown' }}</p>
                    </div>
                </v-col>
                <v-col cols="12" sm="6">
                    <div class="mb-4">
                        <label class="text-captionColor text-caption">{{ $t('Created') }}</label>
                        <p class="text-body1">{{ formatDate(tournament.created_at) }}</p>
                    </div>
                </v-col>
                <v-col cols="12" sm="6" v-if="tournament.started_at">
                    <div class="mb-4">
                        <label class="text-captionColor text-caption">Started</label>
                        <p class="text-body1">{{ formatDate(tournament.started_at) }}</p>
                    </div>
                </v-col>
                <v-col cols="12" sm="6" v-if="tournament.ended_at">
                    <div class="mb-4">
                        <label class="text-captionColor text-caption">Ended</label>
                        <p class="text-body1">{{ formatDate(tournament.ended_at) }}</p>
                    </div>
                </v-col>
            </v-row>
        </v-card-text>
    </v-card>
</template>
<script setup>
    const props = defineProps({
        tournament: Object,
        organizer: Object,
    });

    const formatDate = (date) => {
        if (!date) return '-';
        return new Date(date).toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    };
</script>
<style scoped>
.bg-secondary {
    background: linear-gradient(135deg, #7c4dff 0%, #512da8 100%);
}
</style>
