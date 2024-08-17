<template>
    <span>
        <v-chip 
            class="mr-2 mb-2"
            color="primary" 
            label 
            :size="size"
            v-for="(theme, index) in workshopThemes" 
            :key="index"
        >
            {{ theme }}
        </v-chip>
        <v-chip label color="secondary" v-if="workshopThemes.length == 0">
            {{ t('None') }}
        </v-chip>
    </span>
</template>
<script setup>
    import { computed } from 'vue';
    import { useI18n } from 'vue-i18n';
    import { useThemeStore } from '@/stores/useThemeStore';
    import { storeToRefs } from 'pinia';

    const { t } = useI18n();

    const themeStore = useThemeStore();
    const { getThemeById } = themeStore;

    const props = defineProps({themes: Array, size: {type: String, default: 'default'}});

    const { locale } = useI18n();
    const workshopThemes = computed(() => {
        return props.themes.map(id => {
            const th = getThemeById(id);
            return locale === 'fr' ? th.title_fr : th.title_en;
        });
    });
</script>