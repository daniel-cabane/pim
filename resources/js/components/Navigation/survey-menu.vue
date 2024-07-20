<template>
    <v-menu close-on-content-click>
        <template v-slot:activator="{ props }">
            <v-btn dark icon="mdi-chat-question-outline" v-bind="props" class="pulse" />
        </template>
        <v-list>
            <v-list-subheader>
                {{ $t('Surveys') }}
            </v-list-subheader>
            <v-list-item v-for="survey in surveys" :to="`/surveys/${survey.id}`">
                {{ survey.title }}
            </v-list-item>
        </v-list>
    </v-menu>
</template>
<script setup>
    const props = defineProps({ surveys: Array });

    // const mainTitle = survey => {
    //     return survey.options.language == 'fr' ? survey.options.title_fr : survey.options.title_en;
    // }
</script>

<style scoped>
    .pulse {
        border-radius: 100%;
        animation: pulse-animation 2s infinite;
    }
    @keyframes pulse-animation {
        0% {
            box-shadow: 0 0 0 0px rgba(255, 255, 255, 0.2);
        }

        100% {
            box-shadow: 0 0 0 20px rgba(255, 255, 255, 0);
        }
    }
</style>