<template>
    <div class="garden">
        <div class="corner"></div>
        <div v-for="col in columns" :key="col" class="axis">{{ col }}</div>
        <template v-for="row in 3" :key="row">
            <div class="axis">{{ row }}</div>
            <button
                v-for="col in 3"
                :key="`${row}-${col}`"
                type="button"
                class="cell"
                :class="cellClass(index(row, col))"
                :aria-label="cellLabel(index(row, col))"
                @click="onCell(index(row, col))"
            >
                <span class="index">{{ index(row, col) }}</span>
                <span
                    v-for="size in sizes"
                    :key="size"
                    class="slot"
                    :class="{ last: isLast(index(row, col), size) }"
                >
                    <v-icon
                        :icon="icons[size]"
                        class="plant"
                        :class="[`plant-${size}`, { ghost: owner(index(row, col), size) === null }]"
                        :color="iconColor(index(row, col), size)"
                    />
                    <span
                        v-if="owner(index(row, col), size) !== null"
                        class="mark"
                        :style="{ color: companyColor(owner(index(row, col), size)) }"
                    >{{ owner(index(row, col), size) === 0 ? 'A' : 'B' }}</span>
                </span>
            </button>
        </template>
    </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n';
import { SIZES } from './jardin-botanique-rules.js';

const { t } = useI18n();

const PALETTE = {
    A: '#1b7f4e',
    B: '#e07a1f',
    empty: '#c4b49a',
};

const columns = ['A', 'B', 'C'];
const sizes = SIZES;
const icons = { S: 'mdi-sprout', M: 'mdi-flower', L: 'mdi-tree' };

const props = defineProps({
    board: { type: Array, required: true },
    selectedSize: { type: String, default: null },
    humanTurn: { type: Boolean, default: false },
    lastMove: { type: Object, default: null },
    winCells: { type: Array, default: () => [] },
});

const emit = defineEmits(['cell']);

function index(row, col) {
    return (row - 1) * 3 + (col - 1);
}

function owner(cell, size) {
    return props.board[cell][size];
}

function companyColor(seat) {
    return seat === 0 ? PALETTE.A : PALETTE.B;
}

function iconColor(cell, size) {
    const who = owner(cell, size);
    return who === null ? PALETTE.empty : companyColor(who);
}

function isLast(cell, size) {
    return props.lastMove?.cell === cell && props.lastMove?.size === size;
}

function cellClass(cell) {
    const taken = props.humanTurn && props.selectedSize && owner(cell, props.selectedSize) !== null;
    const legal = props.humanTurn && props.selectedSize && owner(cell, props.selectedSize) === null;
    return {
        win: props.winCells.includes(cell),
        taken,
        legal,
    };
}

function cellLabel(cell) {
    const col = columns[cell % 3];
    const row = Math.floor(cell / 3) + 1;
    const slots = sizes.map((size) => {
        const who = owner(cell, size);
        return who === null ? `${size} ${t('Empty')}` : `${size} ${who === 0 ? 'A' : 'B'}`;
    });
    return `${col}${row}, ${t('Cell')} ${cell}, ${slots.join(', ')}`;
}

function onCell(cell) {
    emit('cell', cell);
}
</script>

<style scoped>
.garden {
    display: grid;
    grid-template-columns: 28px 1fr 1fr 1fr;
    gap: 8px;
    width: min(560px, 100%);
    margin: 0 auto;
    padding: 12px;
    background: #f4f0e6;
    border-radius: 16px;
    box-sizing: border-box;
}
.corner { min-height: 24px; }
.axis {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6d5b45;
    font-weight: 700;
}
.cell {
    position: relative;
    display: flex;
    align-items: flex-end;
    justify-content: space-around;
    min-height: 148px;
    padding: 18px 4px 8px;
    border: 2px solid #c4b49a;
    border-radius: 12px;
    background: #fffdf8;
    cursor: default;
}
.cell.legal { cursor: pointer; border-color: #6d5b45; }
.cell.taken { cursor: not-allowed; opacity: 0.55; }
.cell.win { background: #f6e2a8; border-color: #8d6a2f; }
.index {
    position: absolute;
    top: 4px;
    right: 6px;
    color: #8a7a62;
    font-size: 11px;
}
.slot {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 28px;
    border-radius: 8px;
}
.slot.last { box-shadow: 0 0 0 2px #5c4a32; }
.mark { font-size: 12px; font-weight: 700; line-height: 1; }
.plant-S { font-size: 20px !important; }
.plant-M { font-size: 40px !important; }
.plant-L { font-size: 62px !important; }
.plant.ghost.plant-S { font-size: 15px !important; }
.plant.ghost.plant-M { font-size: 28px !important; }
.plant.ghost.plant-L { font-size: 44px !important; }
</style>
