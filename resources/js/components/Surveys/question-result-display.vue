<template>
    <div v-if="['shortText', 'longText'].includes(question.type)">
        <div class="text-h6 text-grey pa-2 mb-3">
            {{ question.q }}
        </div>
        <v-card hover class="pa-2 my-2" v-for="answer in question.answers.filter(a => a.comment)">
            <div class="d-flex justify-end text-caption text-captionColor">
                {{ answer.name }}
            </div>
            <div class="text-subtitle-1">
                {{ answer.comment }}
            </div>
        </v-card>
    </div>
    <div v-else>
        <div class="d-flex">
            <div style="flex:1;" class="text-h6 text-grey pa-2 mb-3">
                {{ question.q }}
            </div>
            <div class="d-flex align-center">
                <v-icon icon="mdi-poll"/>
                <v-switch hide-details class="mx-1" v-model="graphSwitch"/>
                <v-icon icon="mdi-chart-pie-outline"/>
            </div>
        </div>
        <question-result-graph :question="question" :language="language" :isPie="graphSwitch"/>
    </div>
</template>
<script setup>
    import { ref } from "vue";
    
    const props = defineProps({ question: Object, language: String });

    const graphSwitch = ref(false);
</script>