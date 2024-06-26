<template>
    <v-card width="350" style="position:relative" @click="seeWorkshop">
        <div style="display:flex;flex-wrap:nowrap;max-width:100%">
            <div style="flex:1;max-width:250px;">
                <v-card-title class="pb-0" style="text-overflow:ellipsis;">
                    {{ title }}
                </v-card-title>
                <v-card-subtitle class="font-italic">
                    {{ $t('By') }} {{ workshop.teacher }}
                </v-card-subtitle>
            </div>
            <div class="text-right pa-2">
                <div class="d-flex align-center">
                    <v-img :src="`/images/flag ${workshop.language}.png`" :width="30" class="mr-2"/>
                    <v-chip variant="elevated" theme="dark" size="small"
                        :color="workshop.details.campus == 'BPR' ? 'blue' : 'red'">
                        {{ workshop.details.campus }}
                    </v-chip>
                </div>
                <v-chip label :variant="workshop.status == 'draft' ? 'tonal' : 'elevated'" theme="dark" class="mt-1"
                    :color="statusColor[workshop.status]" :text="$t(workshop.status) " v-if="workshop.editable" />
            </div>
        </div>
        <v-card-text class="threelines">
            {{ description }}
        </v-card-text>
        <div class="px-4" style="display:flex;">
            <div style="flex:1;">
                <div class="text-caption text-grey">
                    {{ $t('Themes') }}
                </div>
                <workshop-themes-chips :themes="workshop.themes" />
            </div>
            <div style="flex:1;">
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

    const statusColor = { draft: 'secondary', submitted: 'warning', confirmed: 'success' }
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