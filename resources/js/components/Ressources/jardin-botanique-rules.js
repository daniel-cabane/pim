export const LINES = [
    [0, 1, 2],
    [3, 4, 5],
    [6, 7, 8],
    [0, 3, 6],
    [1, 4, 7],
    [2, 5, 8],
    [0, 4, 8],
    [2, 4, 6],
];

export const SIZES = ['S', 'M', 'L'];

const SIZE_SET = new Set(SIZES);

export function createBoard() {
    return Array.from({ length: 9 }, () => ({ S: null, M: null, L: null }));
}

export function listLegalMoves(board) {
    const moves = [];
    for (let cell = 0; cell < 9; cell += 1) {
        for (const size of SIZES) {
            if (board[cell][size] === null) moves.push({ size, cell });
        }
    }
    return moves;
}

function patternKey(pattern) {
    if (pattern.type === 'parcel') return `parcel:${pattern.cell}`;
    return `${pattern.type}:${pattern.size || pattern.direction}:${pattern.cells.join(',')}`;
}

function visitPatterns(board, seat, visit) {
    const owns = (cell, size) => board[cell][size] === seat;
    for (const line of LINES) {
        for (const size of SIZES) {
            if (line.every((cell) => owns(cell, size))) {
                if (visit({ type: 'same', size, cells: line.slice() })) return;
            }
        }
        if (owns(line[0], 'S') && owns(line[1], 'M') && owns(line[2], 'L')) {
            if (visit({ type: 'order', direction: 'asc', cells: line.slice() })) return;
        }
        if (owns(line[0], 'L') && owns(line[1], 'M') && owns(line[2], 'S')) {
            if (visit({ type: 'order', direction: 'desc', cells: line.slice() })) return;
        }
    }
    for (let cell = 0; cell < 9; cell += 1) {
        if (SIZES.every((size) => owns(cell, size))) {
            if (visit({ type: 'parcel', cell })) return;
        }
    }
}

export function winningPatterns(board, seat) {
    const patterns = [];
    visitPatterns(board, seat, (pattern) => {
        patterns.push(pattern);
        return false;
    });
    return patterns;
}

export function hasWinningPattern(board, seat) {
    let found = false;
    visitPatterns(board, seat, () => {
        found = true;
        return true;
    });
    return found;
}

function fail(message) {
    throw new Error(message);
}

function parseMove(move) {
    if (move === null || typeof move !== 'object' || Array.isArray(move)) fail('Illegal move');
    const { size } = move;
    if (!SIZE_SET.has(size)) fail('Illegal move');
    let { cell } = move;
    if (typeof cell === 'string') {
        if (!/^[0-8]$/.test(cell)) fail('Illegal move');
        cell = Number(cell);
    }
    if (typeof cell !== 'number' || !Number.isInteger(cell) || cell < 0 || cell > 8) fail('Illegal move');
    return { size, cell };
}

function cloneBoard(board) {
    return board.map((cell) => ({ S: cell.S, M: cell.M, L: cell.L }));
}

export function applyMove(board, seat, move) {
    if (seat !== 0 && seat !== 1) fail('Illegal seat');
    const parsed = parseMove(move);
    if (board[parsed.cell][parsed.size] !== null) fail('Illegal move');
    const before = new Set(winningPatterns(board, seat).map(patternKey));
    const next = cloneBoard(board);
    next[parsed.cell][parsed.size] = seat;
    const patterns = winningPatterns(next, seat).filter((pattern) => !before.has(patternKey(pattern)));
    return {
        board: next,
        patterns,
        winner: patterns.length ? seat : null,
    };
}
