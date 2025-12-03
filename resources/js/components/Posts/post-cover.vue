<template>
    <div>
        <v-img class="align-end" aspect-ratio="16/9" max-height="197" min-height="197" cover :src="post.cover.url">
            <div 
                class="pimSubtitleFont px-4 pt-6"
                style="font-weight:bold;font-size:24px;line-height:1.2;max-height:82px;"
                :style="`color:${fontColor};background: linear-gradient(to top, ${backgroundColor}, transparent);`"
            >
                {{ post.title }}
            </div>
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