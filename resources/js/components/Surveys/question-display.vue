<template>
    <v-card style="width:100%;position:relative;" class="pa-4">
        <v-tooltip text="Required">
            <template v-slot:activator="{ props }">
                <v-icon icon="mdi-asterisk-circle-outline" size="x-small" color="error"
                    style="position:absolute;top:5px;right:5px;" v-bind="props" v-if="question.required && !disabled" />
            </template>
        </v-tooltip>
        <div class="mb-2">
            {{ question[`q_${lg}`] }}
        </div>
        <div v-if="question.type == 'shortText'">
            <v-text-field variant="outlined" v-model="textFieldValue" :label="$t('Answer')"
                @update:model-value="emit('answerUpdated', { value: textFieldValue, index })" />
        </div>
        <div v-if="question.type == 'longText'">
            <v-textarea variant="outlined" :label="$t('Answer')" v-model="textAreaValue"
                @update:model-value="emit('answerUpdated', {value: textAreaValue, index})" />
        </div>
        <div v-if="question.type == 'radio'">
            <v-radio-group v-model="radioValue"
                @update:model-value="emit('answerUpdated', { value: radioValue, index })">
                <v-radio v-for="(option, i) in question.options" :label="option[lg]" :value="i+1" />
            </v-radio-group>
        </div>
        <div v-if="question.type == 'checkbox'">
            <v-checkbox v-for="(option, i) in question.options" :label="option[lg]" :value="i+1" v-model="checkboxValue"
                @update:model-value="emit('answerUpdated', { value: checkboxValue, index, checkbox: true })" />
        </div>
        <div class="overlay" v-if="disabled"/>
    </v-card>
</template>
<script setup>
    import { ref } from "vue";

    const props = defineProps({ question: Object, lg: String, index: Number, initialAnswer: [String, Number, Array, null], disabled: Boolean });
    const emit = defineEmits(['answerUpdated']);

    const textFieldValue = props.initialAnswer ? ref(props.initialAnswer) : ref('');
    const textAreaValue = props.initialAnswer ? ref(props.initialAnswer) : ref('');
    const radioValue = props.initialAnswer ? ref(props.initialAnswer) : ref(null);
    const checkboxValue = props.initialAnswer ? ref(props.initialAnswer) : ref([]);
</script>
<style scoped>
    .overlay {
        position: absolute;
        top: 0;
        width:100%;
        height:100%;
    }
</style>