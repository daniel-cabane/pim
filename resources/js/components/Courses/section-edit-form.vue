<template>
    <v-container class="pt-2">
        <v-row>
            <v-col cols="12" sm="6">
                <v-text-field variant="outlined" label="Titre (fr)" v-model="section.title.fr"/>
                <v-textarea variant="outlined" label="Description (fr)" v-model="section.description.fr"/>
            </v-col>
            <v-col cols="12" sm="6">
                <v-text-field variant="outlined" label="Title (en)" v-model="section.title.en"/>
                <v-textarea variant="outlined" label="Description (en)" v-model="section.description.en"/>
            </v-col>
        </v-row>
        <div v-for="(objective, index) in section.objectives" class="d-flex">
            <div>
                <div class="text-h6 text-captionColor pr-2">
                    {{ $t('Objective') }} {{ index+1 }}
                </div>
                <div class="d-flex justify-center mt-3">
                    <v-btn 
                        color="secondary"
                        icon="mdi-arrow-up"
                        size="x-small"
                        class="mr-1"
                        @click="emit('moveObjectiveUp', { section, objective })"
                        v-if="index > 0"
                    />
                </div>
            </div>
            <v-layout class="pt-2">
                <v-row>
                    <v-col cols="12" sm="6">
                        <v-text-field variant="outlined" label="Titre (fr)" v-model="objective.title.fr"/>
                        <v-textarea variant="outlined" label="Description (fr)" v-model="objective.description.fr"/>
                    </v-col>
                    <v-col cols="12" sm="6">
                        <v-text-field variant="outlined" label="Title (en)" v-model="objective.title.en"/>
                        <v-textarea variant="outlined" label="Description (en)" v-model="objective.description.en"/>
                    </v-col>
                    <v-col cols="12"> 
                        <v-text-field variant="outlined" label="Points" v-model="objective.points"/>
                    </v-col>
                </v-row>
            </v-layout>
        </div>
        <div class="d-flex justify-center my-2">            
            <v-btn color="primary" density="compact" prepend-icon="mdi-decagram" @click="emit('addObjective')">
                {{ $t('Add objective') }}
            </v-btn>
        </div>
    </v-container>
</template>
<script setup>
    const props = defineProps({ section: Object });

    const emit = defineEmits(['editSectionRessource', 'addObjective', 'moveObjectiveUp']);
</script>