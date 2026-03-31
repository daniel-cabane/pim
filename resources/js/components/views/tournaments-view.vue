<template>
    <v-container>
        <div class="mb-6">
            <div class="text-h5 text-captionColor mb-2">
                {{ $t('Tournaments played') }}
            </div>
            <div class="d-flex flex-wrap ga-4" v-if="tournaments.player.length">
                <tournament-card :tournament="tournament" v-for="tournament in tournaments.player"/>
            </div>
            <div class="d-flex justify-center align-center text-surface text-h5" v-else>
                Nothing yet
                <v-icon icon="mdi-robot-dead-outline" class="ml-2"/>
            </div>
        </div>
        <div class="mb-8" v-if="tournaments.organiser.length">
            <div class="text-h5 text-captionColor mb-2">
                {{ $t('Tournaments organised') }}
            </div>
            <div class="d-flex flex-wrap ga-4">
                <tournament-card :tournament="tournament" v-for="tournament in tournaments.organiser"/>
            </div>
        </div>
    </v-container>
</template>

<script setup>
    import { useAuthStore } from '@/stores/useAuthStore';
    import { computed } from 'vue';

    const authStore = useAuthStore();
    
    const tournaments = computed(() => {
        return authStore.user?.my_tournaments;
    });
</script>
