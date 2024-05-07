<template>
    <v-card-title class="d-flex justify-space-between align-center pb-0">
        <span>{{ workshop.title }}</span>
        <v-chip size="small" :color="workshop.details.campus == 'BPR' ? 'blue' : 'red'">{{ workshop.details.campus }}</v-chip>
    </v-card-title>
    <v-card-subtitle class="font-italic">
        {{ $t('By') }} {{ workshop.organiser.name }}
    </v-card-subtitle>
</template>
<script setup>
    const props = defineProps({ workshop: Object })
</script>