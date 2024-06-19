<template>
    <v-img :src="poster" v-if="poster" />
    <div style="width: 100%;padding-top: 56.25%;" v-else>
        <div class="pickButton">
            <v-btn rounded="lg" color="primary" :disabled="imageLoading" @click="emit('pickPoster', language);">
                {{ texts.pick }}
            </v-btn>
        </div>
    </div>
</template>
<script setup>
    import { computed, defineEmits } from 'vue';

    const props = defineProps({ language: String, details: Object, pickingPoster: Boolean, imageLoading: Boolean });
    const texts = computed(() => {
        if(props.language == 'fr'){
            return {
                pick: 'Sélectionner une affiche'
            }
        }
        return {
            pick: 'Select a poster'
        }
    });
    const poster = computed(() => {
        return props.details[`poster_${props.language}`] ? props.details[`poster_${props.language}`] : null;
    });

    const emit = defineEmits(['pickPoster']);
</script>

<style scoped>
    .pickButton{
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>