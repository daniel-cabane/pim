<template>
    <v-card width="100%" class="pa-3">
        <back-btn class="mb-5"/>
        <post-header :post="post"/>
        <v-divider class="my-2"/>
        <div class="mt-4" v-html="post.post"/>
        <div class="d-flex align-center mt-4">
            <back-btn/>
            <v-spacer/>
            <post-edit-menu :post="post" :editing="false" v-if="post.editable"/>
        </div>
    </v-card>
</template>
<script setup>
    const props = defineProps({ post: Object });
</script>