<template>
    <div class="eventBox">
        <v-card style="color:white;cursor: pointer;" :color="event.color"
            :style="`top:${event.startMinute}px;min-height:${event.height}px;max-height:${event.height}px;`">
            <div class="font-weight-bold" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
                {{ event.title }}
            </div>
            <div>
                {{ event.start }} - {{ event.end }}
            </div>
        </v-card>
        <event-info-card :event="event" :showInfo="showInfo" @hideMe="emit('hideMe')" style="z-index:10;" />
    </div>
</template>
<script setup>
    const emit = defineEmits(['hideMe']);
    const props = defineProps({ event: Object, showInfo: Boolean });
</script>
<style scoped>
    .eventBox {
        position: absolute;
        left: 2px;
        right: 3%;
        padding: 1px 5px;
    }
</style>