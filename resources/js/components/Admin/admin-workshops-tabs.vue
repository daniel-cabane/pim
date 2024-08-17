<template>
    <v-card class="d-flex" :loading="isLoading" v-if="isReady">
        <v-tabs v-model="tab" color="primary" direction="vertical">
            <v-tab prepend-icon="mdi-puzzle" :text="$t('Workshops')" value="workshops" />
            <v-tab prepend-icon="mdi-shape-plus" :text="$t('Themes')" value="themes"></v-tab>
        </v-tabs>

        <v-tabs-window style="flex:1" v-model="tab">
            <v-tabs-window-item value="workshops">
                <div class="pa-4 d-flex flex-wrap">
                    <workshop-card v-for="workshop in workshops" :workshop="workshop" class="ma-2"
                        :key="workshop.id" />
                </div>
            </v-tabs-window-item>
            <v-tabs-window-item value="themes">
                <div class="d-flex justify-end pt-3 pr-3">
                    <v-dialog max-width="500">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn color="primary" variant="outlined" :disabled="isLoading" v-bind="activatorProps">
                                {{ $t('New theme') }}
                            </v-btn>
                        </template>
                        <template v-slot:default="{ isActive }">
                            <v-card :title="$t('New theme')">
                            <v-card-text>
                                <v-text-field variant="outlined" label="English" v-model="title_en"/>
                                <v-text-field variant="outlined" label="Français" v-model="title_fr"/>
                            </v-card-text>
                            <div class="d-flex justify-end pa-4">
                                <v-btn variant="tonal" color="error" :text="$t('Close')" class="mr-2" @click="isActive.value = false"/>
                                <v-btn color="primary" :text="$t('Create')" @click="createTheme(title_en, title_fr);isActive.value=false"/>
                            </div>
                            </v-card>
                        </template>
                        </v-dialog>
                </div>
                <div class="pa-4 d-flex flex-wrap">
                    <admin-theme-card v-for="theme in themes" :theme="theme" :isLoading="isLoading" class="ma-2" @updateTheme="updateTheme" @deleteTheme="deleteTheme"/>
                </div>
            </v-tabs-window-item>
        </v-tabs-window>
    </v-card>
</template>
<script setup>
    import { ref } from "vue";
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    import { useThemeStore } from '@/stores/useThemeStore';
    import { storeToRefs } from 'pinia';

    const workshopStore = useWorkshopStore();
    const { adminGetAllWorkshops } = workshopStore;
    const { workshops, isReady, isLoading } = storeToRefs(workshopStore);
    adminGetAllWorkshops();

    const themeStore = useThemeStore();
    const { getThemesAdmin, updateTheme, createTheme, deleteTheme } = themeStore;
    const { themes } = storeToRefs(themeStore);
    getThemesAdmin();

    const tab = ref('workshops');

    const title_en = ref('');
    const title_fr = ref('');
</script>