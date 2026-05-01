<template>
    <div class="d-flex flex-column" style="height: calc(100vh - 150px); min-height: 400px;">
        <canvas
            ref="canvasEl"
            style="flex: 1; width: 100%; display: block; cursor: crosshair; min-height: 0;"
            @mousedown="onMouseDown"
            @mousemove="onMouseMove"
            @mouseup="onMouseUp"
            @mouseleave="onMouseLeave"
            @wheel.prevent="onWheel"
            @contextmenu.prevent
        />
        <div class="pa-3 d-flex flex-wrap align-center ga-3">
            <v-btn
                :color="running ? 'error' : 'success'"
                variant="elevated"
                :prepend-icon="running ? 'mdi-stop' : 'mdi-play'"
                :text="running ? $t('Stop') : $t('Start')"
                min-width="110"
                @click="toggleRun"
            />

            <v-btn
                variant="outlined"
                icon="mdi-skip-previous"
                :disabled="running || historyLength === 0"
                @click="prevStep"
            />
            <v-btn
                variant="outlined"
                icon="mdi-skip-next"
                :disabled="running"
                @click="nextStep"
            />

            <div class="d-flex flex-column" style="min-width: 180px;">
                <div class="text-caption">{{ $t('Speed') }}</div>
                <v-slider v-model="speed" min="1" max="20" step="1" hide-details density="compact" />
            </div>

            <div class="d-flex flex-column" style="min-width: 180px;">
                <div class="text-caption">{{ $t('Zoom') }} ({{ cellSize }}px)</div>
                <v-slider
                    v-model="cellSize"
                    min="2"
                    max="150"
                    step="1"
                    hide-details
                    density="compact"
                    @update:model-value="onZoomSlider"
                />
            </div>

            <v-dialog v-model="clearDialog" max-width="420">
                <template #activator="{ props: activatorProps }">
                    <v-btn
                        color="secondary"
                        variant="outlined"
                        prepend-icon="mdi-delete-sweep"
                        :text="$t('Clear board')"
                        v-bind="activatorProps"
                    />
                </template>
                <v-card>
                    <v-card-title>{{ $t('Clear board') }}</v-card-title>
                    <v-card-text>{{ $t('Are you sure you want to clear the board?') }}</v-card-text>
                    <v-card-actions>
                        <v-spacer />
                        <v-btn :text="$t('Cancel')" @click="clearDialog = false" />
                        <v-btn color="error" variant="flat" :text="$t('Clear')" @click="clearBoard" />
                    </v-card-actions>
                </v-card>
            </v-dialog>

            <v-spacer />
            <div class="text-caption text-medium-emphasis">{{ $t('Generation') }}: {{ generation }}</div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { useTheme } from 'vuetify';

const theme = useTheme();

// Canvas
const canvasEl = ref(null);
let ctx = null;
let resizeObserver = null;
let gameInterval = null;

// Game state (plain variables — canvas renders imperatively)
let cells = new Set();
let history = [];

// Reactive state for template bindings
const running = ref(false);
const speed = ref(5);
const cellSize = ref(20);
const generation = ref(0);
const clearDialog = ref(false);
const historyLength = ref(0);

// Viewport pan (in pixels)
let panX = 0;
let panY = 0;

// Mouse state
let isMouseDown = false;
let mouseButton = -1;
let lastCellX = null;
let lastCellY = null;
let dragStartClientX = 0;
let dragStartClientY = 0;
let dragStartPanX = 0;
let dragStartPanY = 0;
let hasPanned = false;

// ─── Conway's Game of Life ─────────────────────────────────────────────────

function nextGeneration(current) {
    const neighborCount = new Map();
    for (const key of current) {
        const comma = key.indexOf(',');
        const x = parseInt(key.slice(0, comma));
        const y = parseInt(key.slice(comma + 1));
        for (let dx = -1; dx <= 1; dx++) {
            for (let dy = -1; dy <= 1; dy++) {
                if (dx === 0 && dy === 0) continue;
                const nk = `${x + dx},${y + dy}`;
                neighborCount.set(nk, (neighborCount.get(nk) || 0) + 1);
            }
        }
    }
    const next = new Set();
    for (const [key, count] of neighborCount) {
        if (count === 3 || (count === 2 && current.has(key))) {
            next.add(key);
        }
    }
    return next;
}

// ─── Steps ─────────────────────────────────────────────────────────────────

function nextStep() {
    if (history.length >= 100) history.shift();
    history.push(new Set(cells));
    historyLength.value = history.length;
    cells = nextGeneration(cells);
    generation.value++;
    draw();
}

function prevStep() {
    if (history.length === 0) return;
    cells = history.pop();
    historyLength.value = history.length;
    generation.value = Math.max(0, generation.value - 1);
    draw();
}

// ─── Run / Stop ────────────────────────────────────────────────────────────

function toggleRun() {
    running.value = !running.value;
    if (running.value) startLoop();
    else stopLoop();
}

function startLoop() {
    stopLoop();
    const delay = Math.max(16, Math.round(600 / Math.pow(speed.value, 1.1)));
    gameInterval = setInterval(nextStep, delay);
}

function stopLoop() {
    if (gameInterval) {
        clearInterval(gameInterval);
        gameInterval = null;
    }
}

watch(speed, () => {
    if (running.value) startLoop();
});

// ─── Zoom ──────────────────────────────────────────────────────────────────

function onZoomSlider(newSize) {
    if (!canvasEl.value) return;
    const cx = canvasEl.value.width / 2;
    const cy = canvasEl.value.height / 2;
    const worldX = (cx - panX) / cellSize.value;
    const worldY = (cy - panY) / cellSize.value;
    // cellSize.value is already updated by v-model; sync panX/Y
    panX = cx - worldX * newSize;
    panY = cy - worldY * newSize;
    draw();
}

function onWheel(e) {
    const rect = canvasEl.value.getBoundingClientRect();
    const mx = e.clientX - rect.left;
    const my = e.clientY - rect.top;
    const worldX = (mx - panX) / cellSize.value;
    const worldY = (my - panY) / cellSize.value;
    const factor = e.deltaY > 0 ? 0.9 : 1.1;
    const newSize = Math.max(2, Math.min(150, cellSize.value * factor));
    cellSize.value = Math.round(newSize);
    panX = mx - worldX * newSize;
    panY = my - worldY * newSize;
    draw();
}

// ─── Cell coords ───────────────────────────────────────────────────────────

function pixelToCell(px, py) {
    return [
        Math.floor((px - panX) / cellSize.value),
        Math.floor((py - panY) / cellSize.value),
    ];
}

// Paint/erase cells between two cell coordinates (Bresenham line)
function paintLine(x0, y0, x1, y1, add) {
    const dx = Math.abs(x1 - x0);
    const dy = Math.abs(y1 - y0);
    const sx = x0 < x1 ? 1 : -1;
    const sy = y0 < y1 ? 1 : -1;
    let err = dx - dy;
    let x = x0, y = y0;
    while (true) {
        const key = `${x},${y}`;
        if (add) cells.add(key);
        else cells.delete(key);
        if (x === x1 && y === y1) break;
        const e2 = 2 * err;
        if (e2 > -dy) { err -= dy; x += sx; }
        if (e2 < dx) { err += dx; y += sy; }
    }
}

// ─── Mouse events ──────────────────────────────────────────────────────────

function onMouseDown(e) {
    e.preventDefault();
    isMouseDown = true;
    mouseButton = e.button;
    dragStartClientX = e.clientX;
    dragStartClientY = e.clientY;
    dragStartPanX = panX;
    dragStartPanY = panY;
    hasPanned = false;

    if (!running.value && (mouseButton === 0 || mouseButton === 2)) {
        const rect = canvasEl.value.getBoundingClientRect();
        const [cx, cy] = pixelToCell(e.clientX - rect.left, e.clientY - rect.top);
        lastCellX = cx;
        lastCellY = cy;
        const key = `${cx},${cy}`;
        if (mouseButton === 0) cells.add(key);
        else cells.delete(key);
        draw();
    }
}

function onMouseMove(e) {
    if (!isMouseDown) return;

    const totalDx = e.clientX - dragStartClientX;
    const totalDy = e.clientY - dragStartClientY;

    // Middle mouse or left/right when running → pan
    if (mouseButton === 1 || running.value) {
        hasPanned = true;
        panX = dragStartPanX + totalDx;
        panY = dragStartPanY + totalDy;
        draw();
        return;
    }

    // Left/right when stopped → paint/erase
    if (!running.value && (mouseButton === 0 || mouseButton === 2)) {
        const rect = canvasEl.value.getBoundingClientRect();
        const [cx, cy] = pixelToCell(e.clientX - rect.left, e.clientY - rect.top);
        if (cx !== lastCellX || cy !== lastCellY) {
            paintLine(lastCellX, lastCellY, cx, cy, mouseButton === 0);
            lastCellX = cx;
            lastCellY = cy;
            draw();
        }
    }
}

function onMouseUp() {
    isMouseDown = false;
    mouseButton = -1;
    lastCellX = null;
    lastCellY = null;
}

function onMouseLeave() {
    if (isMouseDown) onMouseUp();
}

// ─── Clear ─────────────────────────────────────────────────────────────────

function clearBoard() {
    stopLoop();
    running.value = false;
    cells = new Set();
    history = [];
    historyLength.value = 0;
    generation.value = 0;
    clearDialog.value = false;
    draw();
}

// ─── Rendering ─────────────────────────────────────────────────────────────

function draw() {
    if (!ctx || !canvasEl.value) return;
    const canvas = canvasEl.value;
    const w = canvas.width;
    const h = canvas.height;
    const size = cellSize.value;
    const isDark = theme.global.name.value === 'customDark';

    ctx.fillStyle = isDark ? '#1e1e2e' : '#f8f8f8';
    ctx.fillRect(0, 0, w, h);

    // Grid lines (only when cells are large enough to see them)
    if (size >= 6) {
        ctx.strokeStyle = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.09)';
        ctx.lineWidth = 0.5;
        const startCX = Math.floor(-panX / size) - 1;
        const endCX = Math.ceil((w - panX) / size) + 1;
        ctx.beginPath();
        for (let x = startCX; x <= endCX; x++) {
            const px = Math.round(x * size + panX) + 0.5;
            ctx.moveTo(px, 0);
            ctx.lineTo(px, h);
        }
        const startCY = Math.floor(-panY / size) - 1;
        const endCY = Math.ceil((h - panY) / size) + 1;
        for (let y = startCY; y <= endCY; y++) {
            const py = Math.round(y * size + panY) + 0.5;
            ctx.moveTo(0, py);
            ctx.lineTo(w, py);
        }
        ctx.stroke();
    }

    // Live cells
    ctx.fillStyle = isDark ? '#82b1ff' : '#1565c0';
    const pad = size >= 4 ? 1 : 0;
    for (const key of cells) {
        const comma = key.indexOf(',');
        const cx = parseInt(key.slice(0, comma));
        const cy = parseInt(key.slice(comma + 1));
        const px = cx * size + panX;
        const py = cy * size + panY;
        if (px + size >= 0 && px < w && py + size >= 0 && py < h) {
            ctx.fillRect(px + pad, py + pad, size - pad * 2, size - pad * 2);
        }
    }
}

// ─── Canvas resize ─────────────────────────────────────────────────────────

function resizeCanvas() {
    if (!canvasEl.value) return;
    const canvas = canvasEl.value;
    const rect = canvas.getBoundingClientRect();
    canvas.width = Math.floor(rect.width);
    canvas.height = Math.floor(rect.height);
    ctx = canvas.getContext('2d');
    draw();
}

// ─── Lifecycle ─────────────────────────────────────────────────────────────

onMounted(() => {
    resizeCanvas();
    // Center viewport on cell (0,0)
    if (canvasEl.value) {
        panX = Math.floor(canvasEl.value.width / 2);
        panY = Math.floor(canvasEl.value.height / 2);
        draw();
    }
    resizeObserver = new ResizeObserver(resizeCanvas);
    resizeObserver.observe(canvasEl.value);
});

onUnmounted(() => {
    stopLoop();
    if (resizeObserver) resizeObserver.disconnect();
});
</script>
