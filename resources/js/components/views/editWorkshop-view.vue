<template>
    <v-container>
        <v-card>
            <div>
                <!-- <workshop-edit-form :workshop="workshop" :availableThemes="availableThemes" @image-updated="updateImage"
                    @image-deleted="deleteImage" :imageLoading="imageLoading" /> -->
                <workshop-edit-form />
            </div>
            <div class="d-flex justify-space-between align-center px-4 py-2">
                <v-dialog v-model="quitDialog" width="350">
                    <template v-slot:activator="{ props }">
                        <v-btn variant="tonal" color="error" rounded="xl" style="width:120px" :disabled="loading"
                            prepend-icon="mdi-arrow-left-bold-hexagon-outline" v-bind="props">
                            {{ $t('Quit') }}
                        </v-btn>
                    </template>
                    <v-card :title="$t('Are you sure ?')"
                        :text="$t('If you quit now all unsaved changes will be lost')">
                        <div class="d-flex justify-space-between pa-4">
                            <v-btn color="primary" variant="tonal" @click="quitDialog = false">{{ $t('Stay') }}</v-btn>
                            <v-btn color="error" variant="tonal" @click="quitConfirmed">{{ $t('Quit') }}</v-btn>
                        </div>
                    </v-card>
                </v-dialog>
                <div>
                    <v-dialog width="300" v-model="archiveDialog">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn color="error" class="mr-2 mt-1" append-icon="mdi-delete" v-bind="activatorProps">
                                {{ $t('Archive') }}
                            </v-btn>
                        </template>
                        <template v-slot:default="{ isActive }">
                            <v-card :title="t('Delete workshop')">
                                <v-card-text>
                                    {{ $t('Are you sure you wish to archive this workshop ?') }}
                                </v-card-text>
                                <div style="display:flex;justify-content:flex-end;" class="pa-3">
                                    <v-btn variant="text" class="mr-3" color="primary" :disabled="archiveLoading"
                                        @click="archiveDialog = false">
                                        {{ $t('Cancel') }}
                                    </v-btn>
                                    <v-btn color="error" :loading="archiveLoading" @click="proceedArchive">
                                        {{ $t('Archive') }}
                                    </v-btn>
                                </div>
                            </v-card>
                        </template>
                    </v-dialog>
                    <v-btn color="success" style="min-width:200px;" theme="dark" :loading="loading"
                        append-icon="mdi-content-save" @click="save">
                        {{ $t('Save') }}
                    </v-btn>
                </div>
            </div>
        </v-card>
    </v-container>
</template>
<script setup>
    import { ref } from 'vue';
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    // import { storeToRefs } from 'pinia';
    import { useRouter } from 'vue-router';
    // import { useRoute } from 'vue-router';
    // import { useI18n } from 'vue-i18n';

    let loading = ref(false);
    const archiveDialog = ref(false);
    const archiveLoading = ref(false);

    const workshopStore = useWorkshopStore();
    // const { getWorkshop, updateWorkshop, getThemes, updateImage, deleteImage, archiveWorkshop } = workshopStore;
    // const { workshop, isReady, themes, imageLoading } = storeToRefs(workshopStore);
    const { updateWorkshop, archiveWorkshop } = workshopStore;
    // const { isReady } = storeToRefs(workshopStore);

    // const route = useRoute();
    // getWorkshop(route.params.id);

    // getThemes();
    // const { locale, t } = useI18n();
    // const availableThemes = computed(() => themes.value.map(theme => {
    //     return {
    //         title: locale == 'en' ? theme.title_en : theme.title_fr,
    //         value: theme.id
    //     }
    // }));

    const quitDialog = ref(false);
    const router = useRouter();
    const quitConfirmed = () => {
        quitDialog.value = false;
        router.go(-1);
    }

    const save = async () => {
        loading.value = true;
        await updateWorkshop();
        loading.value = false;
    }

    const proceedArchive = async () => {
        await archiveWorkshop();
        router.push('/myWorkshops');
    }
</script>