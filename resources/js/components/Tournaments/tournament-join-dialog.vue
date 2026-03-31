<template>
    <v-dialog v-model="show" width="500">
        <v-card>
            <v-card-title class="bg-primary text-white">
                <v-icon left>mdi-chess-pawn</v-icon>
                {{ $t('Join') }} {{ tournamentName }}
            </v-card-title>
            <v-card-text class="pt-6">
                <p class="mb-6 text-subtitle-2">Please enter your chess rating to join the tournament.</p>
                
                <v-text-field
                    v-model.number="playerRating"
                    label="Chess Rating"
                    type="number"
                    min="1000"
                    max="2800"
                    outlined
                    dense
                    class="mb-4"
                    hint="Use your rating from chess.com or leave at 800 if you don't know"
                    persistent-hint
                />

                <p class="text-caption text-captionColor">
                    Your rating will be recorded with your application and used for pairing in the tournament.
                </p>
            </v-card-text>
            
            <v-card-actions class="pa-4">
                <v-spacer></v-spacer>
                <v-btn 
                    variant="tonal"
                    color="error"
                    @click="show = false"
                    :disabled="isJoining"
                >
                    Cancel
                </v-btn>
                <v-btn 
                    color="primary"
                    variant="flat"
                    @click="confirmJoin"
                    :loading="isJoining"
                    :disabled="!playerRating || playerRating < 200 || playerRating > 2800"
                >
                    {{ $t('Join Tournament') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
<script setup>
    import { ref, computed } from 'vue';

    const props = defineProps({
        modelValue: Boolean,
        tournamentName: String,
        isJoining: Boolean,
    });

    const emit = defineEmits(['update:modelValue', 'confirm']);

    const playerRating = ref(800);

    const show = computed({
        get: () => props.modelValue,
        set: (val) => emit('update:modelValue', val),
    });

    const confirmJoin = () => {
        if (playerRating.value && playerRating.value >= 200 && playerRating.value <= 2800) {
            emit('confirm', playerRating.value);
        }
    };
</script>
<style scoped>
.bg-primary {
    background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
}
</style>
