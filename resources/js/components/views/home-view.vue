<template>
    <v-container style="display:flex;gap: 15px;">
        <v-card flat color="surface" style="flex:1;box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.5);" class="mb-3 pa-4" v-if="isReady">
            <div class="d-flex align-start justify-space-between px-4">
                <div class="pimTitleFont font-weight-thin text-captionColor mb-4" style="font-size:40px">
                    {{ $t("Recent posts") }}
                </div>
                <v-btn variant="text" size='large' class="mt-2" append-icon="mdi-open-in-new" to="/blog">
                    {{ $t('More') }}
                </v-btn>
            </div>
            <div class="d-flex flex-wrap justify-space-around">
                <post-home-card class="my-2" v-for="post in posts" :key="post.id" :post="post" />
            </div>
            <div class="d-flex justify-end">
                <v-btn variant="text" size='large' class="mt-2" append-icon="mdi-open-in-new" to="/blog">
                    {{ $t('More') }}
                </v-btn>
            </div>
        </v-card>
        <v-card flat width="350" color="surface" style="box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.5);"
            class="mb-3 pa-4">
            <div class="d-flex align-start justify-space-between px-1">
                <div style="width:100%;font-size:40px;" class="pimTitleFont font-weight-thin text-captionColor mb-3">
                    {{ $t('Upcoming') }}
                </div>
                <v-btn variant="text" size='large' class="mt-2" append-icon="mdi-open-in-new" to="/workshops">
                    {{ $t('More') }}
                </v-btn>
            </div>
            <workshop-card v-for="workshop in events" :workshop="workshop" class="my-2" :key="workshop.id" />
            <div class="d-flex justify-end">
                <v-btn variant="text" size='large' class="mt-2" append-icon="mdi-open-in-new" to="/workshops">
                    {{ $t('More') }}
                </v-btn>
            </div>
        </v-card>
    </v-container>
</template>
<script setup>
    import { usePostStore } from '@/stores/usePostStore';
    import { useEventStore } from '@/stores/useEventStore';
    import { storeToRefs } from 'pinia';

    const postStore = usePostStore();
    const { getPublishedPosts } = postStore;
    const { posts, isReady } = storeToRefs(postStore);
    getPublishedPosts();

    const eventStore = useEventStore();
    const { getUpcomingEvents } = eventStore;
    const { events } = storeToRefs(eventStore);
    getUpcomingEvents();
</script>