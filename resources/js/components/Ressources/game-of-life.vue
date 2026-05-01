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
                        <v-btn color="primary" variant="tonal" :text="$t('Cancel')" @click="clearDialog = false" style="min-width: 100px;"/>
                        <v-btn color="error" variant="flat" :text="$t('Clear')" @click="clearBoard" style="min-width: 100px;"/>
                    </v-card-actions>
                </v-card>
            </v-dialog>

            <v-menu :close-on-content-click="true">
                <template #activator="{ props: menuProps }">
                    <v-btn
                        variant="outlined"
                        prepend-icon="mdi-shape-plus"
                        :text="$t('Patterns')"
                        :color="selectedPattern ? 'primary' : undefined"
                        v-bind="menuProps"
                    />
                </template>
                <v-list density="compact" min-width="260">
                    <v-list-subheader>{{ $t('Famous patterns') }}</v-list-subheader>
                    <v-list-item
                        v-for="pattern in PATTERNS"
                        :key="pattern.name"
                        :active="selectedPattern?.name === pattern.name"
                        @click="selectPattern(pattern)"
                    >
                        <v-list-item-title>{{ pattern.name }}</v-list-item-title>
                        <v-list-item-subtitle class="text-caption">{{ pattern.description }}</v-list-item-subtitle>
                    </v-list-item>
                </v-list>
            </v-menu>

            <v-chip
                v-if="selectedPattern"
                closable
                color="primary"
                prepend-icon="mdi-cursor-default-click"
                @click:close="cancelPattern"
            >
                {{ selectedPattern.name }}
            </v-chip>

            <v-spacer />
            <div class="text-caption text-medium-emphasis">{{ $t('Generation') }}: {{ generation }}</div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { useTheme } from 'vuetify';

const theme = useTheme();

// ─── Pattern helpers ───────────────────────────────────────────────────────

function centeredCells(cells) {
    const xs = cells.map(c => c[0]);
    const ys = cells.map(c => c[1]);
    const ox = Math.round((Math.min(...xs) + Math.max(...xs)) / 2);
    const oy = Math.round((Math.min(...ys) + Math.max(...ys)) / 2);
    return cells.map(([x, y]) => [x - ox, y - oy]);
}

const PATTERNS = [
    {
        name: 'Glider',
        description: 'Period 4 · moves diagonally',
        cells: centeredCells([[1,0],[2,1],[0,2],[1,2],[2,2]]),
    },
    {
        name: 'Blinker',
        description: 'Period 2 · smallest oscillator',
        cells: centeredCells([[0,0],[1,0],[2,0]]),
    },
    {
        name: 'Toad',
        description: 'Period 2 · oscillator',
        cells: centeredCells([[1,0],[2,0],[3,0],[0,1],[1,1],[2,1]]),
    },
    {
        name: 'Beacon',
        description: 'Period 2 · oscillator',
        cells: centeredCells([[0,0],[1,0],[0,1],[1,1],[2,2],[3,2],[2,3],[3,3]]),
    },
    {
        name: 'Pulsar',
        description: 'Period 3 · oscillator (48 cells)',
        cells: centeredCells([
            [2,0],[3,0],[4,0],[8,0],[9,0],[10,0],
            [0,2],[5,2],[7,2],[12,2],
            [0,3],[5,3],[7,3],[12,3],
            [0,4],[5,4],[7,4],[12,4],
            [2,5],[3,5],[4,5],[8,5],[9,5],[10,5],
            [2,7],[3,7],[4,7],[8,7],[9,7],[10,7],
            [0,8],[5,8],[7,8],[12,8],
            [0,9],[5,9],[7,9],[12,9],
            [0,10],[5,10],[7,10],[12,10],
            [2,12],[3,12],[4,12],[8,12],[9,12],[10,12],
        ]),
    },
    {
        name: 'Pentadecathlon',
        description: 'Period 15 · oscillator',
        cells: centeredCells([
            [1,0],[1,1],[0,2],[2,2],[1,3],[1,4],[1,5],[1,6],[0,7],[2,7],[1,8],[1,9],[1,10],
        ]),
    },
    {
        name: 'LWSS',
        description: 'Lightweight Spaceship · period 4',
        cells: centeredCells([[1,0],[4,0],[0,1],[0,2],[4,2],[0,3],[1,3],[2,3],[3,3]]),
    },
    {
        name: 'R-pentomino',
        description: 'Chaotic · lives 1103 generations',
        cells: centeredCells([[1,0],[2,0],[0,1],[1,1],[1,2]]),
    },
    {
        name: 'Diehard',
        description: 'Dies after 130 generations',
        cells: centeredCells([[6,0],[0,1],[1,1],[1,2],[5,2],[6,2],[7,2]]),
    },
    {
        name: 'Acorn',
        description: 'Chaotic · 5206 generations',
        cells: centeredCells([[1,0],[3,1],[0,2],[1,2],[4,2],[5,2],[6,2]]),
    },
    {
        name: 'Gosper Glider Gun',
        description: 'Period 30 · produces gliders endlessly',
        cells: centeredCells([
            [24,0],
            [22,1],[24,1],
            [12,2],[13,2],[20,2],[21,2],[34,2],[35,2],
            [11,3],[15,3],[20,3],[21,3],[34,3],[35,3],
            [0,4],[1,4],[10,4],[16,4],[20,4],[21,4],
            [0,5],[1,5],[10,5],[14,5],[16,5],[17,5],[22,5],[24,5],
            [10,6],[16,6],[24,6],
            [11,7],[15,7],
            [12,8],[13,8],
        ]),
    },
];

// Canvas
const canvasEl = ref(null);
let ctx = null;
let resizeObserver = null;
let gameInterval = null;

// Game state (plain variables — canvas renders imperatively)
let cells = new Set();
let history = [];

// Pattern placement state
const selectedPattern = ref(null);
let ghostCX = null;
let ghostCY = null;

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

    // Pattern placement mode: left-click places, right-click cancels
    if (selectedPattern.value) {
        if (e.button === 0) {
            placePattern();
            return;
        } else if (e.button === 2) {
            cancelPattern();
            return;
        }
        // Middle button falls through to normal panning
    }

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
    // Update ghost position for pattern preview
    if (selectedPattern.value && canvasEl.value) {
        const rect = canvasEl.value.getBoundingClientRect();
        const [cx, cy] = pixelToCell(e.clientX - rect.left, e.clientY - rect.top);
        if (cx !== ghostCX || cy !== ghostCY) {
            ghostCX = cx;
            ghostCY = cy;
            draw();
        }
    }

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
    if (selectedPattern.value) {
        ghostCX = null;
        ghostCY = null;
        draw();
    }
}

// ─── Pattern placement ─────────────────────────────────────────────────────

function selectPattern(pattern) {
    selectedPattern.value = pattern;
}

function cancelPattern() {
    selectedPattern.value = null;
    ghostCX = null;
    ghostCY = null;
    draw();
}

function placePattern() {
    if (!selectedPattern.value || ghostCX === null) return;
    if (history.length >= 100) history.shift();
    history.push(new Set(cells));
    historyLength.value = history.length;
    for (const [dx, dy] of selectedPattern.value.cells) {
        cells.add(`${ghostCX + dx},${ghostCY + dy}`);
    }
    draw();
}

function onKeyDown(e) {
    if (e.key === 'Escape' && selectedPattern.value) {
        cancelPattern();
    }
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

    // Ghost pattern preview
    if (selectedPattern.value && ghostCX !== null) {
        ctx.fillStyle = isDark ? 'rgba(130, 177, 255, 0.45)' : 'rgba(21, 101, 192, 0.45)';
        for (const [dx, dy] of selectedPattern.value.cells) {
            const cx = ghostCX + dx;
            const cy = ghostCY + dy;
            const px = cx * size + panX;
            const py = cy * size + panY;
            if (px + size >= 0 && px < w && py + size >= 0 && py < h) {
                ctx.fillRect(px + pad, py + pad, size - pad * 2, size - pad * 2);
            }
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
    window.addEventListener('keydown', onKeyDown);
});

onUnmounted(() => {
    stopLoop();
    if (resizeObserver) resizeObserver.disconnect();
    window.removeEventListener('keydown', onKeyDown);
});
</script>
