<template>
    <v-row>
        <v-col cols="12" class='text-h6 text-captionColor'>
            <div>
                {{ $t('Historic') }}
            </div>
            <div>
                <v-btn block color="secondary" size="x-small" :text="$t('Reset')" @click="emit('reset')"/>
            </div>
        </v-col>
        <v-col cols="6" class='pa-0' style='display:flex;align-items:center;justify-content:center'>
        <v-card width='150px' height='50px' color='white' style='display:flex;align-items:center;justify-content:center'>
            {{ $t('Keep') }}
        </v-card>
        </v-col>
        <v-col cols="6" class='pa-0' style='display:flex;align-items:center;justify-content:center'>
        <v-card width='150px' height='50px' color='black' dark style='display:flex;align-items:center;justify-content:center'>
            {{ $t('Change') }}
        </v-card>
        </v-col>
        <v-col cols="6" class='text-h6 text-center'>
        {{ keepRatio }}
        </v-col>
        <v-col cols="6" class='text-h6 text-center'>
        {{ changeRatio }}
        </v-col>
        <v-col cols="6" style='display:flex;justify-content:center;'>
            <div class='historyPan'>
                <div 
                    v-for='i in history.keepWin'
                    class="square"
                    style='background:rgb(0,255,0)'
                />
                <div 
                    v-for='i in history.keepLose'
                    class="square"
                    style='background:rgb(255,0,0)'
                />
            </div>
        </v-col>
        <v-col cols="6" style='display:flex;justify-content:center;'>
            <div class='historyPan'>
                <div 
                    v-for='i in history.changeWin'
                    class="square"
                    style='background:rgb(0,255,0)'
                />
                <div 
                    v-for='i in history.changeLose'
                    class="square"
                    style='background:rgb(255,0,0)'
                />
            </div>
        </v-col>
    </v-row>
</template>
<script setup>
    import { computed } from "vue";

    const props = defineProps({ history: Object });
    const emit = defineEmits(['reset']);

    const changeRatio = computed(() => {
        if (props.history.changeWin + props.history.changeLose === 0) return '--';
        const ratio = Math.floor((100 * props.history.changeWin) / (props.history.changeWin + props.history.changeLose));
        return `${ratio}%`;
    });

    const keepRatio = computed(() => {
        if (props.history.keepWin + props.history.keepLose === 0) return '--';
        const ratio = Math.floor((100 * props.history.keepWin) / (props.history.keepWin + props.history.keepLose));
        return `${ratio}%`;
    });
</script>
<style scoped>
    .historyPan{
        display:flex;
        flex-direction: column-reverse;
        justify-content: flex-start;
        align-content: center;
        flex-wrap: wrap;
        height: 250px;
        margin: 0px 2px
    }
    .square {
        width:20px;
        height:20px;
        margin:1px;
    }
</style>