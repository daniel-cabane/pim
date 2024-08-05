<template>
    <v-card style="position:relative;">
        <v-toolbar color="secondary">
            <v-toolbar-title>Results - {{ survey.mainTitle }}</v-toolbar-title>
            <v-spacer />
            <v-toolbar-items>
                <v-btn icon="mdi-close" dark @click="emit('closeDialog')" />
            </v-toolbar-items>
        </v-toolbar>
        <v-progress-linear color="primary" style="position:absolute;top:60px;" indeterminate v-if="isLoading" />
        <v-card-text>
            <div style="max-width:750px;margin:auto;">
                <v-tabs v-model="tab">
                    <v-tab value="question">By question</v-tab>
                    <v-tab value="answer">By answer</v-tab>
                </v-tabs>
                <v-tabs-window v-model="tab" class="pa-3">
                    <v-tabs-window-item value="question">
                        <v-select variant="outlined" item-props v-model="questionNb" :items="questionOptions" />
                        <question-result-display :question="focusedQuestion"
                            :language="survey.options.language == 'fr' ? 'fr' : 'en'" v-if="questionNb !== null" />
                    </v-tabs-window-item>
                    <v-tabs-window-item value="answer">
                        <v-select variant="outlined" :label="$t('Student')" v-model="studentId"
                            :items="studentOptions" />
                        <survey-display-card :survey="studentSurvey" disableAnswers hideTitle :key="studentId"
                            v-if="studentId" />
                    </v-tabs-window-item>
                </v-tabs-window>
            </div>
        </v-card-text>
    </v-card>
</template>
<script setup>
    import { ref, computed } from "vue";
    const emit = defineEmits(['closeDialog']);
    const props = defineProps({ survey: Object, isLoading: Boolean });

    const tab = ref('question');
    const questionOptions = computed(() => {
        return props.survey.questions.map((q, i) => ({
            value: i,
            title: `Question ${i+1}`,
            subtitle: props.survey.options.language == 'fr' ? q.q_fr : q.q_en
        }));
    });

    const questionNb = ref(null);
    const focusedQuestion = computed(() => {
        const q = props.survey.questions[questionNb.value];
        return {
            type: q.type,
            q: props.survey.options.language == 'fr' ? q.q_fr : q.q_en,
            answers: props.survey.results.byQuestion[questionNb.value].data,
            options: q.options
        };
    })

    const studentId = ref(null);
    const studentOptions = computed(() => {
        return props.survey.results.byStudent.map(s => ({value: s.id, title: s.name}));
    });
    const studentSurvey = computed(() => {
        const studentAnswers = props.survey.results.byStudent.find(s => s.id == studentId.value);
        return { answers: studentAnswers ? Object.values(studentAnswers.data): null, ...props.survey }
    })
</script>