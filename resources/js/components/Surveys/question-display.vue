<template>
    <v-card style="width:100%;position:relative" class="pa-4">
        <v-tooltip text="Required">
            <template v-slot:activator="{ props }">
                <v-icon icon="mdi-asterisk-circle-outline" size="x-small" color="error"
                    style="position:absolute;top:5px;right:5px;" v-bind="props" v-if="question.required"/>
            </template>
        </v-tooltip>
        {{ question[`q_${lg}`] }}
    </v-card>
</template>
<script setup>
    const props = defineProps({ question: Object, lg: String });
    console.log(props.question);
</script>