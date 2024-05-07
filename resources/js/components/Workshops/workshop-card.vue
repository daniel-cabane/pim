<template>
    <v-card @click="seeWorkshop">
        <workshop-header :workshop="workshop"/>
        <v-card-text class="threelines">
            Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ipsum aperiam, accusamus quo modi repellendus illo sequi exercitationem quam nihil reprehenderit facilis necessitatibus explicabo vero ullam et unde porro eaque aspernatur.Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ipsum aperiam, accusamus quo modi repellendus illo sequi exercitationem quam nihil reprehenderit facilis necessitatibus explicabo vero ullam et unde porro eaque aspernatur.Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ipsum aperiam, accusamus quo modi repellendus illo sequi exercitationem quam nihil reprehenderit facilis necessitatibus explicabo vero ullam et unde porro eaque aspernatur.Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ipsum aperiam, accusamus quo modi repellendus illo sequi exercitationem quam nihil reprehenderit facilis necessitatibus explicabo vero ullam et unde porro eaque aspernatur.
        </v-card-text>
        <div class="px-4 text-caption text-grey">
            {{ $t('Themes') }}
        </div>
        <div class="px-4 pb-2 d-flex justify-space-between align-center">
            <workshop-themes-chips :themes="workshop.themes"/>
            <v-btn size="small" color="primary" icon="mdi-pencil" v-if="workshop.editable" @click.stop="editWorkshop"/>
        </div>
    </v-card>
</template>
<script setup>
    import { useRouter } from 'vue-router';

    const props = defineProps({workshop: Object});

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
        min-height: 78px;
        max-height: 78px;
    }
</style>