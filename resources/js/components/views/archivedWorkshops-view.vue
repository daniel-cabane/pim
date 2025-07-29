<template>
    <v-container v-if="isReady">
        <archived-workshop-preview 
            class="my-2" 
            v-for="workshop in workshops"
            :workshop="workshop"
            @see="seeWorkshop"
            @delete="prepareDeleteWorkshop"
        />
        <v-dialog v-model="seeWorkshopDialog" width="650">
            <v-card>
                <archived-workshop-display :workshop="focusedWorkshop" @duplicate="handleDuplicateWorkshop"/>
            </v-card>
        </v-dialog>
        <v-dialog v-model="deleteDialog" width="450">
            <v-card :title="$t('Delete workshop')">
                    <v-card-text>
                        <div>
                            {{ $t('Are you sure you want to delete') }} <b>{{ focusedWorkshop.language == 'fr' ? focusedWorkshop.title.fr : focusedWorkshop.title.en }}</b> ?
                        </div>
                        <v-checkbox v-model="checkDelete" color="error" :label="$t('Yes I\'m sure')"/>
                    </v-card-text>
                    <div style="display:flex;justify-content:flex-end;" class="pa-3">
                        <v-btn variant="tonal" class="mr-3" color="primary" :disabled="isLoading" @click="deleteDialog = false">
                            {{ $t('Cancel') }}
                        </v-btn>
                        <v-btn color="error" :disabled="!checkDelete" :loading="isLoading" @click="handleDeleteWorkshop">
                            {{ $t('Delete') }}
                        </v-btn>
                    </div>
                </v-card>
        </v-dialog>
    </v-container>
</template>
<script setup>
    import { ref } from "vue";
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    import { storeToRefs } from 'pinia';
    import { useRouter } from 'vue-router';

    const router = useRouter();

    const workshopStore = useWorkshopStore();
    const { getArchivedWorkshops, duplicateWorkshop, deleteWorkshop } = workshopStore;
    const { workshops, isLoading, isReady } = storeToRefs(workshopStore);
    
    getArchivedWorkshops();

    const focusedWorkshop = ref(null);
    const seeWorkshopDialog = ref(false);
    const seeWorkshop = workshop => {
        focusedWorkshop.value = workshop;
        seeWorkshopDialog.value = true;
    }
    const handleDuplicateWorkshop = async id => {
        const res = await duplicateWorkshop(id);
        console.log(res);
        if(res){
            router.push(`/workshops/${res}`);
        }
    }

    const deleteDialog = ref(false);
    const checkDelete = ref(false);
    const prepareDeleteWorkshop = workshop => {
        focusedWorkshop.value = workshop;
        deleteDialog.value = true;
    }
    const handleDeleteWorkshop = async () => {
        const res = await deleteWorkshop(focusedWorkshop.value.id);
        if(res){
            deleteDialog.value = false;
        }
    }
</script>