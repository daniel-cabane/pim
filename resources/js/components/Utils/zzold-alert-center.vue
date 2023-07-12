<template>
    <div>
        OLD
    </div>
    <!-- <TransitionGroup name="slide-fade">
        <v-alert
            :type="alert.type"
            :text="$t(alert.text)"
            min-width="75vw"
            max-width="75vw"
            style="cursor:pointer;"
            class="mb-1"
            density="compact"
            v-for="alert in alerts"
            :key="alert.id"
            @mouseenter="hold(alert.id)"
            @mouseleave="release(alert.id)"
        />
    </TransitionGroup > -->
</template>
<script setup>
    // import { useAlertStore } from '@/stores/useAlertStore';
    // import { storeToRefs } from 'pinia';
    
    // const alertStore = useAlertStore();
    // const { alerts } = storeToRefs(alertStore);
    // const { hold, release } = alertStore;
</script>

<style scoped>
    .slide-fade-enter-active {
        transition: all .2s ease;
    }

    .slide-fade-leave-active {
        transition: all .2s cubic-bezier(1.0, 0.5, 0.8, 1.0);
    }

    .slide-fade-leave-to {
        transform: translateY(-40px);
        opacity: 0;
    }

    .slide-fade-enter-from {
        transform: translateY(-40px);
        opacity: 0;
    }
    .slide-fade-move {
        transition: all .5s ease-out;
    }
</style>