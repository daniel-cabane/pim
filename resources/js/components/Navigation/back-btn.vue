<template>
    <v-btn 
        variant="tonal" 
        size="small"
        rounded="xl"
        :disabled="loading"
        prepend-icon="mdi-arrow-left-bold-circle-outline" 
        @click="goBack"
    >
        {{ $t('Back') }}
    </v-btn>
</template>
<script setup>
    import { useRouter } from 'vue-router';

    const router = useRouter();
    const goBack = () => router.go(-1);

    const props = defineProps({ loading: Boolean });
</script>