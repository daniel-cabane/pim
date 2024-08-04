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
        <div class="text-h6 text-grey pa-2 mb-3">
            {{ question.q }}
        </div>
        <question-bar-graph :question="question" :language="language"/>
    </div>
</template>
<script setup>
    const props = defineProps({ question: Object, language: String });
    console.log(props.question);
</script>