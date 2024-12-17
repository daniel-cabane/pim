<template>
    <div>
        <v-img class="align-end text-white" aspect-ratio="16/9" max-width="350" min-width="350" :src="post.cover.url">
            <v-card-title 
                class="pimSubtitleFont pb-0 pt-6"
                style="font-weight:bold;font-size:24px;line-height:1.2;"
                :style="`color:${fontColor};background: linear-gradient(to top, ${backgroundColor}, transparent);`"
            >
                {{ post.title }}
            </v-card-title>
        </v-img>
    </div>
</template>
<script setup>
    import { computed } from 'vue';

    const props = defineProps({ post: Object });

    const fontColor = computed( () => props.post.cover.titleColor ? props.post.cover.titleColor : 'black');
    const backgroundColor = computed(() => props.post.cover.titleColor == 'black' ? 'white' : 'black');
</script>
<style scoped>

</style>