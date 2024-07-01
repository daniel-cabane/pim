<template>
    <div style="position:relative;">
        <div :style="`background-color:${event.color}`" class="eventChip text-caption">
            <span>
                {{ event.start }} : <b>{{ event.eventType == 'session' ? event.title : $t(event.title) }}</b>
            </span>
            <transition name="slide-fade-left">
                <v-card width="225" class="infoBox pa-2 text-left" v-show="showInfo" :ripple="false">
                    <div class="d-flex justify-space-between align-center">
                        <v-chip variant="flat" class="py-0" :color="event.color">{{ event.campus }}</v-chip>
                        <v-btn variant="flat" size="x-small" icon="mdi-close" @click.stop.prevent="emit('hideMe')" />
                    </div>
                    <div class="mb-1 text-center text-h6" style="white-space:wrap;">
                        {{ event.title }}
                    </div>
                    <event-details :event="event" />
                </v-card>
            </transition>
        </div>
    </div>
</template>
<script setup>
    const emit = defineEmits(['hideMe'])

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
    .infoBox {
        position: absolute;
        top: -50px;
        right: -200px;
        z-index: 1000;
    }
    .slide-fade-left-enter-active,
    .slide-fade-left-leave-active {
        transition: all 0.3s ease;
    }

    .slide-fade-left-enter-from,
    .slide-fade-left-leave-to {
        transform: translateX(-100%);
        opacity: 0;
    }

    .slide-fade-left-enter-to,
    .slide-fade-left-leave-from {
        transform: translateX(0);
        opacity: 1;
    }
</style>