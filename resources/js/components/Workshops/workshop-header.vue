<template>
    <v-card-title class="d-flex justify-space-between align-center pb-0">
        <span>{{ title }}</span>
        <div class="d-flex align-center">
            <v-img src="images/flag fr.png" :width="30" class="mr-2"
                v-if="['fr', 'both'].includes(workshop.language)" />
            <v-img src="images/flag uk.png" :width="30" class="mr-2"
                v-if="['en', 'both'].includes(workshop.language) " />
            <v-chip size="small" :color="workshop.details.campus == 'BPR' ? 'blue' : 'red'">
                {{ workshop.details.campus}}
            </v-chip>
        </div>
    </v-card-title>
    <v-card-subtitle class="font-italic">
        {{ $t('By') }} {{ workshop.organiser.name }}
    </v-card-subtitle>
</template>
<script setup>
    const props = defineProps({ title: String, workshop: Object });
</script>