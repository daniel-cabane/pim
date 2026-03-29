<template>
    <v-container>
        <div class="mb-6">
            <div class="text-h5 text-captionColor">
                {{ $t('Tournaments played') }}
            </div>
            <div class="d-flex flex-wrap gap-2">
                <tournament-card :tournament="tournament" v-for="tournament in tournaments.player"/>
            </div>
        </div>
        <div class="mb-8">
            <div class="text-h5 text-captionColor">
                {{ $t('Tournaments organised') }}
            </div>
            <div class="d-flex flex-wrap gap-2">
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
