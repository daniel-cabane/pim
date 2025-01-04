<template>
    <v-container style="max-width:1168px;">
        <div class="text-h5 text-grey mb-5">
            {{ $t('π room schedule') }}
        </div>
        <div v-if="isReady">
            <pi-room-week v-for="week in weeks" :week="week"/>
        </div>
    </v-container>
</template>
<script setup>
    import { useEventStore } from '@/stores/useEventStore';
    import { storeToRefs } from 'pinia';
    
    const eventStore = useEventStore();
    const { getPiRoomEvents } = eventStore;
    const { weeks, isReady } = storeToRefs(eventStore);
    getPiRoomEvents();

</script>