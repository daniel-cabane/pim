<template>
    <v-container>
        <post-display :post="post" :editing="false"/>
    </v-container>
</template>
<script setup>
    // import { ref, onMounted } from 'vue';
    import { useRoute } from 'vue-router';
    import { usePostStore } from '@/stores/usePostStore';
    import { storeToRefs } from 'pinia';

    const route = useRoute();
    const postStore = usePostStore();
    const { getPost } = postStore;
    const { post } = storeToRefs(postStore)

    getPost(route.params.slug);

    // onMounted(async () => {
    //     await getPost(route.params.slug);
    // })
</script>