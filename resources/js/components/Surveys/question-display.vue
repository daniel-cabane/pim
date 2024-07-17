<template>
    <v-card style="width:100%;position:relative;" class="pa-4">
        <v-tooltip text="Required">
            <template v-slot:activator="{ props }">
                <v-icon icon="mdi-asterisk-circle-outline" size="x-small" color="error"
                    style="position:absolute;top:5px;right:5px;" v-bind="props" v-if="question.required" />
            </template>
        </v-tooltip>
        <div class="mb-2">
            {{ question[`q_${lg}`] }}
        </div>
        <div v-if="question.type == 'shortText'">
            <v-text-field variant="outlined" :label="$t('Answer')" />
        </div>
        <div v-if="question.type == 'longText'">
            <v-textarea variant="outlined" :label="$t('Answer')" />
        </div>
        <div v-if="question.type == 'radio'">
            <v-radio-group>
                <v-radio v-for="(option, index) in question.options" :label="option[lg]" :value="index" />
            </v-radio-group>
        </div>
        <div v-if="question.type == 'checkbox'">
            <v-checkbox v-for="(option, index) in question.options" :label="option[lg]" :value="index" />
        </div>
    </v-card>
</template>
<script setup>
    const props = defineProps({ question: Object, lg: String });
</script>