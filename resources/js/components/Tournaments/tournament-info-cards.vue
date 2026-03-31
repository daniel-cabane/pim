<template>
    <v-row class="mb-6">
        <v-col cols="12" sm="6" md="3">
            <v-card class="h-100" elevation="2">
                <v-card-text>
                    <div class="text-center">
                        <v-icon size="40" color="info" class="mb-2">mdi-tournament</v-icon>
                        <div class="text-h6 font-weight-bold text-capitalize">{{ $t(pairingSystem) }}</div>
                        <div class="text-captionColor text-caption mb-1">{{ $t('Pairing') }}</div>
                    </div>
                </v-card-text>
            </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
            <v-card class="h-100" elevation="2">
                <v-card-text>
                    <div class="text-center">
                        <v-icon size="40" color="primary" class="mb-2">mdi-account-multiple</v-icon>
                        <div class="text-h6 font-weight-bold">{{ tournament.players_count }}</div>
                        <div class="text-captionColor text-caption mb-1">{{ $t('Players') }}</div>
                    </div>
                </v-card-text>
            </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
            <v-card class="h-100" elevation="2">
                <v-card-text>
                    <div class="text-center">
                        <v-icon size="40" color="warning" class="mb-2">mdi-table</v-icon>
                        <div class="text-h6 font-weight-bold">{{ tournament.rounds_count }}</div>
                        <div class="text-captionColor text-caption mb-1">{{ $t('Rounds')}}</div>
                    </div>
                </v-card-text>
            </v-card>
        </v-col>
        <v-col cols="12" sm="6" md="3">
            <v-card class="h-100" elevation="2">
                <v-card-text>
                    <div class="text-center">
                        <v-icon 
                            size="40" 
                            :color="statusColor" 
                            class="mb-2"
                        >{{ statusIcon }}</v-icon>
                        <div class="text-h6 font-weight-bold text-capitalize">{{ $t(tournament.status) }}</div>
                        <div class="text-captionColor text-caption mb-1">{{ $t('Status') }}</div>
                    </div>
                </v-card-text>
            </v-card>
        </v-col>
    </v-row>
</template>
<script setup>
    import { computed } from 'vue';

    const props = defineProps({ tournament: Object });

    const statusIcon = computed(() => {
        const iconMap = {
            'draft': 'mdi-pencil',
            'preparation': 'mdi-play-speed',
            'ongoing': 'mdi-play-circle',
            'completed': 'mdi-check-circle'
        };
        return iconMap[props.tournament?.status] || 'mdi-help-circle';
    });

    const statusColor = computed(() => {
        const colorMap = {
            'draft': 'captionColor',
            'ongoing': 'success',
            'completed': 'secondary'
        };
        return colorMap[props.tournament?.status] || 'captionColor';
    });

    const pairingSystem = computed(() => {
        return props.tournament.format == 'round_robin' ? 'Round robin' : props.tournament.format
    });
</script>
