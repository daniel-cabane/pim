<template>
    <v-container>
        <v-row>
            <v-col cols="12" sm="6">
                <div class="text-caption text-captionColor">
                    {{ $t('Teacher') }}
                </div>
                <div>
                    {{ workshop.teacher }}
                </div>
            </v-col>
            <v-col cols="12" sm="6">
                <div class="text-caption text-captionColor">
                    {{ $t('Starts on') }}
                </div>
                <div>
                    <v-icon icon="mdi-alert" color="error" size="x-small" v-if="daysUntilStart<0"/>
                    {{ workshop.startDate }} ({{ daysUntilStart < 0 ? $t('PAST') : `${daysUntilStart} ${daysUntilStart > 1 ? $t('days') : $t('day')}` }})
                </div>
            </v-col>
            <v-col cols="12" sm="6">
                <div class="text-caption text-captionColor">
                    {{ $t('Campus') }}
                </div>
                <div>
                    {{ workshop.campus }}
                </div>
            </v-col>
            <v-col cols="12" sm="6">
                <div class="text-caption text-captionColor">
                    {{ $t('Room nb') }}
                </div>
                <div>
                    {{ workshop.details.roomNb }}
                </div>
            </v-col>
        </v-row>
        <div class="text-caption text-captionColor pt-4">
            Applicants
        </div>
        <v-row v-if="workshop.notifyApplicants">
            <v-col cols="6">
                {{ confirmedCount }} {{ $t('confirmed') }}
                <v-checkbox density="compact" label="Notify by email" v-model="workshop.notifyApplicants.confirmed"/>
            </v-col>
            <v-col cols="6">
                {{ workshop.applicants.length - confirmedCount }} {{ $t('unconfirmed') }}
                <v-checkbox density="compact" label="Notify by email" v-model="workshop.notifyApplicants.unconfirmed"/>
            </v-col>
        </v-row>
    </v-container>
</template>
<script setup>
    import { ref, computed } from "vue";

    const props = defineProps({ workshop: Object });

    const daysUntilStart = computed(() => {
        const now = new Date();
        const target = new Date(props.workshop.startDate);
        const timeDiff = target.getTime() - now.getTime();
        
        return Math.floor(timeDiff / (1000 * 60 * 60 * 24));
    });

    const confirmedCount = computed(() => {
        return props.workshop.applicants.filter(applicant => applicant.confirmed).length;
    });
</script>