<template>
    <v-card>
        <v-card-text>
            <!-- Tournament Settings Section -->
            <div class="mb-6">
                <div class="text-h6 text-captionColor mb-4">Tournament Settings</div>
                
                <v-form @submit.prevent="saveTournamentSettings">
                    <v-row>
                        <v-col cols="12" md="6" class="pb-0">
                            <v-text-field
                                v-model="tournamentName"
                                label="Tournament Name"
                                variant="outlined"
                                dense
                                :loading="savingSettings"
                                @blur="saveTournamentSettings"
                            />
                        </v-col>
                        <v-col cols="12" md="6" class="pb-0">
                            <v-select
                                v-model="pairingSystem"
                                label="Pairing System"
                                :items="pairingOptions"
                                item-title="label"
                                item-value="value"
                                variant="outlined"
                                dense
                                :loading="savingSettings"
                                @update:model-value="saveTournamentSettings"
                            />
                        </v-col>
                    </v-row>
                    
                    <v-row>
                        <v-col cols="12">
                            <v-textarea
                                v-model="tournamentDescription"
                                label="Description"
                                variant="outlined"
                                dense
                                rows="3"
                                :loading="savingSettings"
                                @blur="saveTournamentSettings"
                            />
                        </v-col>
                    </v-row>
                </v-form>
            </div>

            <div class="text-h6 text-captionColor mb-3">Manage Organisers</div>
            
            <!-- Search and Add Organiser -->
            <div class="mb-8">
                <v-row>
                    <v-col cols="12" md="8">
                        <v-autocomplete
                            variant="outlined"
                            v-model="selectedOrganiser"
                            v-model:search="searchQuery"
                            :items="searchResults"
                            item-title="name"
                            item-value="id"
                            placeholder="Search by name or email (min 3 characters)"
                            :loading="searchingUsers"
                            no-filter
                            clearable
                            hide-details
                            @update:search="onSearchChange"
                        >
                            <template v-slot:item="{ props, item }">
                                <v-list-item
                                    v-bind="props"
                                    :subtitle="`${item.raw.email}`"
                                />
                            </template>
                        </v-autocomplete>
                    </v-col>
                    <v-col cols="12" md="4" class="d-flex align-end">
                        <v-btn
                            color="primary"
                            size="x-large"
                            :disabled="!selectedOrganiser || addingOrganiser"
                            :loading="addingOrganiser"
                            @click="addNewOrganiser"
                            class="w-100"
                        >
                            <v-icon left>mdi-plus</v-icon>
                            Add Organiser
                        </v-btn>
                    </v-col>
                </v-row>
            </div>

            <!-- Organisers List -->
            <div>
                <div v-if="organisersData && organisersData.length > 0" class="d-flex flex-wrap gap-3">
                    <v-card
                        v-for="organiser in organisersData"
                        :key="organiser.id"
                        class="flex-grow-0 mr-2"
                        style="min-width: 300px; max-width: 400px;"
                        color="background"
                    >
                        <v-card-text>
                            <div class="d-flex align-center gap-3">
                                <v-avatar color="primary" size="40" class="mr-4">
                                    <span class="text-white text-subtitle-2 font-weight-bold">{{ organiser.name.charAt(0) }}</span>
                                </v-avatar>
                                <div class="flex-grow-1">
                                    <p class="mb-0 font-weight-bold">{{ organiser.name }}</p>
                                    <p class="mb-0 text-caption text-captionColor">{{ organiser.email }}</p>
                                </div>
                                <div v-if="organiser.id !== tournamentCreatedBy" class="flex-shrink-0">
                                    <v-btn
                                        icon="mdi-close-circle"
                                        variant="text"
                                        color="error"
                                        :loading="removingOrganiser === organiser.id"
                                        @click="removeOrganiserConfirm(organiser)"
                                    />
                                </div>
                                <v-chip v-else size="small" variant="tonal" color="success">
                                    Creator
                                </v-chip>
                            </div>
                        </v-card-text>
                    </v-card>
                </div>
                <div v-else class="text-center py-6">
                    <p class="text-captionColor">No additional organisers assigned</p>
                </div>
            </div>
        </v-card-text>
    </v-card>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="showDeleteDialog" max-width="400">
        <v-card>
            <v-card-title class="text-h6">Remove Organiser</v-card-title>
            <v-card-text>
                Are you sure you want to remove <strong>{{ organiserToDelete?.name }}</strong> as an organiser?
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                    color="primary"
                    variant="tonal"
                    @click="showDeleteDialog = false"
                >
                    Cancel
                </v-btn>
                <v-btn
                    color="error"
                    variant="flat"
                    :loading="removingOrganiser === organiserToDelete?.id"
                    @click="confirmDelete"
                >
                    Remove
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script setup>
    import { ref, onMounted } from 'vue';
    import { useRouter } from 'vue-router';
    import { useTournamentStore } from '@/stores/useTournamentStore';
    import useAPI from '@/composables/useAPI';

    const router = useRouter();
    const props = defineProps({
        tournament: {
            type: Object,
            required: true
        },
        slug: {
            type: String,
            required: true
        }
    });

    const tournamentStore = useTournamentStore();
    const { get: apiGet } = useAPI();

    // Tournament Settings
    const tournamentName = ref('');
    const tournamentDescription = ref('');
    const pairingSystem = ref('');
    const initialName = ref('');
    const initialDescription = ref('');
    const initialFormat = ref('');
    const pairingOptions = [
        { label: 'Swiss', value: 'swiss' },
        { label: 'Round Robin', value: 'round_robin' },
        { label: 'Knockout', value: 'knockout' }
    ];
    const savingSettings = ref(false);

    // Organisers Management
    const searchQuery = ref('');
    const selectedOrganiser = ref(null);
    const searchResults = ref([]);
    const searchingUsers = ref(false);
    const organisersData = ref([]);
    const addingOrganiser = ref(false);
    const removingOrganiser = ref(null);
    const showDeleteDialog = ref(false);
    const organiserToDelete = ref(null);
    let searchTimeout = null;

    const tournamentCreatedBy = props.tournament?.created_by || null;

    const saveTournamentSettings = async () => {
        if (!tournamentName.value.trim()) return;
        
        // Check if any values have changed
        const hasChanges = 
            tournamentName.value !== initialName.value ||
            tournamentDescription.value !== initialDescription.value ||
            pairingSystem.value !== initialFormat.value;
        
        if (!hasChanges) return;
        
        savingSettings.value = true;
        try {
            await tournamentStore.updateTournament(props.slug, {
                name: tournamentName.value,
                description: tournamentDescription.value,
                format: pairingSystem.value,
            });
            
            // Update initial values after successful save
            initialName.value = tournamentName.value;
            initialDescription.value = tournamentDescription.value;
            initialFormat.value = pairingSystem.value;
            
            history.replaceState(null, null, `/tournaments/${props.tournament.slug}/admin`);
        } catch (error) {
            console.error('Error saving tournament settings:', error);
        } finally {
            savingSettings.value = false;
        }
    };

    const onSearchChange = (query) => {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        if (query.length < 3) {
            searchResults.value = [];
            return;
        }

        searchTimeout = setTimeout(async () => {
            searchingUsers.value = true;
            try {
                const results = await apiGet(`/api/tournaments/${props.slug}/search-users?query=${encodeURIComponent(query)}`);
                searchResults.value = results;
            } catch (error) {
                console.error('Error searching users:', error);
                searchResults.value = [];
            } finally {
                searchingUsers.value = false;
            }
        }, 400);
    };

    const addNewOrganiser = async () => {
        if (!selectedOrganiser.value) return;

        addingOrganiser.value = true;
        try {
            await tournamentStore.addOrganiser(props.slug, selectedOrganiser.value);
            searchQuery.value = '';
            selectedOrganiser.value = null;
            searchResults.value = [];
            await loadOrganisers();
        } catch (error) {
            console.error('Error adding organiser:', error);
        } finally {
            addingOrganiser.value = false;
        }
    };

    const removeOrganiserConfirm = (organiser) => {
        organiserToDelete.value = organiser;
        showDeleteDialog.value = true;
    };

    const confirmDelete = async () => {
        if (!organiserToDelete.value) return;

        removingOrganiser.value = organiserToDelete.value.id;
        try {
            await tournamentStore.removeOrganiser(props.slug, organiserToDelete.value.id);
            await loadOrganisers();
            showDeleteDialog.value = false;
            organiserToDelete.value = null;
        } catch (error) {
            console.error('Error removing organiser:', error);
        } finally {
            removingOrganiser.value = null;
        }
    };

    const loadOrganisers = async () => {
        try {
            const result = await tournamentStore.getOrganisers(props.slug);
            organisersData.value = result;
        } catch (error) {
            console.error('Error loading organisers:', error);
        }
    };

    onMounted(() => {
        tournamentName.value = props.tournament?.name || '';
        tournamentDescription.value = props.tournament?.description || '';
        pairingSystem.value = props.tournament?.format || '';
        
        // Set initial values for change detection
        initialName.value = tournamentName.value;
        initialDescription.value = tournamentDescription.value;
        initialFormat.value = pairingSystem.value;
        
        loadOrganisers();
    });

    // Expose methods to parent if needed
    defineExpose({
        loadOrganisers
    });
</script>