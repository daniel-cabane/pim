<template>
    <v-container>
        <v-row>
            <v-card color='surface' width='100%' height='400px' class='pa-2 mb-2'>
                <span class='number-display' :class="theme.name.value == 'customDark' ? 'inset-text-dark' : 'inset-text-light'" :style='["lg", "xl"].includes(name) ? "font-size:200px" : "font-size:150px"'>
                    {{ value }}
                </span>
                <v-avatar v-for="token in tokenList" :color="token ? 'white' : 'black'" class='ma-1 elevation-4'>
                    <v-icon size="large" :icon="token ? 'mdi-plus' : 'mdi-minus'"/>
                </v-avatar>
                <v-card color='error' class='white--text pa-2' style='position:absolute;right:5px;bottom:5px' v-if='locked'>
                    <v-icon color='white' medium>mdi-lock</v-icon>
                </v-card>
                <span></span>
            </v-card>
        </v-row>
        <v-row style='display:flex;justify-content:space-around'>
            <v-btn 
                class="mb-2"
                @click="emit('addWhite', index)"
                :disabled='tokenList.length >= 20 || locked'
                text="Jeton"
                :append-icon="theme.name.value == 'customDark' ? 'mdi-plus-circle' : 'mdi-plus-circle-outline'"
            />
            <v-btn 
                class="mb-2"
                @click="emit('addBlack', index)"
                :disabled='tokenList.length >= 20 || locked'
                text="Jeton"
                :append-icon="theme.name.value == 'customDark' ? 'mdi-minus-circle-outline' : 'mdi-minus-circle'"
            />
            <v-btn 
                class="mb-2"
                @click="emit('clear', index)" 
                color='error' 
                variant='outlined' 
                :disabled='locked'
                append-icon="mdi-close-octagon-outline"
                text="clear"
            />
        </v-row>
    </v-container>
</template>
<script setup>
    import { ref, computed } from 'vue';
    import { useDisplay, useTheme } from 'vuetify';
        
    const theme = useTheme();
    console.log(theme.name.value);
    const { name } = useDisplay();

    const props = defineProps({ index: Number, tokenList: Array, locked: Boolean });
    const emit = defineEmits(['addWhite', 'addBlack', 'clear']);

    const value = computed(() => {
        if (props.tokenList.length === 0) return 0;
        return props.tokenList.reduce((accum, num) => accum + (num ? 1 : -1), 0);
    });

    const randomTokens = () => {
    const tokenValue = Math.random() < 0.5;
    const nbTokens = Math.floor(Math.random() * 16);
    props.tokenList = [];
    for (let i = 0; i < nbTokens; i++) {
        props.tokenList.push(tokenValue);
    }
    locked.value = true;
    //   eventBus.$emit('emitTerm', { val: value.value, index: props.index });
    };
</script>

<style scoped>
    .number-display {
        position:absolute;
        top:0;
        width:100%;
        height:100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .inset-text-light {
        color: rgba(240,240,240, 0.6);
        text-shadow: 1px 4px 6px #eee, 0 0 0 #333, 1px 4px 6px #eee;
    }
    .inset-text-dark {
        color: rgba(240, 240, 240, 0.6); /* Slightly brighter for better visibility */
        text-shadow: 1px 4px 6px #333, 0 0 0 #eee, 1px 4px 6px #333;
    }
</style>