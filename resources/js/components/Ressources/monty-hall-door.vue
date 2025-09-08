<template>
    <v-img :src="src"/>
</template>
<script setup>
    import { computed } from "vue";
    const props = defineProps({ door: Object });

    const src = computed(() => {
        if(!props.door.open){
            if(props.door.selected){
                return '/images/ressources/monty-hall/door-selected.png'
            }
            return '/images/ressources/monty-hall/door.png'
        }
        if(props.door.win){
            if(props.door.selected){
                return '/images/ressources/monty-hall/door-car-selected.png'
            }
            return '/images/ressources/monty-hall/door-car.png'
        }
        if(props.door.selected){
                return '/images/ressources/monty-hall/door-goat-selected.png'
            }
            return '/images/ressources/monty-hall/door-goat.png'
    });
</script>