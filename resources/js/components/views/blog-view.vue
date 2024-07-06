<template>
    <v-container>
        <v-text-field variant="outlined" hide-details class="elevation-5" v-model="title" label="Search by title"
            clearable @click:clear="searching=false;getPublishedPosts(0, 12)" @keyup="typing" />
        <div class="d-flex flex-wrap justify-space-around mt-2" v-if="isReady">
            <post-home-card class="ma-2" v-for="post in posts" :key="post.id" :post="post" />
        </div>
        <div v-if="!searching">
            <v-pagination v-model="page" :length="paginationLength"
                @update:modelValue="getPublishedPosts((page - 1) * 12, 12)" />
        </div>
    </v-container>
</template>
<script setup>
    import { ref, computed } from "vue";
    import { usePostStore } from '@/stores/usePostStore';
    import { storeToRefs } from 'pinia';

    const postStore = usePostStore();
    const { getPublishedPosts, searchPosts } = postStore;
    const { posts, totalNbPosts, isReady } = storeToRefs(postStore);
    getPublishedPosts(0, 12);
    
    const paginationLength = computed(() => Math.ceil(totalNbPosts.value / 12));
    const page = ref(1);

    const title = ref('');
    const searching = ref(false);
    let typingTimer;
    const typing = () => {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            if(title.value.length == 0){
                searching.value = false;
                getPublishedPosts(0, 12);
            } else {
                searching.value = true;
                searchPosts(title.value);
            }
        }, 1000);
    }
</script>