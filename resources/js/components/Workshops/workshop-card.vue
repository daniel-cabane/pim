<template>
    <v-card style="position:relative" @click="seeWorkshop">
        <workshop-header :title="title" :workshop="workshop" />
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
    import { computed } from "vue";
    import { useRouter } from 'vue-router';
    import usePickWorkshopLg from '@/composables/usePickWorkshopLg';
    import { useI18n } from 'vue-i18n';

    const props = defineProps({workshop: Object});

    const { title, description } = usePickWorkshopLg(props.workshop);

    const { locale, t } = useI18n();

    const router = useRouter();
    const seeWorkshop = () => {
        router.push(`/workshops/${props.workshop.id}`);
    }
    const editWorkshop = () => {
        router.push(`/workshops/${props.workshop.id}/edit`);
    }

    // const startDateFormated = computed(() => {
    //     if(props.workshop.start_date){
    //         const date = new Date(props.workshop.start_date);
    //         const day = String(date.getDate()).padStart(2, '0');
    //         const month = String(date.getMonth() + 1).padStart(2, '0');
    //         const year = String(date.getFullYear()).slice(2);

    //         return `${day}-${month}-${year}`;
    //     }

    //     return `${t('Term')} ${props.workshop.term}`;
    // });
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