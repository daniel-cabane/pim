<template>
    <v-card :title="$t('Delete survey')">
        <v-card-text>
            <div>
                {{ $t('Are you sure you want to delete this survey') }} ?
            </div>
            <div>
                <b>{{ survey.mainTitle }}</b> ({{ survey.questions.length }} question<span
                    v-if="survey.questions.length > 1">s</span>)
            </div>
        </v-card-text>
        <div style="display:flex;justify-content:flex-end;" class="pa-3">
            <v-btn variant="tonal" class="mr-3" color="primary" :disabled="isLoading" @click="emit('closeDialog')">
                {{ $t('Cancel') }}
            </v-btn>
            <v-btn color="error" :loading="isLoading" @click="emit('deleteSurvey')">
                {{ $t('Delete') }}
            </v-btn>
        </div>
    </v-card>
</template>
<script setup>
    const props = defineProps({ survey: Object, isLoading: Boolean });
    const emit = defineEmits(['closeDialog', 'deleteSurvey']);
</script>