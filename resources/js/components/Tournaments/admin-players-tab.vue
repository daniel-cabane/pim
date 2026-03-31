<template>
    <v-card>
        <v-card-title class="d-flex justify-end align-center">
            <v-btn
                color="primary"
                size="small"
                append-icon="mdi-plus"
                @click="showAddPlayerDialog = true"
            >
                {{ $t('Add Player') }}
            </v-btn>
        </v-card-title>

        <v-data-table
            :headers="headers"
            :items="players"
            :loading="loadingPlayers"
            density="compact"
            class="elevation-0"
            no-data-text="No players yet"
        >
            <template v-slot:item.name="{ item }">
                <div>
                    <div>{{ item.name }}</div>
                    <div class="text-caption text-captionColor">{{ item.email }}</div>
                </div>
            </template>

            <template v-slot:item.wdl="{ item }">
                <span :class="item.pivot?.banned ? 'text-error font-weight-bold' : ''">
                    {{ item.pivot?.banned ? $t('BANNED') : `${item.pivot?.wins || 0}-${item.pivot?.draws || 0}-${item.pivot?.losses || 0 }` }}
                </span>
            </template>

            <template v-slot:item.actions="{ item }">
                 <v-menu>
                    <template v-slot:activator="{ props }">
                    <v-btn icon="mdi-dots-vertical" variant="text" v-bind="props"></v-btn>
                    </template>

                    <v-list>
                    <v-list-item @click="editPlayer(item)">
                        <template v-slot:prepend>
                            <v-icon icon="mdi-pencil" color="primary"/>
                        </template>
                        <v-list-item-title>{{ $t('Edit') }}</v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="removePlayerConfirm(item)">
                        <template v-slot:prepend>
                            <v-icon icon="mdi-close-circle" color="error"/>
                        </template>
                        <v-list-item-title>{{ $t('Remove') }}</v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="toggleBanPlayer(item)">
                        <template v-slot:prepend>
                            <v-icon icon="mdi-cancel" :color="item.pivot?.banned ? 'primary' : 'warning'"/>
                        </template>
                        <v-list-item-title>{{ item.pivot?.banned ? $t('Unban') : $t('Ban') }}</v-list-item-title>
                    </v-list-item>
                    </v-list>
                </v-menu>
            </template>
        </v-data-table>
    </v-card>

    <!-- Add Player Dialog -->
    <v-dialog v-model="showAddPlayerDialog" max-width="500">
        <v-card>
            <v-card-title>{{ $t('Add Player') }}</v-card-title>
            <v-card-text>
                <v-autocomplete
                    v-model="selectedPlayer"
                    v-model:search="playerSearchQuery"
                    :items="playerSearchResults"
                    item-title="name"
                    item-value="id"
                    :loading="searchingPlayers"
                    :label="$t('Search by name or email')"
                    no-filter
                    clearable
                    hide-details="auto"
                    @update:search="onPlayerSearchChange"
                >
                    <template v-slot:item="{ props, item }">
                        <v-list-item
                            v-bind="props"
                            :subtitle="`${item.raw.email}`"
                        />
                    </template>
                </v-autocomplete>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                    color="error"
                    variant="tonal"
                    @click="showAddPlayerDialog = false"
                >
                    {{ $t('Cancel') }}
                </v-btn>
                <v-btn
                    color="primary"
                    variant="flat"
                    class="w-25"
                    :disabled="!selectedPlayer || addingPlayer"
                    :loading="addingPlayer"
                    @click="addPlayer"
                >
                    {{ $t('Add') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>

    <!-- Remove Player Dialog -->
    <v-dialog v-model="showRemoveDialog" max-width="400">
        <v-card>
            <v-card-title>Remove Player</v-card-title>
            <v-card-text>
                Are you sure you want to remove <strong>{{ playerToRemove?.name }}</strong> from the tournament?
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                    color="primary"
                    variant="tonal"
                    @click="showRemoveDialog = false"
                >
                    {{ $t('Cancel') }}
                </v-btn>
                <v-btn
                    color="error"
                    variant="flat"
                    :loading="removingPlayer === playerToRemove?.id"
                    @click="confirmRemove"
                >
                    {{ $t('Remove') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>

    <v-dialog v-model="editRatingDialog" max-width="400">
        <v-card>
            <v-card-title>Edit {{ focusedPlayer.name }}'s rating</v-card-title>
            <v-card-text>
                <v-text-field
                    v-model="focusedPlayer.pivot.rating"
                    label="Rating"
                    variant="outlined"
                />
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                    color="error"
                    variant="tonal"
                    @click="editRatingDialog = false"
                >
                    {{ $t('Cancel') }}
                </v-btn>
                <v-btn
                    color="primary"
                    variant="flat"
                    @click="submitNewRating"
                >
                    {{ $t('Update') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script setup>
    import { ref, computed, onMounted } from 'vue';
    import { useTournamentStore } from '@/stores/useTournamentStore';
    import useAPI from '@/composables/useAPI';
    import { useI18n } from 'vue-i18n';

    const { t } = useI18n();

    const props = defineProps({
        tournament: {
            type: Object,
            required: true
        },
        slug: {
            type: String,
            required: false
        }
    });

    const tournamentStore = useTournamentStore();
    const { get: apiGet } = useAPI();

    const headers = computed( () => [
        { title: t('Player'), key: 'name' },
        { title: t('Rating'), key: 'pivot.rating', align: 'center' },
        { title: 'Points', key: 'pivot.score', align: 'center' },
        { title: t('W-D-L'), key: 'wdl', align: 'center', sortable: false },
        { title: 'Actions', key: 'actions', align: 'center', sortable: false, width: '100px' }
    ]);

    const players = ref([]);
    const loadingPlayers = ref(false);

    // Add Player Dialog
    const showAddPlayerDialog = ref(false);
    const selectedPlayer = ref(null);
    const playerSearchQuery = ref('');
    const playerSearchResults = ref([]);
    const searchingPlayers = ref(false);
    const addingPlayer = ref(false);
    let playerSearchTimeout = null;

    // Remove Player Dialog
    const showRemoveDialog = ref(false);
    const playerToRemove = ref(null);
    const removingPlayer = ref(null);

    // Ban Player
    const banningPlayer = ref(null);

    const onPlayerSearchChange = (query) => {
        if (playerSearchTimeout) {
            clearTimeout(playerSearchTimeout);
        }

        if (query.length < 3) {
            playerSearchResults.value = [];
            return;
        }

        playerSearchTimeout = setTimeout(async () => {
            searchingPlayers.value = true;
            try {
                const results = await apiGet(`/api/tournaments/${props.tournament.slug}/search-users?query=${encodeURIComponent(query)}`);
                playerSearchResults.value = results;
            } catch (error) {
                console.error('Error searching players:', error);
                playerSearchResults.value = [];
            } finally {
                searchingPlayers.value = false;
            }
        }, 400);
    };

    const addPlayer = async () => {
        if (!selectedPlayer.value) return;

        addingPlayer.value = true;
        try {
            await tournamentStore.addPlayer(props.tournament.slug, selectedPlayer.value);
            showAddPlayerDialog.value = false;
            selectedPlayer.value = null;
            playerSearchQuery.value = '';
            playerSearchResults.value = [];
            await loadPlayers();
        } catch (error) {
            console.error('Error adding player:', error);
        } finally {
            addingPlayer.value = false;
        }
    };

    const removePlayerConfirm = (player) => {
        playerToRemove.value = player;
        showRemoveDialog.value = true;
    };

    const confirmRemove = async () => {
        if (!playerToRemove.value) return;

        removingPlayer.value = playerToRemove.value.id;
        try {
            await tournamentStore.removePlayer(props.tournament.slug, playerToRemove.value.id);
            await loadPlayers();
            showRemoveDialog.value = false;
            playerToRemove.value = null;
        } catch (error) {
            console.error('Error removing player:', error);
        } finally {
            removingPlayer.value = null;
        }
    };

    const toggleBanPlayer = async (player) => {
        banningPlayer.value = player.id;
        try {
            const isBanned = player.pivot?.banned || false;
            await tournamentStore.banPlayer(props.tournament.slug, player.id, !isBanned);
            await loadPlayers();
        } catch (error) {
            console.error('Error banning/unbanning player:', error);
        } finally {
            banningPlayer.value = null;
        }
    };

    const focusedPlayer = ref(null);
    const editRatingDialog = ref(false);
    const editPlayer = player => {
        focusedPlayer.value = player;
        editRatingDialog.value = true;
    }
    const submitNewRating = async () => {
        await tournamentStore.editPlayerRating(focusedPlayer.value);
        editRatingDialog.value = false;
    }

    const loadPlayers = async () => {
        loadingPlayers.value = true;
        try {
            players.value = props.tournament?.players || [];
        } catch (error) {
            console.error('Error loading players:', error);
        } finally {
            loadingPlayers.value = false;
        }
    };

    onMounted(() => {
        loadPlayers();
    });

    defineExpose({
        loadPlayers
    });
</script>