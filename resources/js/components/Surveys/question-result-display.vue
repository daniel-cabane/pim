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
        <div v-for="(option, index) in question.options" class="d-flex">
            <span>
                {{ option[language] }}
            </span>
            <v-card class="ml-2" color="primary" style="height:20px;" :style="`width:${question.answers[index].length*20}px;`">
            </v-card>
        </div>
    </div>
</template>
<script setup>
    const props = defineProps({ question: Object, language: String });
    console.log(props.question);
</script>