<template>
    <v-card width="320" style="position:relative" @click="seeWorkshop">
        <div style="display:flex;flex-wrap:nowrap;max-width:100%">
            <div style="flex:1;max-width:220px;">
                <v-card-title class="pb-0 twolines pimSubtitleFont" style="font-size:24px;line-height:33px;">
                    {{ title }}
                </v-card-title>
                <v-card-subtitle class="font-italic">
                    {{ $t('By') }} {{ workshop.teacher }}
                </v-card-subtitle>
            </div>
            <workshop-lcs :workshop="workshop" />
        </div>
        <v-card-text class="threelines" v-html="description" />
        <div class="px-4">
            <div>
                <div class="text-caption text-grey">
                    {{ $t('Themes') }}
                </div>
                <workshop-themes-chips :themes="workshop.themes" />
            </div>
            <div class="mb-2">
                <div class="text-caption text-grey">
                    {{ $t('Start date') }}
                </div>
                <div>
                    {{ workshop.startDate ? workshop.formatedStartDate : $t(workshop.formatedStartDate) }}
                </div>
            </div>
        </div>
        <v-btn size="small" color="primary" icon="mdi-pencil" v-if="workshop.editable"
            style="position:absolute;right:10px;bottom:10px;" @click.stop="editWorkshop" />
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
    const editWorkshop = () => {
        router.push(`/workshops/${props.workshop.id}/edit`);
    }
</script>

<style scoped>
    .twolines {
        overflow: hidden;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2; 
        white-space: pre-wrap;
        min-height: 78px;
        max-height: 78px;
    }
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