<template>
    <v-card width="500" :title="survey.mainTitle">
        <v-card-text>
            {{ $t('Send this survey to students') }} ?
        </v-card-text>
        <v-card-actions>
            <v-spacer />
            <v-btn variant="text" color="error" :disabled="isLoading" @click="emit('closeDialog')">
                {{ $t('Cancel') }}
            </v-btn>
            <v-btn variant="flat" color="primary" :loading="isLoading" @click="emit('sendSurvey')">
                {{ $t('Send') }}
            </v-btn>
        </v-card-actions>
    </v-card>
</template>
<script setup>
    const props = defineProps({ survey: Object, isLoading: Boolean });
    const emit = defineEmits(['closeDialog', 'sendSurvey']);
</script>