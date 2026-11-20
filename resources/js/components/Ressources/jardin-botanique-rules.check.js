import {
    applyMove,
    createBoard,
    hasWinningPattern,
    LINES,
    listLegalMoves,
    SIZES,
    winningPatterns,
} from './jardin-botanique-rules.js';

function assert(condition, message) {
    if (!condition) throw new Error(message);
}

function sameJson(a, b) {
    return JSON.stringify(a) === JSON.stringify(b);
}

function play(board, seat, move) {
    return applyMove(board, seat, move);
}

function assertNoPatterns(board, label) {
    assert(winningPatterns(board, 0).length === 0, `${label}: seat 0 has a pattern`);
    assert(winningPatterns(board, 1).length === 0, `${label}: seat 1 has a pattern`);
}

function placeSeries(moves) {
    let board = createBoard();
    let last = null;
    for (const step of moves) {
        last = play(board, step.seat, { size: step.size, cell: step.cell });
        board = last.board;
    }
    return last;
}

function assertWin(last, seat, type, extra) {
    assert(last.winner === seat, `expected winner ${seat}, got ${last.winner}`);
    const match = last.patterns.find((pattern) => pattern.type === type && extra(pattern));
    assert(match, `missing ${type} pattern in ${JSON.stringify(last.patterns)}`);
}

const fresh = createBoard();
assert(listLegalMoves(fresh).length === 27, 'a new board has 27 legal moves');
assertNoPatterns(fresh, 'empty');
assert(LINES.length === 8, 'eight lines');
assert(sameJson(SIZES, ['S', 'M', 'L']), 'sizes');

const one = play(fresh, 0, { size: 'S', cell: 0 });
assert(one.winner === null && one.patterns.length === 0, 'one plant is not a win');
const two = play(one.board, 0, { size: 'M', cell: 1 });
assert(two.winner === null && two.patterns.length === 0, 'two plants are not a win');

assertWin(
    placeSeries([
        { seat: 0, size: 'S', cell: 0 },
        { seat: 0, size: 'S', cell: 1 },
        { seat: 0, size: 'S', cell: 2 },
    ]),
    0,
    'same',
    (pattern) => pattern.size === 'S' && sameJson(pattern.cells, [0, 1, 2]),
);

assertWin(
    placeSeries([
        { seat: 0, size: 'S', cell: 0 },
        { seat: 0, size: 'M', cell: 1 },
        { seat: 0, size: 'L', cell: 2 },
    ]),
    0,
    'order',
    (pattern) => pattern.direction === 'asc' && sameJson(pattern.cells, [0, 1, 2]),
);

assertWin(
    placeSeries([
        { seat: 0, size: 'L', cell: 0 },
        { seat: 0, size: 'M', cell: 1 },
        { seat: 0, size: 'S', cell: 2 },
    ]),
    0,
    'order',
    (pattern) => pattern.direction === 'desc' && sameJson(pattern.cells, [0, 1, 2]),
);

const notOrdered = placeSeries([
    { seat: 0, size: 'S', cell: 0 },
    { seat: 0, size: 'L', cell: 1 },
    { seat: 0, size: 'M', cell: 2 },
]);
assert(notOrdered.winner === null, 'S,L,M is not a win');
assertNoPatterns(notOrdered.board, 'S,L,M');

const split = placeSeries([
    { seat: 0, size: 'S', cell: 0 },
    { seat: 1, size: 'S', cell: 1 },
    { seat: 0, size: 'S', cell: 2 },
]);
assert(split.winner === null, 'a split row is not a win');
assertNoPatterns(split.board, 'split row');

assertWin(
    placeSeries([
        { seat: 0, size: 'S', cell: 0 },
        { seat: 0, size: 'M', cell: 0 },
        { seat: 0, size: 'L', cell: 0 },
    ]),
    0,
    'parcel',
    (pattern) => pattern.cell === 0,
);

const blockedParcel = placeSeries([
    { seat: 0, size: 'S', cell: 0 },
    { seat: 0, size: 'M', cell: 0 },
    { seat: 1, size: 'L', cell: 0 },
]);
assert(blockedParcel.winner === null, 'AAB is not a parcel win');
assertNoPatterns(blockedParcel.board, 'AAB');

assertWin(
    placeSeries([
        { seat: 0, size: 'S', cell: 2 },
        { seat: 0, size: 'M', cell: 4 },
        { seat: 0, size: 'L', cell: 6 },
    ]),
    0,
    'order',
    (pattern) => pattern.direction === 'asc' && sameJson(pattern.cells, [2, 4, 6]),
);

assertWin(
    placeSeries([
        { seat: 0, size: 'L', cell: 2 },
        { seat: 0, size: 'M', cell: 4 },
        { seat: 0, size: 'S', cell: 6 },
    ]),
    0,
    'order',
    (pattern) => pattern.direction === 'desc' && sameJson(pattern.cells, [2, 4, 6]),
);

assertWin(
    placeSeries([
        { seat: 0, size: 'S', cell: 0 },
        { seat: 0, size: 'M', cell: 4 },
        { seat: 0, size: 'L', cell: 8 },
    ]),
    0,
    'order',
    (pattern) => pattern.direction === 'asc' && sameJson(pattern.cells, [0, 4, 8]),
);

assertWin(
    placeSeries([
        { seat: 0, size: 'L', cell: 0 },
        { seat: 0, size: 'M', cell: 4 },
        { seat: 0, size: 'S', cell: 8 },
    ]),
    0,
    'order',
    (pattern) => pattern.direction === 'desc' && sameJson(pattern.cells, [0, 4, 8]),
);

assertWin(
    placeSeries([
        { seat: 0, size: 'S', cell: 2 },
        { seat: 0, size: 'M', cell: 5 },
        { seat: 0, size: 'L', cell: 8 },
    ]),
    0,
    'order',
    (pattern) => pattern.direction === 'asc' && sameJson(pattern.cells, [2, 5, 8]),
);

assertWin(
    placeSeries([
        { seat: 0, size: 'L', cell: 2 },
        { seat: 0, size: 'M', cell: 5 },
        { seat: 0, size: 'S', cell: 8 },
    ]),
    0,
    'order',
    (pattern) => pattern.direction === 'desc' && sameJson(pattern.cells, [2, 5, 8]),
);

assertWin(
    placeSeries([
        { seat: 0, size: 'S', cell: 0 },
        { seat: 0, size: 'M', cell: 3 },
        { seat: 0, size: 'L', cell: 6 },
    ]),
    0,
    'order',
    (pattern) => pattern.direction === 'asc' && sameJson(pattern.cells, [0, 3, 6]),
);

assertWin(
    placeSeries([
        { seat: 0, size: 'L', cell: 0 },
        { seat: 0, size: 'M', cell: 3 },
        { seat: 0, size: 'S', cell: 6 },
    ]),
    0,
    'order',
    (pattern) => pattern.direction === 'desc' && sameJson(pattern.cells, [0, 3, 6]),
);

const withExtras = placeSeries([
    { seat: 0, size: 'M', cell: 0 },
    { seat: 0, size: 'S', cell: 0 },
    { seat: 0, size: 'M', cell: 1 },
    { seat: 0, size: 'S', cell: 1 },
    { seat: 0, size: 'L', cell: 2 },
    { seat: 0, size: 'S', cell: 2 },
]);
assertWin(withExtras, 0, 'same', (pattern) => pattern.size === 'S' && sameJson(pattern.cells, [0, 1, 2]));

const ownedByB = placeSeries([
    { seat: 1, size: 'S', cell: 4 },
    { seat: 1, size: 'M', cell: 4 },
    { seat: 1, size: 'L', cell: 4 },
]);
assertWin(ownedByB, 1, 'parcel', (pattern) => pattern.cell === 4);
assert(winningPatterns(ownedByB.board, 0).length === 0, "B's plants are not A's");

const mixedLine = placeSeries([
    { seat: 0, size: 'M', cell: 3 },
    { seat: 1, size: 'M', cell: 4 },
    { seat: 0, size: 'M', cell: 5 },
]);
assert(winningPatterns(mixedLine.board, 0).length === 0, 'A does not own B slots');
assert(winningPatterns(mixedLine.board, 1).length === 0, 'B does not own A slots');

const original = createBoard();
const originalJson = JSON.stringify(original);
const placed = applyMove(original, 0, { size: 'L', cell: 7 });
assert(JSON.stringify(original) === originalJson, 'applyMove mutates its input');
placed.board[7].L = null;
const placedAgain = applyMove(original, 0, { size: 'L', cell: 7 });
assert(placedAgain.board[7].L === 0, 'a later applyMove still sees the original board');

const occupied = applyMove(createBoard(), 0, { size: 'S', cell: 3 });
const occupiedJson = JSON.stringify(occupied.board);
let threw = false;
try {
    applyMove(occupied.board, 1, { size: 'S', cell: 3 });
} catch {
    threw = true;
}
assert(threw, 'replaying a filled slot must throw');
assert(JSON.stringify(occupied.board) === occupiedJson, 'a rejected move must leave the board unchanged');

for (const bad of [
    () => applyMove(createBoard(), 0, { size: 's', cell: 0 }),
    () => applyMove(createBoard(), 0, { size: 'S', cell: 1.5 }),
    () => applyMove(createBoard(), 0, { size: 'S', cell: '4.0' }),
    () => applyMove(createBoard(), 0, { size: 'S', cell: '9' }),
    () => applyMove(createBoard(), 2, { size: 'S', cell: 0 }),
    () => applyMove(createBoard(), '0', { size: 'S', cell: 0 }),
    () => applyMove(createBoard(), 0, null),
]) {
    let rejected = false;
    try {
        bad();
    } catch {
        rejected = true;
    }
    assert(rejected, 'an illegal move was accepted');
}

const asString = applyMove(createBoard(), 1, { size: 'M', cell: '4' });
assert(asString.board[4].M === 1, 'numeric string cells are accepted');

const orderedMoves = listLegalMoves(createBoard());
assert(orderedMoves[0].size === 'S' && orderedMoves[0].cell === 0, 'first move');
assert(orderedMoves[1].size === 'M' && orderedMoves[1].cell === 0, 'second move');
assert(orderedMoves[2].size === 'L' && orderedMoves[2].cell === 0, 'third move');
assert(orderedMoves[3].size === 'S' && orderedMoves[3].cell === 1, 'fourth move');
const afterOne = listLegalMoves(asString.board);
assert(afterOne[3].size === 'S' && afterOne[3].cell === 1, 'order stays cell-major after a placement');
assert(!afterOne.some((move) => move.cell === 4 && move.size === 'M'), 'filled slot leaves the list');

const alreadyWon = placeSeries([
    { seat: 0, size: 'S', cell: 0 },
    { seat: 0, size: 'S', cell: 1 },
    { seat: 0, size: 'S', cell: 2 },
]);
const extra = applyMove(alreadyWon.board, 0, { size: 'M', cell: 8 });
assert(extra.winner === null && extra.patterns.length === 0, 'an older pattern is not reported again');

const mono = createBoard();
mono[0] = { S: 0, M: 0, L: 0 };
const monoPatterns = winningPatterns(mono, 0);
assert(monoPatterns.some((pattern) => pattern.type === 'parcel' && pattern.cell === 0), 'a monochrome cell is a parcel win');
assert(!hasWinningPattern(mono, 1), 'the other company does not own that parcel');

function decode(code) {
    const minority = Math.floor(code / 2);
    const minorityOwner = code % 2;
    const cell = {
        S: 1 - minorityOwner,
        M: 1 - minorityOwner,
        L: 1 - minorityOwner,
    };
    cell[SIZES[minority]] = minorityOwner;
    return cell;
}

const total = 6 ** 9;
for (let n = 0; n < total; n += 1) {
    let value = n;
    const board = [];
    for (let cell = 0; cell < 9; cell += 1) {
        board.push(decode(value % 6));
        value = Math.floor(value / 6);
    }
    if (!hasWinningPattern(board, 0) && !hasWinningPattern(board, 1)) {
        throw new Error(`full mixed board ${n} has no winner`);
    }
}

console.log('jardin-botanique rules ok');
