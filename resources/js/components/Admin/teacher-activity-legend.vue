<template>
    <v-icon class="mr-2" :size="32" icon="mdi-help-circle" color="captionColor" @click="showCard = !showCard"/>
    <Transition name="slide-fade">
        <v-card :title="$t('Legend')" width="225" style="position:absolute;top:20px;right:60px;z-index:100" v-if="showCard">
            <v-card-text>
                <div class="d-flex align-center mb-3" v-for="legend in legends">
                    <div class="sessionBox" :style="`background-color:${legend.color};`"/>
                    <span class="ml-2" style="line-height: 100%;white-space: break-spaces;">
                        {{ $t(legend.text) }}
                    </span>
                </div>
            </v-card-text>
            <v-icon style="position:absolute;top:2px;right:2px;" size="x-small" icon="mdi-close" color="error" @click="showCard=false"/>
        </v-card>
    </Transition>
</template>
<script setup>
    import { ref } from 'vue';

    const colors = {
        mission: '#555555',
        workshops: {
            past: '#9ACD32',
            future: '#6A7CFF'
        },
        openDoors: {
            past: '#C7E685',
            future: '#A2D9F1'
        },
    }

    const legends = [
        { text: 'Past workshop session', color: colors.workshops.past},
        { text: 'Past open door session', color: colors.openDoors.past},
        { text: 'Future workshop session', color: colors.workshops.future},
        { text: 'Future open door session', color: colors.openDoors.future},
    ];

    const showCard = ref(false);
</script>
<style scoped>
    .sessionBox {
        margin-left: 2px;
        margin-top: 1px;
        min-width: 18px;
        max-width: 18px;
        min-height: 18px;
        max-height: 18px;
    }
    .slide-fade-enter-active {
        transition: all 0.2s ease-out;
    }

    .slide-fade-leave-active {
        transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1);
    }

    .slide-fade-enter-from,
    .slide-fade-leave-to {
        transform: translateX(-30px);
        opacity: 0;
    }
</style>