<template>
    <v-card width="320" style="position:relative" @click="seeWorkshop">
        <div style="display:flex;flex-wrap:nowrap;max-width:100%">
            <div style="flex:1;max-width:220px;">
                <v-card-title class="pt-3 twolines pimSubtitleFont" style="font-size:20px;line-height:1.2;">
                    {{ title }}
                </v-card-title>
                <v-card-subtitle class="font-italic pt-1">
                    {{ $t('By') }} {{ workshop.teacher }}
                </v-card-subtitle>
            </div>
            <workshop-lcs :workshop="workshop" />
        </div>
        <v-card-text class="threelines" v-html="description" />
        <div class="px-4">
            <div>
                <div class="text-caption text-captionColor">
                    {{ $t('Themes') }}
                </div>
                <workshop-themes-chips :themeTitles="workshop.themeTitles" />
            </div>
            <div class="mb-2 d-flex text-caption">
                <div style="flex:1;max-width:50%">
                    <div class="text-captionColor">
                        {{ workshop.startDate ? $t('Start date') : $t('Period') }}
                    </div>
                    <div>
                        {{ workshop.startDate ? workshop.formatedStartDate : $t(workshop.formatedStartDate) }}
                    </div>
                </div>
                <div style="flex:1;max-width:50%">
                    <div class="text-captionColor">
                        {{ $t('Levels') }}
                    </div>
                    <div class="text-truncate">
                        {{ workshop.details.levels.join(', ') }}
                    </div>
                </div>
            </div>
        </div>
        <!-- <v-btn size="small" color="primary" icon="mdi-pencil" v-if="workshop.editable"
            style="position:absolute;right:10px;bottom:10px;" @click.stop="editWorkshop" /> -->
    </v-card>
</template>
<script setup>
    import { useRouter } from 'vue-router';
    import usePickWorkshopLg from '@/composables/usePickWorkshopLg';

    const props = defineProps({workshop: Object});

    const { title, description } = usePickWorkshopLg(props.workshop);

    const router = useRouter();
    const seeWorkshop = () => {
        router.push(`/workshops/${props.workshop.id}`);
    }
    // const editWorkshop = () => {
    //     router.push(`/workshops/${props.workshop.id}/edit`);
    // }
</script>

<style scoped>
    .twolines {
        overflow: hidden;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        line-clamp: 2;
        -webkit-line-clamp: 2; 
        white-space: pre-wrap;
        min-height: 60px;
        max-height: 60px;
    }
    .threelines {
        overflow: hidden;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        line-clamp: 3;
        -webkit-line-clamp: 3;
        white-space: pre-wrap;
        min-height: 60px;
        max-height: 60px;
    }
</style>