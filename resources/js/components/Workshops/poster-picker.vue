<template>
    <v-hover v-if="poster">
        <template v-slot:default="{ isHovering, props }">
            <div style="position:relative" v-bind="props">
                <v-img :src="poster" />
                <div style="position:absolute;right:15px;bottom:15px;" v-show="isHovering">
                    <v-dialog width="300" v-model="deleteDialog">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn color="error" class="mr-3" v-bind="activatorProps">
                                {{ $t('Delete poster') }}
                            </v-btn>
                        </template>
                        <template v-slot:default="{ isActive }">
                            <v-card :title="t('Delete poster')">
                                <v-card-text>
                                    {{ $t('Are you sure you wish to delete this file ?') }}
                                </v-card-text>
                                <div style="display:flex;justify-content:flex-end;" class="pa-3">
                                    <v-btn variant="tonal" class="mr-3" color="primary" @click="deleteDialog = false">
                                        {{ $t('Cancel') }}
                                    </v-btn>
                                    <v-btn color="error" @click="emit('deletePoster', language);deleteDialog=false;">
                                        {{ $t('Delete') }}
                                    </v-btn>
                                </div>
                            </v-card>
                        </template>
                    </v-dialog>
                    <v-btn color="primary" @click="emit('pickPoster', language);">
                        {{ $t('Change poster') }}
                    </v-btn>
                </div>
            </div>
        </template>
    </v-hover>
    <div style="width: 100%;padding-top: 56.25%;" v-else>
        <div class="pickButton">
            <v-btn rounded="lg" color="primary" :disabled="imageLoading" @click="emit('pickPoster', language);">
                {{ texts.pick }}
            </v-btn>
        </div>
    </div>
</template>
<script setup>
    import { computed, defineEmits, ref } from 'vue';
    import { useI18n } from 'vue-i18n';

    const { t } = useI18n();

    const deleteDialog=ref(false);

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

    const emit = defineEmits(['pickPoster', 'deletePoster']);
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