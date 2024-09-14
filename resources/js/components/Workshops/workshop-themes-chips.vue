<template>
    <span>
        <v-chip 
            class="mr-2 mb-2"
            color="fis" 
            variant="outlined"
            label 
            :size="size"
            v-for="(theme, index) in themeTitles" 
            :key="index"
        >
            {{ theme }}
        </v-chip>
        <v-chip label color="secondary" v-if="themeTitles.length == 0">
            {{ $t('None') }}
        </v-chip>
    </span>
</template>
<script setup>
    const props = defineProps({themeTitles: Array, size: {type: String, default: 'default'}});
</script>