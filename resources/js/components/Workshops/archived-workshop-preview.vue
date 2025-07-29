<template>
    <v-card :title="workshop.language == 'fr' ? workshop.title.fr : workshop.title.en" :subtitle="formatStartingDate(workshop.startDate)">
        <v-card-text>
            {{ workshop.language == 'fr' ? workshop.description.fr : workshop.description.en }}
        </v-card-text>
        <v-card-actions>
            <v-spacer/>
            <v-btn color="error" icon="mdi-delete" @click="emit('delete', workshop)"/>
            <v-btn color="primary" icon="mdi-eye" @click="emit('see', workshop)"/>
        </v-card-actions>
    </v-card>
</template>
<script setup>
    const props = defineProps({ workshop: Object });

    const emit = defineEmits(['see', 'delete']);

    const formatStartingDate = date => {
        if(date){
            return date.split('-').reverse().join('-');
        }
        return 'No starting date';
    }
</script>