<template>
    <div style="position:relative;">
        <div :style="`background-color:${event.color}`" class="eventChip text-caption">
            <span>
                {{ event.start }} : <b>{{ event.eventType == 'session' ? event.title : $t(event.title) }}</b>
            </span>
            <event-info-card :event="event" :showInfo="showInfo" @hideMe="emit('hideMe')"/>
        </div>
    </div>
</template>
<script setup>
    const emit = defineEmits(['hideMe']);
    const props = defineProps({event: Object, showInfo: Boolean});
</script>
<style scoped>
    .eventChip {
        border-radius: 5px;
        padding: 0px 5px;
        margin: 0px 2px;
        max-width: 100%;
        cursor: pointer;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: white;
    }
</style>