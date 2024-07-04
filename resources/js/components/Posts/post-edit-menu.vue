<template>
    <v-menu location="top">
        <template v-slot:activator="{ props }">
            <v-btn :loading="loading" icon="mdi-dots-vertical" v-bind="props"></v-btn>
        </template>
        <v-list>
            <delete-post-dialog :post="post" />
            <v-divider />
            <v-list-item @click="emit('updatePostStatus', 'submitted')" v-if="post.status == 'draft'">
                <template v-slot:prepend>
                    <v-icon icon="mdi-file-eye" color="primary"></v-icon>
                </template>
                <v-list-item-title>{{ $t('Submit post') }}</v-list-item-title>
            </v-list-item>
            <v-list-item @click="emit('updatePostStatus', 'draft')" v-if="post.status == 'submitted'">
                <template v-slot:prepend>
                    <v-icon icon="mdi-file-hidden"></v-icon>
                </template>
                <v-list-item-title>{{ $t('Retract post') }}</v-list-item-title>
            </v-list-item>
            <v-divider />
            <preview-post-dialog :post="post" activator="listItem" />
        </v-list>
    </v-menu>
</template>
<script setup>
    const props = defineProps({ post: Object, loading: Boolean });
    const emit = defineEmits(['updatePostStatus']);
</script>