<template>
    <div class="d-flex">
        <div style="max-width:15%;">
            <div v-for="option in question.options" class="d-flex justify-end align-center" style="min-height:50px;max-height:50px;overflow-y:hidden;">
                {{ option[language] }}
            </div>
        </div>
        <div style="flex:1;" :style="`max-width:${maxScore*100}px`">
            <div v-for="(option, index) in question.options" class="d-flex align-center pl-2" style="min-height:50px;max-height:50px;">
                <v-card hover color="primary" style="height:40px;" :style="`width:${question.answers[index].length*100/maxScore}%;`"/>
            </div>
        </div>
    </div>
</template>
<script setup>
    import { computed } from "vue";

    const props = defineProps({ question: Object, language: String });

    const maxScore = computed(() => props.question.answers.reduce((maxLength, currentArray) => Math.max(maxLength, currentArray.length), 0));
</script>