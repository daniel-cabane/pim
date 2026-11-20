<template>
    <div>
        <v-alert
            v-if="storageWarning"
            type="warning"
            variant="tonal"
            class="mb-4"
            closable
        >
            {{ $t('Programs could not be saved in this browser. They will last until you leave the page.') }}
        </v-alert>

        <div class="d-flex align-center">
            <v-tabs v-model="tab" show-arrows class="flex-grow-1" color="primary">
                <v-tab value="game">{{ $t('Game') }}</v-tab>
                <v-tab v-for="program in programs" :key="program.id" :value="program.id">
                    <span class="tab-name">{{ program.name.trim() || '…' }}</span>
                    <v-tooltip :text="deleteBlocked(program.id) ? $t('This program is playing and cannot be closed.') : $t('Delete')">
                        <template #activator="{ props: tip }">
                            <v-btn
                                v-bind="tip"
                                icon="mdi-close"
                                size="x-small"
                                variant="text"
                                class="ml-1"
                                :disabled="deleteBlocked(program.id)"
                                :aria-label="$t('Delete')"
                                @click.stop="askDelete(program.id)"
                            />
                        </template>
                    </v-tooltip>
                </v-tab>
            </v-tabs>
            <v-tooltip :text="programs.length >= maxPrograms ? $t('Up to 6 programs.') : $t('Add a program')">
                <template #activator="{ props: tip }">
                    <span v-bind="tip" class="ml-2">
                        <v-btn
                            icon="mdi-plus"
                            variant="tonal"
                            color="primary"
                            :disabled="programs.length >= maxPrograms"
                            :aria-label="$t('Add a program')"
                            @click="addProgram"
                        />
                    </span>
                </template>
            </v-tooltip>
        </div>

        <div v-show="tab === 'game'" class="pt-4">
            <div class="d-flex flex-wrap ga-4 align-end">
                <v-select
                    v-for="seat in seats"
                    :key="seat"
                    v-model="seatChoice[seat]"
                    :items="seatItems"
                    :label="playerName(seat)"
                    :disabled="matchRunning"
                    item-title="title"
                    item-value="value"
                    variant="outlined"
                    density="comfortable"
                    hide-details
                    style="max-width: 280px; min-width: 200px;"
                />
                <div style="min-width: 220px; flex: 1; max-width: 320px;">
                    <div class="text-caption">{{ $t('AI delay') }} ({{ delay }} ms)</div>
                    <v-slider
                        v-model="delay"
                        :min="0"
                        :max="2000"
                        :step="100"
                        hide-details
                        density="compact"
                        color="primary"
                    />
                </div>
                <v-btn
                    :color="matchRunning ? 'error' : 'primary'"
                    :variant="matchRunning ? 'tonal' : 'flat'"
                    size="large"
                    :disabled="primaryDisabled"
                    @click="onPrimary"
                >
                    {{ primaryLabel }}
                </v-btn>
            </div>

            <div class="status-slot mt-4" aria-live="polite">
                <div v-if="status === 'thinking'" class="d-flex align-center ga-3 text-h6">
                    <v-progress-circular indeterminate color="primary" size="28" width="3" />
                    {{ $t('{name} is thinking', { name: turnName }) }}
                </div>
                <v-alert v-else-if="status === 'error'" type="error" variant="tonal" density="comfortable">
                    <div class="d-flex flex-wrap align-center ga-3">
                        <span>{{ errorMessage }}</span>
                        <v-btn color="primary" variant="flat" :text="$t('Retry')" @click="retry" />
                    </div>
                </v-alert>
                <div v-else-if="status === 'playing'" class="text-h6">
                    {{ humanPrompt }}
                </div>
                <div v-if="hint" class="text-error mt-1">{{ hint }}</div>
            </div>

            <div class="size-row">
                <v-btn-toggle
                    v-model="selectedSize"
                    class="d-flex flex-wrap justify-center"
                    divided
                    variant="outlined"
                    color="primary"
                >
                    <v-btn
                        v-for="size in sizes"
                        :key="size"
                        :value="size"
                        size="large"
                        :disabled="!humanTurn || !sizeAvailable(size)"
                    >
                        <v-icon :icon="sizeIcon[size]" start />
                        {{ size }} · {{ $t(sizeWord[size]) }}
                    </v-btn>
                </v-btn-toggle>
                <div v-if="phaseOverlay" class="size-overlay">
                    <v-sheet class="phase-alert" color="surface" elevation="8" rounded="lg">
                        <template v-if="status === 'over'">
                            <div class="text-h6 text-success">{{ $t('{name} has won', { name: winnerLabel }) }}</div>
                            <div v-for="(pattern, index) in patterns" :key="index">{{ patternLabel(pattern) }}</div>
                        </template>
                        <template v-else-if="status === 'draw'">
                            <div class="text-h6">{{ $t('Draw') }}</div>
                            <div>{{ $t('The match stopped without a winner.') }}</div>
                        </template>
                        <div v-else class="text-h6">{{ $t('Choose who plays') }}</div>
                    </v-sheet>
                </div>
            </div>

            <v-row class="mt-2">
                <v-col cols="12" md="8">
                    <jardin-botanique-board
                        :board="board"
                        :selected-size="selectedSize"
                        :human-turn="humanTurn"
                        :last-move="lastMove"
                        :win-cells="winCells"
                        @cell="onCell"
                    />
                </v-col>
                <v-col cols="12" md="4">
                    <div ref="logEl" class="move-log">
                        <div v-for="seat in seats" :key="seat" class="log-column">
                            <div class="log-title" :style="{ color: seatColor[seat] }">{{ playerName(seat) }}</div>
                            <div v-if="!movesFor(seat).length" class="text-medium-emphasis text-caption">{{ $t('No moves yet') }}</div>
                            <div v-for="(move, index) in movesFor(seat)" :key="index" class="log-move">
                                <v-icon :icon="sizeIcon[move.size]" :color="seatColor[seat]" size="22" />
                                <span>{{ coord(move.cell) }}</span>
                            </div>
                        </div>
                    </div>
                </v-col>
            </v-row>
        </div>

        <div
            v-for="program in programs"
            :key="program.id"
            v-show="tab === program.id"
            class="pt-4"
        >
            <jardin-botanique-editor
                v-if="editorMounted[program.id]"
                :program="program"
                :name-message="nameMessage(program)"
                :console-lines="consoleLines"
                :dark="isDark"
                :active="tab === program.id"
                @update-name="program.name = $event"
                @update-code="program.code = $event"
                @clear-console="consoleLines = []"
            />
        </div>

        <v-dialog v-model="abandonDialog" max-width="420">
            <v-card>
                <v-card-title>{{ $t('Abandon this game?') }}</v-card-title>
                <v-card-text>{{ $t('The board will be cleared and nobody wins.') }}</v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" :text="$t('Cancel')" @click="abandonDialog = false" />
                    <v-btn color="error" variant="flat" :text="$t('Abandon')" @click="confirmAbandon" />
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="deleteDialog" max-width="420">
            <v-card>
                <v-card-title>{{ $t('Delete this program?') }}</v-card-title>
                <v-card-text>{{ deleteName }}</v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" :text="$t('Cancel')" @click="deleteDialog = false" />
                    <v-btn color="error" variant="flat" :text="$t('Delete')" @click="confirmDelete" />
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script setup>
import { computed, nextTick, reactive, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useTheme } from 'vuetify';
import { applyMove, createBoard, listLegalMoves, SIZES } from './jardin-botanique-rules.js';
import { runInSandbox } from './jardin-botanique-sandbox.js';
import {
    MAX_CODE,
    MAX_PROGRAMS,
    createProgram,
    loadGardenState,
    nameError,
    nextProgramName,
    programUsable,
    saveGardenState,
    STARTER_CODE,
} from './jardin-botanique-storage.js';

const { t } = useI18n();
const theme = useTheme();

const sizes = SIZES;
const seats = [0, 1];
const maxPrograms = MAX_PROGRAMS;
const sizeWord = { S: 'Small', M: 'Medium', L: 'Large' };
const sizeIcon = { S: 'mdi-sprout', M: 'mdi-flower', L: 'mdi-tree' };
const seatColor = { 0: '#1b7f4e', 1: '#e07a1f' };

const loaded = loadGardenState();
const programs = ref(loaded.programs);
const delay = ref(loaded.delay);
const storageWarning = ref(loaded.warning);
const tab = ref('game');
const editorMounted = reactive({});
const seatChoice = ref([null, null]);
const status = ref('setup');
const board = ref(createBoard());
const history = ref([]);
const currentSeat = ref(0);
const selectedSize = ref(null);
const hint = ref('');
const lastMove = ref(null);
const patterns = ref([]);
const winner = ref(null);
const errorKind = ref('');
const errorRaw = ref('');
const consoleLines = ref([]);
const abandonDialog = ref(false);
const deleteDialog = ref(false);
const deleteId = ref(null);
const logEl = ref(null);

let turnToken = 0;
let activeRun = null;
let saveTimer = null;

const isDark = computed(() => theme.global.current.value.dark);
const matchRunning = computed(() => status.value === 'playing' || status.value === 'thinking' || status.value === 'error');
const choosing = computed(() => status.value === 'setup' || status.value === 'over' || status.value === 'draw');
const humanTurn = computed(() => status.value === 'playing' && seatChoice.value[currentSeat.value] === 'human');

const seatItems = computed(() => {
    const items = [{ title: t('Human'), value: 'human' }];
    for (const program of programs.value) {
        if (programUsable(program, programs.value)) {
            items.push({ title: program.name.trim(), value: program.id });
        }
    }
    return items;
});

const seatsReady = computed(() => seatChoice.value.every((choice) => {
    if (choice === 'human') return true;
    const program = programs.value.find((item) => item.id === choice);
    return programUsable(program, programs.value);
}));

const primaryDisabled = computed(() => choosing.value && !seatsReady.value);
const primaryLabel = computed(() => {
    if (status.value === 'setup') return t('Start game');
    if (status.value === 'over' || status.value === 'draw') return t('Play again');
    return t('Abandon');
});

const phaseOverlay = computed(() => status.value === 'setup' || status.value === 'over' || status.value === 'draw');
const turnName = computed(() => labelFor(currentSeat.value, programNameFor(currentSeat.value)));
const humanPrompt = computed(() => {
    const name = turnName.value;
    if (!selectedSize.value) return t('{name}, choose a size', { name });
    return t('{name}, choose a parcel', { name });
});
const winnerLabel = computed(() => (winner.value ? labelFor(winner.value.seat, winner.value.programName) : ''));
const winCells = computed(() => {
    if (status.value !== 'over') return [];
    const cells = new Set();
    for (const pattern of patterns.value) {
        if (pattern.type === 'parcel') cells.add(pattern.cell);
        else pattern.cells.forEach((cell) => cells.add(cell));
    }
    return [...cells];
});
const deleteName = computed(() => programs.value.find((program) => program.id === deleteId.value)?.name?.trim() || '');
const errorMessage = computed(() => {
    if (errorKind.value === 'timeout') return t('Time limit exceeded');
    if (errorKind.value === 'empty') return t('The program has no code.');
    if (errorKind.value === 'illegal') return t('That move is not legal.');
    if (errorKind.value === 'promise') return t('return a move, not a Promise');
    if (errorKind.value === 'missing') return t('Define function chooseMove(state)');
    if (errorKind.value === 'long') return t('Code is limited to 50 000 characters.');
    return errorRaw.value;
});

function playerName(seat) {
    return seat === 0 ? t('Player A') : t('Player B');
}

function movesFor(seat) {
    return history.value.filter((move) => move.seat === seat);
}

function coord(cell) {
    return `${'ABC'[cell % 3]}${Math.floor(cell / 3) + 1}`;
}

function labelFor(seat, programName) {
    if (programName) return programName;
    return playerName(seat);
}

function programNameFor(seat) {
    const choice = seatChoice.value[seat];
    if (!choice || choice === 'human') return null;
    const program = programs.value.find((item) => item.id === choice);
    if (!program) return null;
    return program.name.trim() || '…';
}

function nameMessage(program) {
    const error = nameError(program.name, programs.value, program.id);
    if (error === 'empty') return t('Name is required before this program can play.');
    if (error === 'long') return t('Name must be 24 characters or fewer.');
    if (error === 'reserved') return t('This name is reserved.');
    if (error === 'duplicate') return t('Another program already has this name.');
    return '';
}

function sizeAvailable(size) {
    return board.value.some((cell) => cell[size] === null);
}

function patternLabel(pattern) {
    if (pattern.type === 'same' && pattern.size === 'S') return t('Three small plants in a line');
    if (pattern.type === 'same' && pattern.size === 'M') return t('Three medium plants in a line');
    if (pattern.type === 'same' && pattern.size === 'L') return t('Three large plants in a line');
    if (pattern.type === 'order' && pattern.direction === 'asc') return t('Sizes in increasing order');
    if (pattern.type === 'order' && pattern.direction === 'desc') return t('Sizes in decreasing order');
    return t('Three sizes in one parcel');
}

function pushConsole(line) {
    const next = consoleLines.value.concat(line);
    consoleLines.value = next.length > 200 ? next.slice(next.length - 200) : next;
}

function syncSeats() {
    if (matchRunning.value) return;
    for (let seat = 0; seat < 2; seat += 1) {
        const choice = seatChoice.value[seat];
        if (!choice || choice === 'human') continue;
        const program = programs.value.find((item) => item.id === choice);
        if (!programUsable(program, programs.value)) seatChoice.value[seat] = null;
    }
}

function scheduleSave() {
    clearTimeout(saveTimer);
    saveTimer = setTimeout(() => {
        const rounded = Math.max(0, Math.min(2000, Math.round(delay.value / 100) * 100));
        if (rounded !== delay.value) delay.value = rounded;
        try {
            saveGardenState(programs.value, delay.value);
        } catch {
            storageWarning.value = true;
        }
    }, 300);
}

function invalidate() {
    turnToken += 1;
    activeRun?.cancel();
    activeRun = null;
}

function resetMatch() {
    board.value = createBoard();
    history.value = [];
    patterns.value = [];
    winner.value = null;
    lastMove.value = null;
    hint.value = '';
    selectedSize.value = null;
    errorKind.value = '';
    errorRaw.value = '';
    currentSeat.value = 0;
}

function waitDelay(ms) {
    if (ms <= 0) return new Promise((resolve) => { requestAnimationFrame(() => resolve()); });
    return new Promise((resolve) => { setTimeout(resolve, ms); });
}

function normalize(move) {
    return {
        size: move.size,
        cell: typeof move.cell === 'string' ? Number(move.cell) : move.cell,
    };
}

function failTurn(kind, raw = '') {
    errorKind.value = kind;
    errorRaw.value = raw;
    status.value = 'error';
    pushConsole({ level: 'error', text: errorMessage.value });
}

function commitMove(seat, move, applied, programName) {
    board.value = applied.board;
    lastMove.value = { cell: move.cell, size: move.size };
    history.value = history.value.concat({ seat, size: move.size, cell: move.cell, programName });
    selectedSize.value = null;
    hint.value = '';
    if (applied.winner !== null) {
        patterns.value = applied.patterns;
        winner.value = { seat, programName };
        status.value = 'over';
        return;
    }
    if (listLegalMoves(applied.board).length === 0) {
        patterns.value = [];
        status.value = 'draw';
        return;
    }
    currentSeat.value = seat === 0 ? 1 : 0;
    beginTurn();
}

async function executeProgram(token) {
    const seat = currentSeat.value;
    const program = programs.value.find((item) => item.id === seatChoice.value[seat]);
    const programName = program ? program.name.trim() : null;
    status.value = 'thinking';
    errorKind.value = '';
    errorRaw.value = '';
    const header = { level: 'header', text: t('{name} · move {n}', { name: labelFor(seat, programName), n: history.value.length + 1 }) };
    if (!program || !program.code.trim()) {
        pushConsole(header);
        failTurn('empty');
        return;
    }
    if (program.code.length > MAX_CODE) {
        pushConsole(header);
        failTurn('long');
        return;
    }
    const state = JSON.parse(JSON.stringify({
        you: seat,
        board: board.value,
        legalMoves: listLegalMoves(board.value),
        history: history.value.map((move) => ({ seat: move.seat, size: move.size, cell: move.cell })),
    }));
    const run = runInSandbox(program.code, state);
    activeRun = run;
    const result = await run.promise;
    if (activeRun === run) activeRun = null;
    if (token !== turnToken || result.cancelled) return;
    pushConsole(header);
    for (const line of result.logs || []) pushConsole(line);
    if (!result.ok) {
        if (result.error === 'timeout') failTurn('timeout');
        else if (result.error === 'return a move, not a Promise') failTurn('promise');
        else if (result.error === 'Define function chooseMove(state)') failTurn('missing');
        else failTurn('raw', result.error || '');
        return;
    }
    let applied;
    try {
        applied = applyMove(board.value, seat, result.move);
    } catch {
        failTurn('illegal');
        return;
    }
    const move = normalize(result.move);
    pushConsole({ level: 'log', text: `→ ${move.size} @ ${move.cell}` });
    commitMove(seat, move, applied, programName);
}

async function beginTurn() {
    const token = turnToken;
    selectedSize.value = null;
    hint.value = '';
    if (seatChoice.value[currentSeat.value] === 'human') {
        status.value = 'playing';
        return;
    }
    status.value = 'thinking';
    await waitDelay(delay.value);
    if (token !== turnToken) return;
    await executeProgram(token);
}

function startGame() {
    if (!seatsReady.value) return;
    invalidate();
    resetMatch();
    beginTurn();
}

function onPrimary() {
    if (matchRunning.value) abandonDialog.value = true;
    else startGame();
}

function confirmAbandon() {
    invalidate();
    resetMatch();
    status.value = 'setup';
    abandonDialog.value = false;
}

function retry() {
    turnToken += 1;
    executeProgram(turnToken);
}

function onCell(cell) {
    if (!humanTurn.value || !selectedSize.value) return;
    const size = selectedSize.value;
    if (board.value[cell][size] !== null) {
        hint.value = t('This parcel already has a plant of this size.');
        return;
    }
    const seat = currentSeat.value;
    let applied;
    try {
        applied = applyMove(board.value, seat, { size, cell });
    } catch {
        return;
    }
    commitMove(seat, { size, cell }, applied, null);
}

function deleteBlocked(id) {
    return matchRunning.value && (seatChoice.value[0] === id || seatChoice.value[1] === id);
}

function askDelete(id) {
    if (deleteBlocked(id)) return;
    deleteId.value = id;
    deleteDialog.value = true;
}

function confirmDelete() {
    const id = deleteId.value;
    deleteDialog.value = false;
    if (!id || deleteBlocked(id)) return;
    programs.value = programs.value.filter((program) => program.id !== id);
    delete editorMounted[id];
    if (tab.value === id) tab.value = 'game';
    if (seatChoice.value[0] === id) seatChoice.value[0] = null;
    if (seatChoice.value[1] === id) seatChoice.value[1] = null;
}

function addProgram() {
    if (programs.value.length >= MAX_PROGRAMS) return;
    const program = createProgram(nextProgramName(programs.value), STARTER_CODE);
    programs.value = programs.value.concat(program);
    tab.value = program.id;
}

watch(tab, (value) => {
    if (value !== 'game') editorMounted[value] = true;
});

watch(selectedSize, () => {
    hint.value = '';
});

watch(programs, () => {
    syncSeats();
    scheduleSave();
}, { deep: true });

watch(delay, scheduleSave);

watch(history, async () => {
    await nextTick();
    if (logEl.value) logEl.value.scrollTop = logEl.value.scrollHeight;
}, { deep: true });
</script>

<style scoped>
.tab-name {
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.status-slot { min-height: 48px; }
.size-row { position: relative; }
.size-overlay {
    position: absolute;
    inset: 0;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}
.phase-alert {
    pointer-events: auto;
    max-width: min(520px, 100%);
    padding: 12px 20px;
    text-align: center;
}
.move-log {
    max-height: 420px;
    overflow: auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    padding: 8px 4px;
}
.log-title { font-weight: 700; margin-bottom: 8px; }
.log-move {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 2px 0;
    font-weight: 600;
}
</style>
