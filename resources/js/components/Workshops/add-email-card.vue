<template>
    <v-card :title="$t('New email')">
        <v-card-text>
            <v-text-field :label="$t('Subject')" variant="outlined" v-model="subject"/>
        </v-card-text>
        <v-card-actions>
            <v-spacer />
            <v-btn color="error" variant="text" :disabled="isLoading" @click="emit('closeDialog')">
                {{ $t('Cancel') }}
            </v-btn>
            <v-btn color="primary" variant="flat" :loading="isLoading" @click="submitEmail">
                {{ $t('Submit') }}
            </v-btn>
        </v-card-actions>
    </v-card>
</template>
<script setup>
    import { ref } from "vue";
    import { useEmailStore } from '@/stores/useEmailStore';
    import { storeToRefs } from 'pinia';

    const props = defineProps({ workshopId: Number });
    const emit = defineEmits(['closeDialog']);

    const EmailStore = useEmailStore();
    const { addWorkshopEmail } = EmailStore;
    const { isLoading } = storeToRefs(EmailStore);

    const subject = ref('');

    const submitEmail = async () => {
        await addWorkshopEmail(props.workshopId, subject.value);
        emit('closeDialog');
    }
</script>