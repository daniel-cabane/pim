<template>
    <v-card :title="$t('Delete email')">
        <v-card-text>
            <div class="text-h5 py-3 text-grey text-center">
                Coming soon...
            </div>
            <!-- <div>
                {{ $t('Are you sure you want to delete this email') }} ?
            </div>
            <v-checkbox :label="$t('Yes, delete it permanently')" v-model="allowDelete" /> -->
        </v-card-text>
        <v-card-actions>
            <v-spacer />
            <v-btn variant="text" color="primary" :disabled="isLoading" @click="emit('closeDialog')">
                {{ $t('Cancel') }}
            </v-btn>
            <v-btn variant="flat" color="error" :loading="isLoading" @click="emit('deleteEmail', email)"
                :disabled="!allowDelete">
                {{ $t('Delete') }}
            </v-btn>
        </v-card-actions>
    </v-card>
</template>
<script setup>
    import { ref } from "vue";

    const props = defineProps({ email: Object, isLoading: Boolean });
    const emit = defineEmits(['closeDialog', 'deleteEmail']);

    const allowDelete = ref(false);
</script>