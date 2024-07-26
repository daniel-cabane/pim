<template>
    <v-container v-if="isWindowSmall">
        <v-tabs v-model="tab" color="primary" class="mb-2">
            <v-tab :text="$t('Recent posts')" value="posts"></v-tab>
            <v-tab :text="$t('Upcoming') " value="workshops"></v-tab>
        </v-tabs>
        <v-tabs-window v-model="tab">
            <v-tabs-window-item value="posts">
                <v-card flat color="surface" style="flex:1;" class="mb-3 pa-4 insetCard" v-if="isReady">
                    <div class="d-flex align-start justify-end px-4">
                        <v-btn variant="text" size='large' class="mt-2" append-icon="mdi-open-in-new" to="/blog">
                            {{ $t('More') }}
                        </v-btn>
                    </div>
                    <div class="d-flex flex-wrap justify-space-around">
                        <post-home-card class="my-2" v-for="post in posts" :key="post.id" :post="post" />
                    </div>
                    <div class="d-flex justify-end">
                        <v-btn variant="text" size='large' class="mt-2" append-icon="mdi-open-in-new" to="/blog"
                            v-if="posts.length > 4">
                            {{ $t('More') }}
                        </v-btn>
                    </div>
                </v-card>
            </v-tabs-window-item>
            <v-tabs-window-item value="workshops">
                <v-card flat color="surface" class="mb-3 pa-4 insetCard">
                    <div class="d-flex align-start justify-end px-1">
                        <v-btn variant="text" size='large' class="mt-2" append-icon="mdi-open-in-new" to="/workshops">
                            {{ $t('More') }}
                        </v-btn>
                    </div>
                    <div class="d-flex flex-wrap justify-space-around">
                        <workshop-card v-for="workshop in events" :workshop="workshop" class="my-2"
                            :key="workshop.id" />
                    </div>
                    <div class="d-flex justify-end">
                        <v-btn variant="text" size='large' class="mt-2" append-icon="mdi-open-in-new" to="/workshops"
                            v-if="events.length > 2">
                            {{ $t('More') }}
                        </v-btn>
                    </div>
                </v-card>
            </v-tabs-window-item>
        </v-tabs-window>
    </v-container>
    <v-container style="display:flex;gap: 15px;" v-else>
        <v-card flat color="surface" style="flex:1;" class="mb-3 pa-4 insetCard" v-if="isReady">
            <div class="d-flex align-start justify-space-between px-4">
                <div class="pimTitleFont font-weight-thin text-captionColor" style="font-size:36px;">
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
                <v-btn variant="text" size='large' class="mt-2" append-icon="mdi-open-in-new" to="/blog"
                    v-if="posts.length > 4">
                    {{ $t('More') }}
                </v-btn>
            </div>
        </v-card>
        <v-card flat width="350" color="surface" class="mb-3 pa-4 insetCard">
            <div class="d-flex align-start justify-space-between px-1">
                <div style="width:100%;font-size:36px;" class="pimTitleFont font-weight-thin text-captionColor">
                    {{ $t('Upcoming') }}
                </div>
                <v-btn variant="text" size='large' class="mt-2" append-icon="mdi-open-in-new" to="/workshops">
                    {{ $t('More') }}
                </v-btn>
            </div>
            <workshop-card v-for="workshop in events" :workshop="workshop" class="my-2" :key="workshop.id" />
            <div class="d-flex justify-end">
                <v-btn variant="text" size='large' class="mt-2" append-icon="mdi-open-in-new" to="/workshops"
                    v-if="events.length > 2">
                    {{ $t('More') }}
                </v-btn>
            </div>
        </v-card>
    </v-container>
</template>
<script setup>
    import { ref, computed } from "vue";
    import { usePostStore } from '@/stores/usePostStore';
    import { useEventStore } from '@/stores/useEventStore';
    import { storeToRefs } from 'pinia';
    import { useDisplay } from 'vuetify';

    const postStore = usePostStore();
    const { getPublishedPosts } = postStore;
    const { posts, isReady } = storeToRefs(postStore);
    getPublishedPosts();

    const eventStore = useEventStore();
    const { getUpcomingEvents } = eventStore;
    const { events } = storeToRefs(eventStore);
    getUpcomingEvents();

    const { name } = useDisplay();
    const isWindowSmall = computed(() => name.value == 'xs' || name.value == 'sm');
    const tab = ref('posts');
</script>
<style scoped>
    .insetCard {
        background-color: red;
        box-shadow: 12px 12px 12px rgba(0, 0, 0, 0.1) inset, -10px -10px 10px white inset;
    }
</style>