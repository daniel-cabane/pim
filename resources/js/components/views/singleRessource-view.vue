<template>
    <v-container>
        <div class="pb-4 d-flex justify-space-between">
            <back-btn />
            <v-dialog>
                <template v-slot:activator="{ props: activatorProps }">
                    <v-btn variant="elevated" color="secondary" :text="$t('Tutorial')" rounded="pill" v-bind="activatorProps"/>
                </template>
                <template v-slot:default="{ isActive }">
                    <component :is="`${ressourceSlug}-tutorial`" @close="isActive.value = false"/>
                </template>
            </v-dialog>
            
        </div>
        <component :is="ressourceSlug"/>
    </v-container>
</template>
<script setup>
    import { useRoute } from 'vue-router';

    const route = useRoute();
    const ressourceSlug = route.params.slug;
</script>