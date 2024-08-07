<template>
    <div style="position:relative;" class="my-3">
        <v-divider>
            <span class="text-caption text-captionColor mt-5 mb-2" style="white-space:nowrap;">
                Question {{ index + 1 }}
            </span>
        </v-divider>
        <div class="d-flex py-1">
            <v-select variant="outlined" label="Type" :items="questionTypes" v-model="question.type" />
            <v-switch class="mx-2" :label="$t('Required')" color="primary" v-model="question.required"/>
            <v-btn icon="mdi-delete" color="error" size="small" class="ma-2" @click="showOverlay=true" v-if="isDraft"/>
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
                <v-icon icon="mdi-close-octagon" size="x-large" color="error" @click="emit('deleteOption', {question, index})" v-if="isDraft"/>
            </div>
            <div class="text-center">
                <v-btn size="x-small" variant="tonal" block color="primary" @click="emit('addOption', question)">
                    {{ $t('Add option') }}
                </v-btn>
            </div>
        </div>
        <Transition name="swipe-down">
            <div class="deleteOverlay" v-if="showOverlay">
                <v-card width="350" class="text-center pa-4 text-h5">
                    <div class="d-flex align-center justify-center mb-3">
                        <v-icon class="mr-2" icon="mdi-alert" color="error" size="small"/>
                        <span>
                            {{ $t('Delete this question') }} ?
                        </span>
                    </div>
                    <div>
                        <v-btn variant="tonal" class="mr-2" color="primary" @click="showOverlay=false">
                            {{ $t('No') }}
                        </v-btn>
                        <v-btn variant="tonal" color="error" @click="emit('deleteQuestion', index)">
                            {{ $t('Yes') }}
                        </v-btn>
                    </div>
                </v-card>
            </div>
        </Transition>
    </div>
</template>
<script setup>
    import { ref } from "vue";
    const props = defineProps({ question: Object, language: String, index: Number, isDraft: Boolean });
    const emit = defineEmits(['addOption', 'deleteOption', 'deleteQuestion']);

    const questionTypes = [
        { title: 'Single choice', value: 'radio' }, { title: 'Multiple choice', value: 'checkbox' },
        { title: 'Short text', value: 'shortText' }, { title: 'Long text', value: 'longText' }
    ];

    const showOverlay = ref(false);
</script>
<style scoped>
    .deleteOverlay {
        position: absolute;
        top: -2%;
        left: -2%;
        width:104%;
        height: 104%;
        background:rgba(0, 0, 0, 0.4);
        border-radius: 12px;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .swipe-down-enter-active,
    .swipe-down-leave-active {
        transition: all 0.2s ease-out;
    }

    .swipe-down-enter-from,
    .swipe-down-leave-to {
        height:0px;
        opacity: 0;
    }
</style>