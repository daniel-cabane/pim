<template>
    <div class="d-flex justify-space-between align-start mb-6">
        <div>
            <div class="pimSubtitleFont" style="font-size: 56px;">
                {{ tournament.name }}
            </div>
            <div class="text-captionColor text-subtitle-1 ml-2">
                {{ tournament.description }}
            </div>
        </div>
        <div class="d-flex gap-3 align-center mt-4">
            <!-- Player Label -->
             <div v-if="authStore.user && isPlayerInTournament">
                <v-chip 
                     color="error"
                     text-color="white"
                     size="x-large"
                     label
                     class="text-h6 font-weight-bold px-4"
                     prepend-icon="mdi-cancel"
                     v-if="isPlayerBanned"
                 >
                     {{ $t('BANNED') }}
                 </v-chip>

                 <v-chip 
                     color="success"
                     text-color="white"
                     size="x-large"
                     label
                     class="text-h6 font-weight-bold px-4"
                     prepend-icon="mdi-chess-pawn"
                     v-else
                 >
                     {{ $t('Player') }}
                 </v-chip>
             </div>

            <!-- Join Button -->
            <v-btn 
                v-else-if="authStore.user && !isPlayerInTournament && tournament.status === 'preparation'"
                color="primary"
                size="large"
                @click="emit('joinTournament')"
            >
                <v-icon left>mdi-plus</v-icon>
                {{ $t('Join Tournament') }}
            </v-btn>

            <!-- Admin Button -->
            <v-btn 
                v-if="isOrganizer" 
                :to="`/tournaments/${tournament.slug}/admin`"
                color="success"
                icon
                size="large"
                class="ml-4"
            >
                <v-icon>mdi-cog</v-icon>
                <v-tooltip activator="parent">{{ $t('Manage Tournament') }}</v-tooltip>
            </v-btn>
        </div>
    </div>
</template>
<script setup>
    import { useAuthStore } from '@/stores/useAuthStore';

    const authStore = useAuthStore();

    const props = defineProps({
        tournament: Object,
        isPlayerInTournament: Boolean,
        isPlayerBanned: Boolean,
        isOrganizer: Boolean,
    });

    const emit = defineEmits(['joinTournament']);
</script>
