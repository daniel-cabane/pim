<template>
    <v-card :title="survey.options[`title_${lg}`]" :subtitle="survey.options[`description_${lg}`]"
        style="position:relative;">
        <v-btn icon="mdi-close" color="error" size="large" variant="text" style="position:absolute;top:5px;right:5px;"
            @click="emit('closeDialog')" v-if="showCloseButton" />
        <v-card-text class="d-flex justify-center">
            <div style="max-width:600px;">
                <question-display class="my-5" v-for="question in survey.questions" :question="question" :lg="lg" />
                <div class="d-flex justify-end pa-2" v-if="showCloseButton">
                    <v-btn variant="tonal" color="error" @click="emit('closeDialog')">
                        Close
                    </v-btn>
                </div>
                <div class="d-flex justify-end pa-2" v-if="showSubmitButton">
                    <v-btn class="mr-2" variant="tonal" color="error" @click="emit('closeDialog')">
                        Cancel
                    </v-btn>
                    <v-btn class="mr-2" color="primary" @click="emit('closeDialog')">
                        Submit
                    </v-btn>
                </div>
            </div>
        </v-card-text>
    </v-card>
</template>
<script setup>
    import { computed } from "vue";
    import { useI18n } from 'vue-i18n';

    const { locale } = useI18n();

    const props = defineProps({ survey: Object, showCloseButton: { type: Boolean, default: false }, showSubmitButton: { type: Boolean, default: false } });
    const emit = defineEmits(['closeDialog']);

    const lg = computed(() => {
        return props.survey.options.language == 'both' ? locale : props.survey.options.language;
    });
</script>