<template>
    <TransitionGroup name="slide-fade">
        <v-alert
            :type="notification.type"
            :text="$t(notification.text)"
            min-width="75vw"
            max-width="75vw"
            style="cursor:pointer;"
            class="mb-1"
            density="compact"
            v-for="notification in notifications"
            :key="notification.id"
            @mouseenter="hold(notification.id)"
            @mouseleave="release(notification.id)"
            @click="removeNotification(notification.id)"
        />
    </TransitionGroup >
</template>
<script setup>
    import useNotifications from '@/composables/useNotifications';
    
    const { notifications, removeNotification, hold, release } = useNotifications();
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