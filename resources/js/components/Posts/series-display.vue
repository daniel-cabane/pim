<template>
    <div class="series-container" v-if="series.length">
        <v-dialog max-width="500">
            <template v-slot:activator="{ props: activatorProps }">
                <div
                    v-for="(serie, index) in adaptedSeries"
                    :key="index"
                    class="serie-item"
                    :class="{ open: openSerieIndex === index }"
                    :style="{ '--serie-color': serie.adaptedColor, '--serie-color-rgb': hexToRgb(serie.adaptedColor) }"
                    @click.stop.prevent="openSerieIndex = index"
                >
                    <span v-if="openSerieIndex === index" class="serie-title" @click.prevent="focusedSerie = serie" v-bind="activatorProps">
                        {{ serie.title }}
                    </span>
                </div>
            </template>

            <template v-slot:default="{ isActive }">
                <v-card>
                    <v-card :title="focusedSerie.title" :color="focusedSerie.adaptedColor" variant="tonal">
                    <v-card-text>
                        <v-card 
                            class="pa-2 my-2" 
                            style="width:100%;" 
                            v-for="post in focusedSerie.posts"
                            color="surface" 
                            :to="`/posts/${post.slug}`"
                        >
                            <v-icon icon="mdi-post" class="mr-2"/>
                            <span class="mt-3 pimSubtitleFont font-weight-bold">
                                {{ post.title }}
                            </span>
                        </v-card>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer/>
                        <v-btn
                            variant="tonal"
                            :text="$t('Close')"
                            @click="isActive.value = false"
                        />
                    </v-card-actions>
                    </v-card>
                </v-card>
            </template>
        </v-dialog>
    </div>
</template>
<script setup>
    import { ref, computed } from "vue";
    import { useTheme } from "vuetify";
    import { getAdaptiveSerieColor, hexToRgb as hexToRgbObject } from "@/utils/colorUtils.js";

    const props = defineProps({ series: Array });
    
    const theme = useTheme();
    const openSerieIndex = ref(0);
    const focusedSerie = ref(null);
    
    // Compute adapted colors based on current theme
    const adaptedSeries = computed(() => {
        return props.series.map(serie => ({
            ...serie,
            adaptedColor: getAdaptiveSerieColor(serie.color, theme.global.name.value === 'customDark')
        }));
    });
    
    const hexToRgb = (hex) => {
        const rgb = hexToRgbObject(hex);
        return `${rgb.r}, ${rgb.g}, ${rgb.b}`;
    };
</script>
<style scoped>
    .series-container {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .serie-item {
        background-color: var(--serie-color);
        cursor: pointer;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }
    .serie-item:not(.open) {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }
    .serie-item:not(.open):hover {
        width: 20px;
        height: 20px;
        border-radius: 8px;
    }
    .serie-item.open {
        flex: 1;
        padding: 4px 8px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(var(--serie-color-rgb), 0.15);
    }
    .serie-title {
        color: var(--serie-color);
        font-size: 0.75rem;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: flex;
        justify-content: center;
        width: 100%;
    }
</style>