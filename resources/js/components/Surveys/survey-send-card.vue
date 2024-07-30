<template>
    <v-card width="500" :title="survey.mainTitle">
        <v-card-text>
            {{ $t('Open this survey') }} ?
        </v-card-text>
        <v-card-actions>
            <v-spacer />
            <v-btn variant="text" color="error" :disabled="isLoading" @click="emit('closeDialog')">
                {{ $t('Cancel') }}
            </v-btn>
            <v-btn variant="flat" color="success" :loading="isLoading" @click="emit('sendSurvey')">
                {{ $t('Open') }}
            </v-btn>
        </v-card-actions>
    </v-card>
</template>
<script setup>
    const props = defineProps({ survey: Object, isLoading: Boolean });
    const emit = defineEmits(['closeDialog', 'sendSurvey']);
</script>