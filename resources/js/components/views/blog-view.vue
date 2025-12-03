<template>
    <v-container>
        <v-text-field 
            variant="outlined" 
            hide-details
            class="elevation-5"
            v-model="title"
            label="Search by title"
            clearable
        />
        <div class="d-flex flex-wrap justify-space-around mt-2 ga-2" v-if="isReady">
            <post-home-card
                v-for="post in displayedPosts"
                :key="post.id"
                :post="post" 
            />
        </div>
        <!-- <div>
            <v-pagination 
                v-model="page"
                :length="paginationLength"
                @update:modelValue="logme"
            />
        </div> -->
    </v-container>
</template>
<script setup>
    import { ref, computed } from "vue";
    import { usePostStore } from '@/stores/usePostStore';
    import { storeToRefs } from 'pinia';
    import { useI18n } from 'vue-i18n';

    const { locale } = useI18n();

    const postStore = usePostStore();
    const { getBlog } = postStore;
    const { posts, isReady } = storeToRefs(postStore);
    getBlog(locale.value);
    
    // const paginationLength = computed(() => Math.ceil(totalNbPosts.value / 12));
    const page = ref(1);

    const title = ref('');
    // const logme = () => console.log(page);

    const displayedPosts = computed(() => {
        const q = title.value?.trim().toLowerCase();
        if (!q) return posts.value;
        return posts.value.filter(p => {
            // handle title as string or localized object
            let text = '';
            if (!p) return false;
            if (typeof p.title === 'string') text = p.title;
            else if (p.title && typeof p.title === 'object') text = p.title[locale.value] ?? Object.values(p.title)[0] ?? '';
            return text.toString().toLowerCase().includes(q);
        });
    });
</script>