<template>
    <v-card :title="survey.options[`title_${lg}`]" :subtitle="survey.options[`description_${lg}`]">
        <v-card-text class="d-flex justify-center">
            <div style="max-width:600px;">
                <question-display class="my-5" v-for="question in survey.questions" :question="question" :lg="lg" />
            </div>
        </v-card-text>
    </v-card>
</template>
<script setup>
    import { computed } from "vue";
    import { useI18n } from 'vue-i18n';

    const { locale } = useI18n();

    const props = defineProps({ survey: Object });

    const lg = computed(() => {
        return props.survey.options.language == 'both' ? locale : props.survey.options.language;
    });
</script>