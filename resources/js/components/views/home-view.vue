<template>
    <v-container>
        {{ t('home') }}
    </v-container>
</template>
<script setup>
    import { useI18n } from 'vue-i18n';
    const { t } = useI18n();    
</script>