<template>
    <v-card style="position:relative;">
        <v-card-title class="d-flex justify-space-between align-center" v-if="!hideTitle">
            <span class="text-truncate">
                {{ survey.options[`title_${lg}`] }}
            </span>
            <v-chip color="secondary" v-if="disableAnswers">
                {{ $t('Answers locked') }}
            </v-chip>
            <v-chip color="primary" v-else>
                {{ $t('Answers editable') }}
            </v-chip>
        </v-card-title>
        <v-card-subtitle v-if="!hideTitle" v-html="survey.options[`description_${lg}`]"/>
        <v-btn icon="mdi-close" color="error" size="large" variant="text" style="position:absolute;top:5px;right:5px;" @click="emit('closeDialog')" v-if="showCloseButton" />
        <v-card-text class="d-flex justify-center">
            <div style="min-width:350px;width:600px;max-width:600px;">
                <question-display 
                    class="my-5"
                    v-for="(question, index) in survey.questions"
                    :question="question"
                    :style="missingRequired.includes(index) ? 'border:5px solid red' : ''" :lg="lg" :index="index"
                    :initialAnswer="survey.answers ? survey.answers[index] : null" :disabled="disableAnswers"
                    @answerUpdated="answerUpdated" 
                />
                <div class="d-flex justify-end pa-2" v-if="showCloseButton">
                    <v-btn variant="tonal" color="error" @click="emit('closeDialog')">
                        Close
                    </v-btn>
                </div>
                <div class="d-flex justify-end pa-2" v-if="showSubmitButton">
                    <v-btn class="mr-2" variant="tonal" :disabled="isLoading" color="error" @click="emit('cancel')">
                        {{ $t('Cancel') }}
                    </v-btn>
                    <v-btn class="mr-2" color="primary" :disabled="missingRequired.length > 0 || disableAnswers"
                        :loading="isLoading" @click="handleSubmit">
                        {{ $t('Submit') }}
                    </v-btn>
                </div>
            </div>
        </v-card-text>
        <transition name="slide-right">
            <v-card color="error" width="250" class="tabRight pa-3" v-if="missingRequired.length > 0">
                <div class="text-caption">
                    <span v-if="missingRequired.length > 1">
                        {{ $t('Missing required answers') }}
                    </span>
                    <span v-else>
                        {{ $t('Missing required answer') }}
                    </span>
                </div>
                <div>
                    Question<span v-if="missingRequired.length>1">s</span>
                    &nbsp
                    <span v-for="qnb in missingRequired">
                        {{ qnb+1 }}&nbsp
                    </span>
                </div>
            </v-card>
        </transition>
    </v-card>
</template>
<script setup>
    import { ref, reactive, computed } from "vue";
    import { useI18n } from 'vue-i18n';

    const { locale } = useI18n();

    const props = defineProps({ 
        survey: Object, 
        hideTitle: { type: Boolean, default: false }, 
        showCloseButton: { type: Boolean, default: false },
        showSubmitButton: { type: Boolean, default: false },
        disableAnswers: { type: Boolean, default: false },
        isLoading: { type: Boolean, default: false }
    });
    const emit = defineEmits(['closeDialog', 'cancel', 'submitSurvey']);

    const lg = computed(() => {
        return props.survey.options.language == 'both' ? locale.value : props.survey.options.language;
    });

    const answers = reactive(props.survey.questions.map((q, i) => props.survey.answers ? props.survey.answers[i] : null));
    const missingRequired = ref([]);
    const answerUpdated = data => {
        answers[data.index] = data.value;
        missingRequired.value = missingRequired.value.filter(i => i!=data.index);
    }
    const handleSubmit = () => {
        props.survey.questions.forEach((q, i) => {
            if (q.required && (answers[i] === null || answers[i] === '')){
                missingRequired.value.push(i);
            }
        });
        if(missingRequired.value.length == 0){
            emit('submitSurvey', answers);
        }
    }
    const disableAnswers = computed(() => props.disableAnswers || props.survey.status != 'open' || props.survey.answers && !props.survey.options.answerEditable);
</script>

<style scoped>
    .tabRight {
        position: fixed;
        top: 150px;
        right: -10px;
    }
    .slide-right-enter-active,
    .slide-right-leave-active {
        transition: right 0.3s ease-out;
    }

    .slide-right-enter-from,
    .slide-right-leave-to {
        right: -275px;
    }

    .slide-right-enter-to,
    .slide-right-leave-from {
        right: -10px;
    }
</style>