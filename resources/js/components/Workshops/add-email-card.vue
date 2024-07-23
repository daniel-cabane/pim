<template>
    <v-card :title="$t('New email')">
        <v-card-text>
            <div class="d-flex justify-center text-subheading text-grey">
                Coming soon...
            </div>
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
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    import { storeToRefs } from 'pinia';

    const emit = defineEmits(['closeDialog']);

    const name = ref('');

    const WorkshopStore = useWorkshopStore();
    // const { newMail } = WorkshopStore;
    const { isLoading } = storeToRefs(WorkshopStore);
    const submitEmail = async () => {
        emit('closeDialog');
    }
</script>