<template>
    <v-card>
        <v-card-title class='pimTitleFont' style="font-size:56px;">
            {{ mainTitle[locale] }}
        </v-card-title>
        <v-card-text>
            <v-row>
                <v-col cols='12' md="8">
                    <v-window v-model="onboarding">
                        <v-window-item>
                            One
                        </v-window-item>
                        <v-window-item>
                            Two
                        </v-window-item>
                    </v-window>
                </v-col>
            </v-row>
        </v-card-text>
        <v-card-actions style='display:block'>
            <div style='display:flex;justify-content:space-around'>
                <v-btn text @click="prev">
                    <v-icon>mdi-chevron-left</v-icon>
                </v-btn>
                <v-item-group v-model="onboarding" class="text-center" mandatory>
                    <v-item v-for="n in length" :key="`btn-${n}`" v-slot="{ active, toggle }">
                        <v-btn :input-value="active" icon @click="toggle">
                            <v-icon 
                                :icon="n == onboarding+1 ? 'mdi-record-circle-outline' : 'mdi-record'"
                                :color="n == onboarding+1 ? 'white' : 'captionColor'"
                            />
                        </v-btn>
                    </v-item>
                </v-item-group>
                <v-btn text @click="next">
                    <v-icon>mdi-chevron-right</v-icon>
                </v-btn>
            </div>
            <div style='display:flex'>
                <v-spacer/>
                <v-btn color='primary' variant="flat" style='width:200px' @click="emit('close')">
                    OK !
                </v-btn>
            </div>
        </v-card-actions>
    </v-card>
</template>
<script setup>
    import { ref } from "vue";
    import { useI18n } from 'vue-i18n';

    const { locale } = useI18n();

    const emit = defineEmits(['close']);

    const length = 5
    const onboarding = ref(0);

    const next = () => onboarding.value = onboarding.value + 1 === length ? 0 : onboarding.value + 1;
    const prev = () => onboarding.value = onboarding.value - 1 < 0 ? length - 1 : onboarding.value - 1;

    const mainTitle = { en: 'Relative Elevator', fr: 'Ascenceur relatif' };
    const text = [];
</script>