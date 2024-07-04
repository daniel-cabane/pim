<template>
    <v-dialog v-model="dialog" fullscreen :scrim="false" transition="dialog-bottom-transition">
        <template v-slot:activator="{ props }">
            <v-btn class="mr-2" color="primary" icon="mdi-eye" v-bind="props" v-if="activator == 'icon'" />
            <v-btn class="mr-2" color="primary" apprendIcon="mdi-eye" v-bind="props" v-if="activator == 'button'">
                {{ $t('preview') }}
            </v-btn>
            <v-list-item v-bind="props" v-if="activator == 'listItem'">
                <template v-slot:prepend>
                    <v-icon icon="mdi-eye" color="primary"></v-icon>
                </template>
                <v-list-item-title>{{ $t('Preview post') }}</v-list-item-title>
            </v-list-item>
        </template>
        <v-card>
            <v-toolbar dark color="secondary">
                <v-toolbar-title>
                    {{ $t('Post preview') }}
                </v-toolbar-title>
                <v-spacer/>
                <v-btn icon dark @click="dialog = false">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-toolbar>
            <div class="px-5 py-2">
                <post-header :post="post" />
                <v-divider class="my-2" />
                <div class="mt-4" v-html="post.post" />
            </div>
        </v-card>
    </v-dialog>
</template>
<script setup>
    import { ref } from "vue";

    const props = defineProps({ post: Object, activator: String });

    const dialog = ref(false);
</script>