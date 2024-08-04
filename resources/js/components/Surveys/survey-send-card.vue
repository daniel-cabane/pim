<template>
    <v-card width="500" :title="survey.mainTitle">
        <v-card-text>
            <div v-if="survey.nbRecipients == 0">
                {{ $t('This survey has no recipient. It cannot be opened') }}
            </div>
            <div v-else>
                {{ $t('Do you want to open this survey for') }}
                {{ survey.nbRecipients }} {{ $t('Recipient') }}{{ survey.nbRecipients>1 ? 's' : '' }} ?
                <v-checkbox v-model="sendEmail" :label="$t('Also notify recipients by email')"/>
            </div>
        </v-card-text>
        <v-card-actions>
            <v-spacer />
            <v-btn variant="text" color="error" style="width:150px;" :disabled="isLoading" @click="emit('closeDialog')">
                {{ $t('Cancel') }}
            </v-btn>
            <v-btn variant="flat" color="primary" style="width:150px;" :disabled="survey.nbRecipients == 0" :loading="isLoading" @click="emit('sendSurvey', sendEmail)">
                {{ $t('Open') }}
            </v-btn>
        </v-card-actions>
    </v-card>
</template>
<script setup>
    import { ref } from "vue";
    const props = defineProps({ survey: Object, isLoading: Boolean });
    const emit = defineEmits(['closeDialog', 'sendSurvey']);

    const sendEmail = ref(false);
</script>