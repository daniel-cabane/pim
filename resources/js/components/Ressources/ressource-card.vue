<template>
    <v-card width="400" :to="`/ressources/${ressource.slug}`">
        <img width="100%" :src="`/images/ressources/${ressource.slug}/cover.png`"/>
        <v-card-title class="pimTitleFont" style="font-size:1.75rem;">
            {{ ressource.title[locale] }}
        </v-card-title>
        <v-card-text>
            <div v-html="ressource.description[locale]"/>
            <div class="mt-4">
                <v-chip v-for="theme in ressource.themes" :text="theme[locale]" class="mr-2" color="secondary" variant="flat"/>
            </div>
        </v-card-text>
    </v-card>
</template>
<script setup>
    import { useI18n } from 'vue-i18n';

    const { locale } = useI18n();

    const props = defineProps({ ressource: Object });
</script>