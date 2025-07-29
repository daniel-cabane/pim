<template>
    <v-card-title style="display:flex;flex-wrap:nowrap;max-width:100%">
        <div style="flex:1;">
            <v-card-title class="pb-1 pimSubtitleFont" style="font-size:48px;white-space:normal;line-height:60px;">
                {{ title }}
            </v-card-title>
            <v-card-subtitle class="font-italic">
                {{ $t('By') }} {{ workshop.teacher }}
            </v-card-subtitle>
        </div>
        <workshop-lcs :workshop="workshop" />
    </v-card-title>
    <v-card-text>
        <div v-html="description" />
        <v-container fluid class="px-0 pb-0">
            <v-row>
                <v-col cols="6" class="mb-4">
                    <div class="text-caption text-captionColor">
                        {{ $t('Start date') }}
                    </div>
                    <div>
                        {{ workshop.startDate ? workshop.formatedStartDate : $t(workshop.formatedStartDate) }}
                    </div>
                </v-col>
                <v-col cols="6" class="mb-4">
                    <div class="text-caption text-captionColor">
                        {{ $t('Nb of sessions') }}
                    </div>
                    <div>
                        {{ workshop.details.nbSessions }}
                    </div>
                </v-col>
                <v-col cols="6" class="mb-4">
                    <div class="text-caption text-captionColor">
                        {{ $t('Levels') }}
                    </div>
                    <div>
                        {{ workshop.details.levels.join(', ') }}
                    </div>
                </v-col>
                <v-col cols="6" class="mb-4">
                    <div class="text-caption text-captionColor">
                        {{ $t('Themes') }}
                    </div>
                    <div>
                        <workshop-themes-chips :themeTitles="workshop.themeTitles" size="small" />
                    </div>
                </v-col>
            </v-row>
        </v-container>
    </v-card-text>
    <v-card-actions>
        <v-spacer/>
        <v-btn 
            color="primary"
            variant="flat"
            append-icon="mdi-content-copy"
            :text="$t('Duplicate')"
            @click="emit('duplicate', workshop.id)"
        />
    </v-card-actions>
</template>
<script setup>
    import usePickWorkshopLg from '@/composables/usePickWorkshopLg';

    const props = defineProps({ workshop: Object });
    const emit = defineEmits(['duplicate']);

    const { title, description } = usePickWorkshopLg(props.workshop);
</script>