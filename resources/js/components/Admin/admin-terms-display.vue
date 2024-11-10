<template>
    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:15px;">
        <v-card v-for="term in terms" :key="term.id">
            <v-card-title class="d-flex justify-space-between align-center">
                <span>
                    {{ $t('Term') + ' ' + term.nb }}
                </span>
                <span>
                    <v-icon size="x-small" color="primary" @click="showEditTermDialog(term)">
                        mdi-pencil
                    </v-icon>
                    <v-icon size="x-small" color="error" @click="prepareDeleteTerm(term.id)">
                        mdi-delete
                    </v-icon>
                </span>
            </v-card-title>
            <v-card-text>
                {{ formatDate(term.start_date) }}
                →
                {{ formatDate(term.finish_date) }}
            </v-card-text>
        </v-card>
        <v-dialog max-width="350" v-model="addDialog">
            <template v-slot:activator="{ props: activatorProps }">
                <v-btn v-bind="activatorProps" color="primary" icon="mdi-plus" variant="elevated" />
            </template>
            <v-card width="350" :title="$t('New term')">
                <v-card-text class="pt-3">
                    <v-select v-model="nb" :items="[1,2,3]" variant="outlined" label="Nb" />
                    <v-text-field v-model="startDate" type="date" :label="$t('Start date')" variant="outlined" :disabled="isLoading" />
                    <v-text-field v-model="finishDate" type="date" :label="$t('Finish date')" variant="outlined" :disabled="isLoading" />
                </v-card-text>
                <div class="px-4 d-flex pb-3">
                    <v-spacer />
                    <v-btn color="error" class="mr-2" variant="tonal" @click="addDialog=false" :disabled="isLoading">
                        {{ $t('Cancel') }}
                    </v-btn>
                    <v-btn color="primary" @click="handleCreateTerm" :loading="isLoading">
                        {{ $t('Submit') }}
                    </v-btn>
                </div>
            </v-card>
        </v-dialog>
        <v-dialog width="350" v-model="deleteDialog">
            <v-card width="350" prepend-icon="mdi-delete" text="Are you sure you want to delete this term" title="Delete Term">
                <template v-slot:actions>
                    <v-spacer />
                    <v-btn color="primary" variant="tonal" :disabled="isLoading" :text="$t('No')" @click="deleteDialog = false" />
                    <v-btn color="error" variant="flat" :text="$t('Yes')" :loading="isLoading" @click="handleDeleteTerm" />
                </template>
            </v-card>
        </v-dialog>
        <v-dialog width="350" v-model="editDialog">
            <v-card width="350" title="Edit Term">
                <v-card-text>
                    <v-text-field v-model="focusedTerm.start_date" type="date" :label="$t('Start date')" variant="outlined" :disabled="isLoading" />
                    <v-text-field v-model="focusedTerm.finish_date" type="date" :label="$t('Finish date')" variant="outlined" :disabled="isLoading" />
                </v-card-text>
                <template v-slot:actions>
                    <v-spacer />
                    <v-btn color="error" variant="tonal" :disabled="isLoading" :text="$t('Cancel')" @click="editDialog = false" />
                    <v-btn color="primary" variant="flat" :text="$t('Confirm')" :loading="isLoading" @click="handleEditTerm" />
                </template>
            </v-card>
        </v-dialog>
    </div>
</template>
<script setup>
    import { ref } from "vue";
    import { useEventStore } from '@/stores/useEventStore';
    import { storeToRefs } from 'pinia';

    const eventStore = useEventStore();
    const { getTerms, createTerm, deleteTerm, editTerm } = eventStore;
    const { terms, isLoading } = storeToRefs(eventStore);
    getTerms();

    const nb = ref(null);
    const startDate = ref(null);
    const finishDate = ref(null);
    const handleCreateTerm = async () => {
        if(nb && startDate && finishDate){
            await createTerm({nb: nb.value, start_date: startDate.value, finish_date: finishDate.value});
            addDialog.value = false;
        }
    }
    const formatDate = date => {
        var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2)
            month = '0' + month;
        if (day.length < 2)
            day = '0' + day;

        return [day, month, year].join('-');
    }
    const addDialog = ref(false);
    const deleteDialog = ref(false);
    const editDialog = ref(false);
    const focusedTerm = ref(null);
    const focusedId = ref(null);
    const prepareDeleteTerm = id => {
        focusedId.value = id;
        deleteDialog.value = true;
    }
    const handleDeleteTerm = async () => {
        await deleteTerm(focusedId.value);
        focusedId.value = null;
        deleteDialog.value = false;
    }
    const showEditTermDialog = term => {
        focusedTerm.value = term;
        editDialog.value = true;
    }
    const handleEditTerm = async () => {
        await editTerm(focusedTerm.value);
        editDialog.value = false;
    }
</script>