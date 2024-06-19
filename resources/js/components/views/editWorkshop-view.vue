<template>
    <v-container>
        <v-card>
            <div v-if="isReady">
                <workshop-edit-form :workshop="workshop" :availableThemes="availableThemes" @image-updated="updateImage" :imageLoading="imageLoading"/>
            </div>
            <div class="d-flex justify-space-between align-center px-4 py-2">
                <v-dialog v-model="quitDialog" width="350">
                    <template v-slot:activator="{ props }">
                        <v-btn variant="tonal" color="error" rounded="xl" style="width:120px" :disabled="loading" prepend-icon="mdi-arrow-left-bold-hexagon-outline" v-bind="props">
                            {{ $t('Quit') }}
                        </v-btn>
                    </template>
                    <v-card :title="$t('Are you sure ?')" :text="$t('If you quit now all unsaved changes will be lost')">
                        <div class="d-flex justify-space-between pa-4">
                            <v-btn color="primary" @click="quitDialog = false">{{ $t('Stay') }}</v-btn>
                            <v-btn color="error" @click="quitConfirmed">{{ $t('Quit') }}</v-btn>
                        </div>
                    </v-card>
                </v-dialog>
                <v-btn color="success" :loading="loading" append-icon="mdi-content-save" @click="save">
                    {{ $t('Save') }}
                </v-btn>
            </div>
        </v-card>
    </v-container>
</template>
<script setup>
    import { ref, computed } from 'vue';
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    import { storeToRefs } from 'pinia';
    import { useRoute } from 'vue-router';
    import { useRouter } from 'vue-router';
    import { useI18n } from 'vue-i18n';

    let loading = ref(false);

    const workshopStore = useWorkshopStore();
    const { getWorkshop, updateWorkshop, getThemes, updateImage } = workshopStore;
    const { workshop, isReady, themes, imageLoading } = storeToRefs(workshopStore);

    const route = useRoute();
    getWorkshop(route.params.id);

    getThemes();
    const { locale } = useI18n();
    const availableThemes = computed(() => themes.value.map(theme => {
        return {
            title: locale == 'en' ? theme.title_en : theme.title_fr,
            value: theme.id
        }
    }));

    let quitDialog = ref(false);
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
</script>