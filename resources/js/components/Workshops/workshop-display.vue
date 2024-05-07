<template>
    <workshop-header :workshop="workshop"/>
    <v-card-text>
        <div v-html="workshop.description"/>
        <v-container fluid class="px-0">
            <v-row no-gutters>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-grey">
                        {{ $t('Sessions') }}
                    </div>
                    <div v-for="(session, index) in workshop.details.schedule" :key="index">
                        {{ session.day }} from {{ session.start }} to {{ session.finish }}
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-grey">
                        {{ $t('Start date') }}
                    </div>
                    <div >
                        {{ workshop.start_date }}
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-grey">
                        {{ $t('Finish date') }}
                    </div>
                    <div >
                        {{ workshop.start_date }}
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-grey">
                        {{ $t('Room') }}
                    </div>
                    <div >
                        {{ workshop.details.location }}
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-grey">
                        {{ $t('Language') }}
                    </div>
                    <div >
                        {{ workshop.details.language }}
                    </div>
                </v-col>
                <v-col cols="12" sm="6" md="4" class="mb-4">
                    <div class="text-caption text-grey">
                        {{ $t('Themes') }}
                    </div>
                    <div >
                        <workshop-themes-chips :themes="workshop.themes" size="small"/>
                    </div>
                </v-col>
            </v-row>
        </v-container>
    </v-card-text>
</template>
<script setup>
    import { onMounted } from 'vue';
    const props = defineProps({ workshop: Object });

    onMounted(() => console.log(props.workshop));
</script>