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
        <v-chip label color="secondary" v-if="workshopThemes.length == 0">None</v-chip>
    </span>
</template>
<script setup>
    import { computed } from 'vue';
    import { useI18n } from 'vue-i18n';
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    import { storeToRefs } from 'pinia';

    const workshopStore = useWorkshopStore();
    const { getThemes } = workshopStore;
    const { themes } = storeToRefs(workshopStore);

    getThemes();

    const props = defineProps({themes: Array, size: {type: String, default: 'default'}});

    const { locale } = useI18n();
    const workshopThemes = computed(() => {
        const themeTitles = [];
        themes.value.forEach(th => {
            if(props.themes.indexOf(th.id) >= 0){
                themeTitles.push(locale == 'en' ? th.title_en : th.title_fr);
            }
        });
        return themeTitles;
    });
</script>