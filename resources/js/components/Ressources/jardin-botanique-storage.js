export const STORAGE_KEY = 'pim.jardin-botanique.v1';
export const MAX_PROGRAMS = 6;
export const MAX_CODE = 50000;

export const STARTER_CODE = `/**
 * Called on your turn. Return one legal move: { size, cell }.
 *
 * state.you is 0 or 1. Your plants are the slots equal to state.you.
 * Do not hardcode 0: the same program may sit in either seat.
 *
 * state.board[cell] is { S, M, L }. Each value is 0, 1, or null.
 * Cells are numbered:
 *   0 1 2
 *   3 4 5
 *   6 7 8
 *
 * state.legalMoves is { size, cell } in cell order, then S, M, L.
 * state.history is the moves already played, oldest first:
 *   { seat, size, cell }
 *
 * S < M < L. A full search of the game will be stopped after 1.5s.
 */
function chooseMove(state) {
  const moves = state.legalMoves;
  return moves[Math.floor(Math.random() * moves.length)];
}
`;

export function createProgram(name, code = STARTER_CODE) {
    return {
        id: crypto.randomUUID(),
        name,
        code,
    };
}

export function defaultPrograms() {
    return [createProgram('Random')];
}

export function nameError(name, programs, id) {
    const trimmed = name.trim();
    if (!trimmed) return 'empty';
    if (trimmed.length > 24) return 'long';
    const lower = trimmed.toLowerCase();
    if (lower === 'human' || lower === 'humain') return 'reserved';
    if (programs.some((program) => program.id !== id && program.name.trim().toLowerCase() === lower)) return 'duplicate';
    return null;
}

export function programUsable(program, programs) {
    return !!program && !nameError(program.name, programs, program.id) && program.code.trim().length > 0 && program.code.length <= MAX_CODE;
}

export function nextProgramName(programs) {
    const taken = new Set(programs.map((program) => program.name.trim().toLowerCase()));
    let n = 2;
    while (taken.has(`ai ${n}`)) n += 1;
    return `AI ${n}`;
}

function fallback(warning) {
    return { programs: defaultPrograms(), delay: 600, warning };
}

export function loadGardenState() {
    let raw;
    try {
        raw = localStorage.getItem(STORAGE_KEY);
    } catch {
        return fallback(true);
    }
    if (!raw) return fallback(false);
    try {
        const data = JSON.parse(raw);
        if (!data || !Array.isArray(data.programs)) return fallback(false);
        const programs = data.programs
            .filter((program) => program && typeof program.id === 'string' && typeof program.name === 'string' && typeof program.code === 'string')
            .slice(0, MAX_PROGRAMS)
            .map((program) => ({ id: program.id, name: program.name, code: program.code }));
        if (!programs.length) return fallback(false);
        const delay = typeof data.delay === 'number' && data.delay >= 0 && data.delay <= 2000 && data.delay % 100 === 0
            ? data.delay
            : 600;
        return { programs, delay, warning: false };
    } catch {
        return fallback(false);
    }
}

export function saveGardenState(programs, delay) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify({
        programs: programs.map((program) => ({ id: program.id, name: program.name, code: program.code })),
        delay,
    }));
}
