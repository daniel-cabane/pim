# Late arrival and manual re-pairing in Swiss tournaments

Swiss only. Knockout and round robin keep their pairing.

An organiser adds a player during a live Swiss round and that player immediately has a bye in the latest round. With two or more byes, the organiser can pair two of them into a new game. White is chosen at random on the server. The organiser can break a game that has no result yet: the row is deleted and both players become byes. Cancel stays what it is, and the screen explains how it differs from Break.

## Rules

1. **Who.** The creator, a co-organiser, or an admin. That is `TournamentPolicy::update` and `GamePolicy::startGame`, which already allow the same three. The screens show the new buttons only where `isOrganizer` is already true (creator or co-organiser). Do not widen that Vue check to the admin role.
2. **Which round.** The latest round (`rounds()->latest('round_number')`) while `status` is `ongoing` and `format` is `swiss`. Older rounds are unchanged.
3. **Add.** `POST /api/tournaments/{slug}/players` still attaches the pivot and increments `players_count`. It then creates a bye when rule 2 holds and the tournament has a round. Preparation, draft, completed, knockout, round robin, and a Swiss event with no round yet only attach the player, as today. Self-join stays limited to preparation. Do not set `rating`; the column default is 800, and the organiser can edit it afterwards as today.
4. **Bye.** Same row the engines already write: `player2_id` null, `result1` win, `result2` loss, `status` completed, `ended_at` set. `player1` is White. There is no colour column and no new status.
5. **Unset game.** Both `result1` and `result2` are null, and `player2_id` is set. Status may be `pending` or `in_progress`. A single reported result is not unset: `Game::reportResult` stores `pending` while the other result is still null, because `getComputedStatusAttribute` returns pending if either result is null. Do not treat `status === 'pending'` as unset.
6. **Break.** Allowed only for an unset game in the round from rule 2. Delete that row. Give each of the two players a bye in that round. If either player already has another game in the round, reject.
7. **Pair.** The organiser sends two different player ids and no colour. Both must be non-banned players whose only game in that round is a bye. Delete both bye rows and insert one `pending` game with both results null. The server picks White with `random_int(0, 1)`. A rematch is allowed.
8. **Cancel.** Unchanged. Both results become `loss`, status `cancelled`, the row stays, and the two players are not free for another opponent this round. `recalculateStandings` ignores it because it only reads `completed` games.
9. **Remove.** Before detach, delete that player's bye rows in the latest round, then detach and decrement `players_count` as today. Leave any two-player game. This is required: a leftover bye is `completed`, and `recalculateStandings` uses `syncWithoutDetaching`, which would score the bye and put the player back.
10. **A bye cannot be scored or started.** `recordResult`, `setResult`, and `startGame` return 422 when `player2_id` is null. The public pairings table currently shows the result pencil on byes; the admin table already hides it. Hide it whenever `player2_id` is null. This covers Swiss, knockout, and round-robin byes.

## Scoring

A bye is worth 1 point and counts as a win. That already happens in `Tournament::recalculateStandings` for a completed game with `result1 = win` and a null opponent (`wins + 1`, `score + 1`). `result2 = loss` is stored so the row stays coherent; it is not applied to anyone when `player2_id` is null.

`recalculateStandings` runs in one place: `createNextRound`, before the next pairing. The pairings table shows a bye as `1 - 0` immediately because that is what the row says. The leaderboard reads the pivot, so it moves only when the next round is created. Ending the tournament (`status = completed`) does not recalculate. Do not call `recalculateStandings` from add, break, pair, remove, or complete. Do not change when the pivot is written.

`SwissPairingEngine::getByePlayer` already treats every `player2_id = null` as a bye received. A late bye counts, so that player is last in line for the next odd-player bye. Previous-opponent detection ignores null opponents and ignores a deleted game. A cancelled game still counts as a meeting. Leave both.

The next round stays blocked while any game in the latest round is neither `completed` nor `cancelled` (`Round::allGamesCompleted`). A bye does not block it. A game created by pairing byes does. Several byes may remain when the round looks finished; starting the next round then scores each of them.

## Server

One writer for a bye, used by the repair code and by the three engines. The engines change only by calling it. Their pairing does not change.

```php
Game::createBye(int $tournamentId, int $roundId, int $playerId): Game
```

Columns are the ones in rule 4. Replace the inline bye inserts in `SwissPairingEngine`, `KnockoutPairingEngine`, and `RoundRobinPairingEngine`.

Put the mutations in `App\Services\SwissRoundRepair`:

- `giveBye(Tournament $tournament, int $userId): bool` — inserts the bye when rule 3 applies and that player has no game in the latest round. Returns whether it inserted one. Does not open a transaction.
- `deleteLatestByes(Tournament $tournament, int $userId): void` — deletes that player's bye rows in the latest round. Does not open a transaction.
- `breakGame(Game $game): void` — owns the transaction.
- `pairByes(Tournament $tournament, Round $round, int $playerAId, int $playerBId): void` — owns the transaction.

`addPlayer` and `removePlayer` wrap their existing writes plus `giveBye` / `deleteLatestByes` in one `DB::transaction`. `tournament_player` already has a unique `(tournament_id, user_id)`. Keep the current "already a player" response (HTTP 200, `message.type = error`). A duplicate-key race returns that same response instead of a 500.

Inside `breakGame` and `pairByes`, lock the affected game rows with `lockForUpdate()`, ordered by `id`, and re-check the rules under the lock. Throw `\DomainException` with one of the locale keys below. The controller catches it and returns 422 `{ "message": "<key>" }`. `DB::transaction` rolls back when the exception leaves the closure.

Authorize before the service. Break uses `startGame` on the game. Pair uses `update` on the tournament. A failed policy stays 403.

Success body, same shape as `addPlayer`, so `useAPI` can read `message.text` and the store can replace the tournament:

```json
{ "message": { "text": "<key>", "type": "success" }, "tournament": "<loadTournamentRelations>" }
```

Call `loadTournamentRelations` after the write. Error toasts go through `useAPI`'s catch, which passes `response.data.message` to `$t()`. A 422 `message` must be a string key, not `{ text, type }`. Do not change `useAPI`.

Routes, inside the existing `auth:sanctum` group:

- `POST games/{game}/break` → `GameController@breakGame`
- `POST tournaments/{tournament:slug}/rounds/{round}/pair-byes` with `player_a_id` and `player_b_id` → `TournamentController@pairByes`

Reject pair when the round is not the latest, belongs to another tournament, the ids match, either player is missing or banned, or either player is not exactly one bye in that round. Reject break when the format or status fails rule 2, the game is not in the latest round, it is a bye, either result is set, or either player already has another game in the round.

No migration. `player2_id` is already nullable. Do not add a status, a bye table, a colour column, or a history of broken games. Do not run the Swiss engine again. Do not update `players_count` or the pivot inside break or pair. Do not set `round.status`.

## Client

`useTournamentStore` gains `breakGame(gameId)` and `pairByes(slug, roundId, playerAId, playerBId)`. Both `post` and, when the response contains `tournament`, replace `this.tournament`. Do not splice a single game: break removes one row and adds two, pair does the opposite. `addPlayer` already replaces the tournament.

Share the rules and the dialogs. Do not copy them into both tables.

- `resources/js/composables/useSwissRepair.js` exports `isBye(game)` (`player2_id` null), `isUnset(game)` (rule 5), `byePlayers(round, players)` (bye games whose `player1_id` is a non-banned entry in `tournament.players`; the pivot is on that list, not on the game player), and `havePlayed(tournament, idA, idB)` (any loaded game, any round, both ids set).
- `resources/js/components/Tournaments/swiss-repair-dialogs.vue` owns the pair dialog, the break confirm, and the two explanation dialogs. It calls the store.

Use both from:

- `admin-rounds-tab.vue`, on the card whose `round_number` is the highest, while `tournament.status === 'ongoing'` and `format === 'swiss'`
- `tournament-round-pairings.vue`, which already shows only the latest round, when `isOrganizer` and the same format and status hold

Do not key this off `isCurrentRound`. That helper is false once every game is `completed` or `cancelled`. Breaking the last unset game produces only byes, the round then looks finished, and Pair must still be there so the organiser can match those byes before starting the next round.

**Pair.** Show `Pair two byes` when `byePlayers` has at least two. Two selects, those players only, confirm disabled until they differ. The dialog says White is chosen at random. If `havePlayed` is true, also show `These players have already played each other.` The request still sends.

**Break.** A `Break` button on each unset game only. The confirm names both players and shows the Break explanation.

**Cancel.** An info icon on the existing Cancel button, in both result dialogs, opens the Cancel explanation.

**Player card.** In `tournament-current-game.vue`, when the viewer's game in the latest round has no `player2_id`, show the bye sentence and do not show the result selector, the avatars, or the opponent. A knockout or round-robin bye hits the same branch.

The public result cells stay as they are: a bye is `Bye` / `Exempt` and `1 - 0`.

## Copy

Add these keys to `resources/locales/en.json` and `resources/locales/fr.json`. API messages are keys because the notification centre renders `$t(notification.text)`.

| Key | English | French |
| --- | --- | --- |
| `Player added to the current round as a bye` | Player added to the current round as a bye | Joueur ajouté au round en cours comme exempt |
| `Game broken. Both players have a bye.` | Game broken. Both players have a bye. | Partie cassée. Les deux joueurs sont exempts. |
| `Game created` | Game created | Partie créée |
| `Only an unset game in the current Swiss round can be broken` | Only an unset game in the current Swiss round can be broken | Seule une partie sans résultat du round suisse en cours peut être cassée |
| `Choose two bye players in the current Swiss round` | Choose two bye players in the current Swiss round | Choisissez deux exempts du round suisse en cours |
| `A bye has no result to set` | A bye has no result to set | Un exempt n'a pas de résultat à saisir |
| `Cancel game explanation` | Cancel keeps this game in the round. Both players score 0. Neither player can be paired again in this round. | Annuler conserve la partie dans le round. Les deux joueurs marquent 0. Aucun des deux ne peut être réapparié dans ce round. |
| `Break game explanation` | Break deletes this game. Both players become byes and score 1 point, unless you pair them into a new game before the next round starts. | Casser supprime la partie. Les deux joueurs deviennent exempts et marquent 1 point, sauf s'ils sont réappariés dans une nouvelle partie avant le prochain round. |
| `Break` | Break | Casser |
| `Pair two byes` | Pair two byes | Apparier deux exempts |
| `You have a bye this round` | You have a bye this round. It is worth 1 point. | Vous êtes exempt ce round. Cela vaut 1 point. |
| `These players have already played each other.` | These players have already played each other. | Ces joueurs se sont déjà rencontrés. |
| `White is chosen at random` | White is chosen at random. | Les Blancs sont tirés au sort. |

`Player added` already exists. Keep it for an add that does not create a bye.

## Tests

`tests/Feature/SwissByeAdjustmentTest.php`, with `RefreshDatabase`. Build the user, tournament, pivot, round, and games in the test. Act as the tournament's `created_by` unless the case says otherwise. `User` uses Spatie roles; a user with no role fails `hasRole('admin')` and is denied, which is enough for the 403 case.

- Add to an ongoing Swiss tournament with a round: one bye on the latest round, pivot `score` still 0, `players_count` incremented.
- Add during preparation, to a completed tournament, and to an ongoing knockout: player attached, no game created.
- Add a player who is already enrolled: existing error response, still one membership, no extra game.
- Pair two byes: both bye rows gone, one pending game, the two ids are the two players in either order. The body has no colour field.
- Pair a player who is not a bye, the same player twice, an older round, or as an enrolled non-organiser: 422 or 403, no new game.
- Break an unset game: that row is gone, two byes exist, and `recalculateStandings` then gives each player score 1 and wins 1.
- Break after one result is stored (status still pending), a completed game, a cancelled game, a bye, and a game in an older round: 422, rows unchanged.
- `POST games/{bye}/result` and `POST games/{bye}/set-result`: 422, bye row unchanged.
- Remove a player whose latest-round game is a bye: bye deleted, player detached. `recalculateStandings` does not recreate the membership.
- `POST .../next-round` while a paired game is pending: 422 and no new round. With a bye left in place: the next round is created and that player's score is 1.

## Done when

- A player added to a live Swiss round appears as a bye on the latest round, on the admin rounds tab and on the public page.
- Two byes can be paired into one pending game, and the client cannot choose the colour.
- An unset game can be broken. A game with any result cannot. A bye cannot be given a result.
- Cancel and Break each open their explanation, in English and French.
- A bye that is still there when the next round starts scores 1. A bye that was paired or deleted scores 0.
- Pair and Break remain available on the latest ongoing Swiss round after every game in it is a completed bye.
- Knockout pairing, round-robin pairing, and older rounds behave as they do today.
