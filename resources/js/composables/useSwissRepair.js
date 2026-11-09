export function isAbsent(game) {
    return !!game && game.status === 'absent';
}

export function isBye(game) {
    return !!game && game.player2_id == null && game.player1_id != null && !isAbsent(game);
}

export function isUnpaired(game) {
    return isBye(game) || isAbsent(game);
}

export function isUnset(game) {
    return !!game && game.player2_id != null && game.result1 == null && game.result2 == null;
}

export function unpairedPlayers(round, players) {
    if (!round?.games?.length) {
        return [];
    }

    const active = new Set(
        (players || [])
            .filter((player) => !player.pivot?.banned)
            .map((player) => Number(player.id))
    );

    return round.games.filter((game) => isUnpaired(game) && active.has(Number(game.player1_id)));
}

export function havePlayed(tournament, idA, idB) {
    if (idA == null || idB == null || Number(idA) === Number(idB)) {
        return false;
    }

    const a = Number(idA);
    const b = Number(idB);

    for (const round of tournament?.rounds || []) {
        for (const game of round.games || []) {
            if (game.player1_id == null || game.player2_id == null) {
                continue;
            }
            const ids = [Number(game.player1_id), Number(game.player2_id)];
            if (ids.includes(a) && ids.includes(b)) {
                return true;
            }
        }
    }

    return false;
}
