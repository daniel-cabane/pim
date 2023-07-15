<template>
    <v-card @click="seeWorkshop">
        <v-card-title class="d-flex justify-space-between align-center">
            <span>{{ workshop.title }}</span>
            <v-chip size="small" :color="workshop.details.campus == 'BPR' ? 'blue' : 'red'">{{ workshop.details.campus }}</v-chip>
        </v-card-title>
        <v-card-text class="threelines">
            Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ipsum aperiam, accusamus quo modi repellendus illo sequi exercitationem quam nihil reprehenderit facilis necessitatibus explicabo vero ullam et unde porro eaque aspernatur.Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ipsum aperiam, accusamus quo modi repellendus illo sequi exercitationem quam nihil reprehenderit facilis necessitatibus explicabo vero ullam et unde porro eaque aspernatur.Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ipsum aperiam, accusamus quo modi repellendus illo sequi exercitationem quam nihil reprehenderit facilis necessitatibus explicabo vero ullam et unde porro eaque aspernatur.Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ipsum aperiam, accusamus quo modi repellendus illo sequi exercitationem quam nihil reprehenderit facilis necessitatibus explicabo vero ullam et unde porro eaque aspernatur.
        </v-card-text>
        <div class="px-4 text-caption text-grey">
            {{ $t('Themes') }}
        </div>
        <div class="px-4 pb-2 d-flex justify-space-between align-center">
            <span>
                <v-chip class="mr-2" color="primary" label v-for="(theme, index) in workshopThemes" :key="index">
                    {{ theme }}
                </v-chip>
                <v-chip label color="secondary" v-if="workshopThemes.length == 0">None</v-chip>
            </span>
            <v-btn size="small" color="primary" icon="mdi-pencil" v-if="workshop.editable" @click.stop="editWorkshop"/>
        </div>
    </v-card>
</template>
<script setup>
    import { computed } from 'vue';
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    import { storeToRefs } from 'pinia';
    import { useI18n } from 'vue-i18n';
    import { useRouter } from 'vue-router';

    const props = defineProps({workshop: Object});

    const workshopStore = useWorkshopStore();
    const { getThemes } = workshopStore;
    const { themes } = storeToRefs(workshopStore);

    getThemes();
    const { locale } = useI18n();
    const workshopThemes = computed(() => {
        const themeTitles = [];
        themes.value.forEach(th => {
            if(props.workshop.themes.indexOf(th.id) >= 0){
                themeTitles.push(locale == 'en' ? th.title_en : th.title_fr);
            }
        });
        return themeTitles;
    });

    const router = useRouter();
    const seeWorkshop = () => {
        router.push(`/workshops/${props.workshop.id}`);
    }
    const editWorkshop = () => {
        router.push(`/workshops/${props.workshop.id}/edit`);
    }
</script>

<style scoped>
    .threelines {
        overflow: hidden;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3; 
        white-space: pre-wrap;
        min-height: 62px;
        max-height: 62px;
    }
</style>