<template>
    <v-container style="max-width:1168px;">
        <div class="d-flex justify-space-between align-center mb-5">
            <div class="text-h5 text-grey">
                {{ $t('π room schedule') }}
            </div>
            <v-menu v-if="isWindowSmall">
                <template v-slot:activator="{ props }">
                <v-btn icon="mdi-information-box" variant="text" v-bind="props"></v-btn>
                </template>

                <v-list>
                    <v-list-item v-for="legend in legends" :subtitle="$t(legend.comment)" :title="$t(legend.title)">
                        <template v-slot:prepend>
                            <div class="square mr-3" :style="`background:${legend.color}`"/>
                            <!-- <v-icon size="" :color="legend.color" icon="mdi-square"></v-icon> -->
                        <!-- <v-avatar :color="legend.color">
                        </v-avatar> -->
                        </template>
                    </v-list-item>
                </v-list>
            </v-menu>
            <div class="d-flex text-grey" v-else>
                <div class="d-flex ml-5" v-for="legend in legends">
                    <div class="square mr-1" :style="`background:${legend.color}`"/>
                    <div>
                        <div class="text-h6" style="line-height: 85%;">
                            {{ $t(legend.title) }}
                        </div>
                        <div class="text-caption">
                            {{ $t(legend.comment) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="isReady">
            <pi-room-week v-for="week in weeks" :week="week"/>
        </div>
    </v-container>
</template>
<script setup>
    import { useEventStore } from '@/stores/useEventStore';
    import { storeToRefs } from 'pinia';
    import { computed } from 'vue';
    import { useTheme, useDisplay } from 'vuetify';

    const theme = useTheme();

    const { name } = useDisplay();
    const isWindowSmall = computed(() => name.value == 'xs' || name.value == 'sm');
    
    const eventStore = useEventStore();
    const { getPiRoomEvents } = eventStore;
    const { weeks, isReady } = storeToRefs(eventStore);
    getPiRoomEvents();

    const legends = [
        {title: 'Open doors', comment: 'All students welcome', color: theme.global.current.value.colors.openDoors},
        {title: 'Workshop', comment: 'Silent games allowed', color: theme.global.current.value.colors.inclusiveWorkshop},
        {title: 'Workshop', comment: 'Registered students only', color: theme.global.current.value.colors.exclusiveWorkshop},
    ]

</script>
<style scoped>
    .square {
        width: 32px;
        height: 32px;
    }
</style>