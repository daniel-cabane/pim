<template>
    <transition :name="isWeekend ? 'slide-fade-right' : 'slide-fade-left'">
        <v-card width="250" class="infoBox pa-2 text-left" :style="isWeekend ? 'left:-200px;' : 'right:-200px;'" v-show="showInfo" :ripple="false">
            <div class="d-flex justify-space-between align-center">
                <v-chip variant="flat" class="py-0" :color="event.color">{{ event.campus }}</v-chip>
                <v-btn variant="flat" size="x-small" icon="mdi-close" @click.stop.prevent="emit('hideMe')" />
            </div>
            <div class="mb-1 text-center text-h6" style="white-space:wrap;">
                {{ $t(event.title) }}
            </div>
            <event-details :event="event" />
        </v-card>
    </transition>
</template>
<script setup>
    import { computed } from "vue";
    const props = defineProps({ event: Object, showInfo: Boolean });
    const emit = defineEmits(['hideMe']);
    
    const isWeekend = computed(() => [0,6].includes(props.event.dayOfWeek));
</script>
<style scoped>
    .infoBox {
        position: absolute;
        top: -100px;
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
    .slide-fade-right-enter-active,
    .slide-fade-right-leave-active {
        transition: all 0.3s ease;
    }

    .slide-fade-right-enter-from,
    .slide-fade-right-leave-to {
        transform: translateX(100%);
        opacity: 0;
    }

    .slide-fade-right-enter-to,
    .slide-fade-right-leave-from {
        transform: translateX(0);
        opacity: 1;
    }
</style>