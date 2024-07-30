<template>
    <div>
        <v-divider>
            <span class="text-caption text-captionColor mt-5 mb-2" style="white-space:nowrap;">
                Question {{ index + 1 }}
            </span>
        </v-divider>
        <div class="d-flex">
            <v-select variant="outlined" label="Type" :items="questionTypes" v-model="question.type" />
            <v-switch class="ml-3" :label="$t('Required')" color="primary" v-model="question.required"/>
        </div>
        <div style="display:flex;gap:15px;">
            <v-text-field hide-details variant="outlined" style="flex:1" label="Question (en)" v-model="question.q_en"
                v-if="language != 'fr'" />
            <v-text-field hide-details variant="outlined" style="flex:1" label="Question (fr)" v-model="question.q_fr"
                v-if="language != 'en'" />
        </div>
        <div v-if="['radio', 'checkbox'].includes(question.type) ">
            <div class="my-3" style="display:flex;gap:15px;align-items:center;" v-for="(option, index) in question.options">
                <v-text-field hide-details variant="outlined" style="flex:1;" density="compact" v-model="question.options[index].en"
                    :label="`Option ${index + 1} (en)`" v-if="language != 'fr'" />
                <v-text-field hide-details variant="outlined" style="flex:1;" density="compact" v-model="question.options[index].fr"
                    :label="`Option ${index + 1} (fr)`" v-if="language != 'en'" />
                <v-icon icon="mdi-close-octagon" size="x-large" color="error" @click="emit('deleteOption', {question, index})"/>
            </div>
            <div class="text-center">
                <v-btn size="x-small" variant="tonal" block color="primary" @click="emit('addOption', question)">
                    {{ $t('Add option') }}
                </v-btn>
            </div>
        </div>
    </div>
</template>
<script setup>
    const props = defineProps({ question: Object, language: String, index: Number });
    const emit = defineEmits(['addOption', 'deleteOption']);

    const questionTypes = [
        { title: 'Single choice', value: 'radio' }, { title: 'Multiple choice', value: 'checkbox' },
        { title: 'Short text', value: 'shortText' }, { title: 'Long text', value: 'longText' }
    ]
</script>