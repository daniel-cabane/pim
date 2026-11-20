# Le jardin botanique

Implementation spec for a new ressource, slug `jardin-botanique`.

This document is normative. Section 2 is the source story. Section 3 is the rules to code. If a sentence in section 2 can be read two ways, section 3 wins.

## 1. Goal

Build a two-player game about planting a 3×3 botanical garden, and a page where a student writes the program that plays it.

A visitor opens the ressource, chooses a human or a saved program for each seat, and plays. The page starts with one program, named Random, which returns a legal move chosen at random. Its name and its code are fully editable. Every new program tab starts with a copy of that same code. Programs are written in JavaScript in an editor on the same page. The program sees the board and returns one legal move. It cannot touch the page, the network, or the signed-in session.

The page is a classroom tool in the same family as Monty Hall, the biased dice, and the Game of Life: one Vue screen, a tutorial, English and French. It is not an online multiplayer game and it does not record results.

## 2. Source story

A city wants to award the design of a new botanical garden to a landscaping company. Two companies compete. To separate them, the city sets a trial on a model of nine parcels in a 3×3 grid.

In each parcel, three varieties of one family can be planted: a small variety, a medium variety, and a large variety.

The companies alternate turns. Each company has its own plant color. On a turn, a company chooses a parcel and plants one plant of its color and of one of the three sizes. A plant, once placed, cannot be moved.

Several plants may share a parcel only when their sizes differ.

A company wins the trial immediately when its own plants form one of these compositions:

- three plants of the same size on three aligned parcels
- three plants of different sizes on three aligned parcels, in increasing or decreasing order
- all three sizes gathered in the same parcel

Alignments may be horizontal, vertical, or diagonal. The company that wins the trial wins the contract.

## 3. Rules

### Board

Nine parcels. Index them row by row, top to bottom, left to right:

```
0 1 2
3 4 5
6 7 8
```

`cell = row * 3 + col`, with `row` 0 at the top and `col` 0 at the left.

Sizes are `S`, `M`, and `L`, with `S < M < L`. These codes are the API. The interface shows the words (small / petite, medium / moyenne, large / grande) beside the code. Do not translate the codes to `P` / `M` / `G`.

Each parcel has exactly three slots, one per size. A slot is empty or holds one plant. A plant belongs to seat `0` or seat `1`.

One slot holds one plant. The company that fills a slot keeps it. The other company cannot plant that size in that parcel, cannot replace the plant, and cannot move it. A parcel therefore holds at most three plants, and a full game is at most 27 moves.

Both companies plant into the same nine parcels. They block each other by taking slots.

### Seats and turns

Seat `0` is company A. Seat `1` is company B. Seat `0` always plays first, then the companies alternate. There is no pass. A turn is one plant in one empty slot.

The same person or the same program may occupy both seats. When a program occupies seat `1`, it must play from `state.you === 1`. Hardcoding `0` is a wrong program, not a rules exception.

### Legal move

A move is `{ size, cell }`.

- `size` is `S`, `M`, or `L`
- `cell` is an integer from 0 to 8
- that slot is empty

No other move exists. The set of empty slots is the same for both companies. A company is never unable to move while the other still can.

Accept a numeric string for `cell` (`"4"` means `4`). Reject any other type, reject fractional numbers, and reject lowercase sizes.

### Lines

These eight lines, in this order, are the only alignments. There is no wrap, no shorter line, and no other diagonal.

| Kind | Cells, in order |
| --- | --- |
| Row | `[0,1,2]` `[3,4,5]` `[6,7,8]` |
| Column, top to bottom | `[0,3,6]` `[1,4,7]` `[2,5,8]` |
| Diagonal | `[0,4,8]` `[2,4,6]` |

### Winning patterns

After a move, look only at the company that just played, on the board after the plant is placed. The opponent's plants do not count. The opponent cannot complete a pattern on your turn, because a pattern uses only your plants and only you add your plants.

A company wins when at least one of the following is true.

**Same size.** For some line and some size, that company owns that size in all three cells. Other plants in those cells do not matter.

**Ordered sizes.** For some line `[a,b,c]`, that company owns either `S` on `a`, `M` on `b`, and `L` on `c`, or `L` on `a`, `M` on `b`, and `S` on `c`. Both directions count. Any other arrangement of three different sizes does not: `S,L,M` on a line is not a win. Extra plants in those cells do not cancel the pattern, and they do not create a new one.

**Full parcel.** That company owns `S`, `M`, and `L` in the same cell.

One move may complete several patterns. Any one of them wins. The match ends at once. Report every pattern completed by that move, and highlight the union of their cells.

The soonest a win can happen is move 5, which is company A's third plant. Company B cannot win before move 6. Every pattern needs three plants, and A plays moves 1, 3, and 5.

### End of the match

A legal match always ends with a winner, often before the board is full.

This is a property of these rules, checked by enumerating every full board: every board in which a cell is filled with three plants of one company is a parcel win for that company, and every other full board (each cell split 2–1 between the companies) contains a line win for at least one company. A pattern appears on the turn of the company that owns it. Correct detection therefore ends the match on or before the move that fills the last slot. The company about to play is never faced with an empty move list.

If a bug ever produces "no legal move, and no pattern for the company to move", stop and show a draw. That message is a safety net so the page cannot sit there forever. Do not add draw scoring, draw strategy, or extra draw screens.

There is no undo, no resign-as-loss, and no stored result. Abandoning a match throws the board away and does not name a winner.

### Worked positions

Notation: each cell is three characters, `S` then `M` then `L`. `A` is seat 0, `B` is seat 1, `.` is empty. The grid is drawn with cell 0 at the top left.

Same-size win for A (size `S` on the first row):

```
A.. A.. A..
... ... ...
... ... ...
```

Ordered win for A, increasing left to right (`S,M,L`):

```
A.. .A. ..A
... ... ...
... ... ...
```

Ordered win for A, decreasing left to right (`L,M,S`):

```
..A .A. A..
... ... ...
... ... ...
```

Not a win (`S,L,M` is not monotonic):

```
A.. ..A .A.
... ... ...
... ... ...
```

Not a win (the size-`S` row is split between A and B):

```
A.. B.. A..
... ... ...
... ... ...
```

Parcel win for A on cell 0. The same cell with the `L` slot owned by B (`AAB`) is a win for nobody:

```
AAA ... ...     AAB ... ...
... ... ...     ... ... ...
... ... ...     ... ... ...
```

Ordered win on the other diagonal `[2,4,6]`, increasing from the top right (`S` on cell 2, `M` on cell 4, `L` on cell 6):

```
... ... A..
... .A. ...
A.. ... ...
```

Ordered win on the diagonal `[0,4,8]`, increasing from the top left:

```
A.. ... ...
... .A. ...
... ... ..A
```

Ordered win on the column `[2,5,8]`, increasing from the top:

```
... ... A..
... ... .A.
... ... ..A
```

## 4. Pure rules module

Put the rules in `resources/js/components/Ressources/jardin-botanique-rules.js`.

Plain ESM. No Vue, no DOM, no `import.meta`, no path alias. The UI and a Node check script both import it.

Export:

- `LINES` — the eight lines above, in the table order
- `SIZES` — `['S', 'M', 'L']`
- `createBoard()` — nine cells, each `{ S: null, M: null, L: null }`
- `listLegalMoves(board)` — every legal `{ size, cell }`, ordered by cell `0..8` and, within a cell, `S` then `M` then `L`
- `applyMove(board, seat, move)` — does not mutate `board`. Returns `{ board, patterns, winner }`. `winner` is `seat` when `patterns` is non-empty, otherwise `null`. `patterns` lists only the mover's new completions:
  - `{ type: 'same', size, cells }`
  - `{ type: 'order', direction: 'asc' | 'desc', cells }` where `asc` means `S,M,L` along the line as listed in `LINES` and `desc` means `L,M,S`
  - `{ type: 'parcel', cell }`
- Throw if `seat` is not `0` or `1`, if the move is illegal, or if `size` / `cell` fail the parsing rules above. Do not throw merely because the mover already had a pattern before the call; the UI must stop calling once `winner` is set. The function itself is stateless.

`resources/js/components/Ressources/jardin-botanique-rules.check.js` imports the module and throws on the first failed assertion. It must pass with:

```
node resources/js/components/Ressources/jardin-botanique-rules.check.js
```

Required cases:

1. A new board has 27 legal moves and no pattern for either seat.
2. One plant, and two plants, produce no pattern.
3. Each positive example in "Worked positions", including both diagonals, both columns directions, and a row. `applyMove` on the completing plant returns that seat as `winner` and a pattern of the right `type`.
4. Each negative example returns no pattern for either seat.
5. A same-size line is still a win when those cells also contain other sizes.
6. A's plants never count as B's, and B's never count as A's.
7. Replaying a filled slot throws, and the original board is unchanged after a successful `applyMove`.
8. `listLegalMoves` order is cell-major, then `S`, `M`, `L`.
9. Exhaustive check of all `6^9` full boards in which every cell is split 2–1 between the two companies: for every such board, seat 0 or seat 1 (or both) has a winning pattern. Together with a unit check that a cell filled by one company is a parcel win for that company, this locks the "no draw" property. A detector that marks every position as a win must still fail cases 1, 2, and 4.

No Vitest, Jest, or PHP test. The project has no frontend test runner; do not add one.

## 5. Fit in the app

Ressources are client-side Vue screens. The route `/ressources/:slug` already renders the component whose name is the slug, plus a Tutorial button that renders `${slug}-tutorial` and listens for `close`. Files in `resources/js` are auto-registered from their filename. Do not add a route, a Laravel controller, a model, a migration, a policy, or a Pinia store.

Conventions to follow, visible in `game-of-life` (tutorial slides) and `biased-dice` (Vuetify controls):

- Vue 3 `<script setup>`, Vuetify 3 for every control, dialog, tab, select, button, alert, and slider
- User-facing chrome through vue-i18n, keys added to both `resources/locales/en.json` and `resources/locales/fr.json`. The tutorial uses the same inline `{ en, fr }` slide pattern as `game-of-life-tutorial.vue`
- The board is HTML buttons, not a canvas. The grid is nine cells
- Guests can use the page. Nothing requires a signed-in user

### Files

| File | Role |
| --- | --- |
| `resources/js/components/Ressources/jardin-botanique.vue` | The ressource. Must use this filename so the slug resolves |
| `resources/js/components/Ressources/jardin-botanique-tutorial.vue` | Tutorial dialog. Must emit `close` |
| `resources/js/components/Ressources/jardin-botanique-rules.js` | Rules |
| `resources/js/components/Ressources/jardin-botanique-rules.check.js` | Node assertions |
| `resources/js/components/Ressources/jardin-botanique-sandbox.js` | Iframe runner |
| Further `.vue` files in the same folder | Allowed for the board and the editor |
| `resources/js/components/views/ressources-view.vue` | One new catalog entry |
| `public/images/ressources/jardin-botanique/cover.png` | Catalog image, landscape, no baked-in title. A 3×3 garden with two plant colors is enough |
| `resources/locales/en.json`, `resources/locales/fr.json` | Every new string |

`app.js` registers every `.vue` file eagerly. A static import of CodeMirror from `jardin-botanique.vue` would ship the editor on every page of the site. Load CodeMirror with a dynamic `import()` the first time an AI tab is opened.

### Catalog entry

```javascript
{
    title: {
        en: 'The botanical garden',
        fr: 'Le jardin botanique',
    },
    slug: 'jardin-botanique',
    description: {
        en: 'Two companies compete to plant a 3×3 botanical garden. Align three plants, or gather all three sizes in one parcel, to win the contract. Play on one screen, or write a JavaScript program and set it against another.',
        fr: "Deux entreprises concourent pour l'aménagement d'un jardin botanique de neuf parcelles. Remportez le marché en alignant trois plantes, ou en réunissant les trois tailles dans une même parcelle. Jouez à deux sur le même écran, ou programmez une IA en JavaScript.",
    },
    themes: [
        { en: 'Algorithms', fr: 'Algorithmes' },
        { en: 'Strategy', fr: 'Stratégie' },
        { en: 'Programming', fr: 'Programmation' },
    ],
}
```

### Tutorial

Four slides, same shell as the Game of Life tutorial (window, dots, OK button that emits `close`):

1. The trial and the contract. What a seat is, and that A plays first.
2. The three winning compositions, including that `S,L,M` does not count, and that a taken slot blocks the other company.
3. How a human plays: size, then parcel. What the colors and the `S` / `M` / `L` marks mean.
4. How a program plays: the Random tab that is already there, `state.you`, `state.board`, `state.legalMoves`, the 1.5s limit, and the fact that a search of the whole game will be stopped. Say that Random can be rewritten, that a new tab starts from the same code, and that a program used on seat B must trust `state.you` and must not hardcode `0`.

The page shell already has the Tutorial button. Do not add a second one.

### Board presentation

The surrounding page follows the light and dark theme. The board itself uses a fixed palette so plant colors survive both themes: paper `#f4f0e6`, cell `#fffdf8`, grid `#c4b49a`, company A `#1b7f4e`, company B `#e07a1f`. Define those in one place.

Each cell shows three slots. Use MDI icons `mdi-sprout`, `mdi-flower`, and `mdi-tree` for `S`, `M`, and `L`. Color is the company. Each plant also shows `A` or `B`, so the board is readable without color. An empty slot is a faint outline of the same icon. Show the cell index in small type in the corner (`0`…`8`) and label columns `A B C` and rows `1 2 3` from the top, so cell 0 is `A1` and cell 4 is `B2`. The move log is two columns, one per player. Each move shows that player's plant icon, in that player's color, followed by the cell coordinates (`B2`), not the cell index.

Highlight the last plant. On a win, highlight every cell that belongs to a reported pattern. A parcel win highlights that one cell.

The board is the focus of the game tab, between about 320px and 560px, centered. Size buttons sit above it and are large enough to tap.

## 6. Match flow

### Setup

The game tab opens in setup. Two selects, one per seat:

- Human
- then each saved program, by its current name

On a fresh browser the only program is Random, so each seat offers Human and Random. Start is enabled when both seats have a choice. The same program may be used twice. Random is that saved program: its turns run through the sandbox, like every other program. There is no second Random hidden outside the editor.

A saved program appears in the list only when its name is valid and its code is not empty. If the user clears the code of the selected program before the start, clear that seat.

While a match is running, disable both selects. After a win, or after abandon, enable them again and keep the previous choices.

### Human turn

Humans on one computer play with the board visible to both. Information is public; do not hide the board between turns.

1. Status line: who plays, and "choose a size".
2. The player picks `S`, `M`, or `L`. A size with no empty slot left is disabled. Cells do not accept a plant yet.
3. Status line: "choose a parcel". Cells where that size is free are buttons. Cells where that size is taken are disabled. Clicking a taken cell does not consume the turn; show one line: "This parcel already has a plant of this size."
4. A legal click places the plant, clears the size selection, appends the move log, and passes the turn.
5. Choosing another size before the click replaces the selection.

Ignore clicks when it is not a human turn.

### Program turn

Read that program's current code at the start of the turn (not a snapshot from the start of the match), so a student can fix the program between turns.

Show that the program is thinking. Run the sandbox. Ignore human clicks while it runs.

- Legal move: place it, log it, then wait the delay below before the next turn.
- Throw, timeout, syntax error, missing `chooseMove`, empty code, or illegal move: place nothing, do not pass the turn. Show the error on the game tab and in the console. The match waits on a Retry button. Retry reads the code again and reruns the same turn. Editing the program is allowed while waiting.

Abandon during a run destroys the iframe and ignores any late result.

### Delay

When the seat about to play is not human, wait a delay so the plant can be seen. Slider from 0 to 2000ms, step 100, default 600. At 0, still wait one animation frame so the board paints between moves. No delay before a human turn. Persist the slider value with the saved programs.

There is no step debugger and no pause control. The slider is the whole watch tool. The error-and-retry state is the whole debugging tool.

### End and abandon

On a win, an overlay on the size buttons names the winner (the program's current name, or "Player A" / "Joueur A" for a human) and lists each pattern in a sentence ("Three medium plants in a line", "Sizes in increasing order", "Three sizes in one parcel"). The same overlay shows "Choose who plays" before the match and a draw if the safety net is reached. It does not push the board. The primary button becomes Play again and starts a new match with the same seats.

Abandon is available during a match, behind a confirmation dialog, and returns to setup without a winner. The move log clears on Play again and on Abandon. It shows every move of the current match (at most 27), oldest at the top, scrolled to the latest.

## 7. Programs

### Tabs

Vuetify tabs.

- First tab: the game. It cannot be closed.
- Then one tab per saved program. Start with a single program named `Random`, containing the starter code below. The page therefore opens on two tabs. Random counts as one of the six programs.
- A `+` control adds a program, up to 6 saved programs. At 6, disable `+` and explain why.
- A new program is named `AI 2`, `AI 3`, … using the first free number. Its code is a copy of the starter code below, including when the Random tab has been edited, renamed, or deleted. Editing the new tab does not change Random. Its tab is selected.
- A tab can be closed. Ask for confirmation every time. Closing is refused while a match is running and that program occupies a seat. If it was selected in setup and the match has not started, clear that seat.
- Keep editor instances alive across tab changes (`v-show` once created) so the cursor and undo history survive.

### Names

The name field is always visible above the editor, with a Docs button on the same row. The button opens a dialog, in English and French, with tabs for `chooseMove`, every field of `state`, the board numbering, the winning patterns, and the time limit, console, and errors. A program with a blank name cannot be put in a seat; the editor still works. This is the "name it before you use it" rule.

A name is valid when, after trim, it is 1 to 24 characters, unique among saved programs ignoring case, and not `human` or `humain` in any case. `Random` is a valid name. Show the reason next to the field. Keep the typed capitalization.

### Editor

CodeMirror 6, packages `codemirror`, `@codemirror/lang-javascript`, and `@codemirror/theme-one-dark` only. Do not add Monaco, Ace, `vue-codemirror`, or `vue-codemirror6`.

JavaScript highlighting, line numbers, the default setup. Use the one-dark theme when the app theme is `customDark`, and the default light theme otherwise. No language server, no formatter, no npm imports, no extra files.

Cap a program at 50 000 characters. Past that, refuse the extra input and show why. Do not send a longer program to the sandbox.

### Starter code

Use this text exactly for the default Random program and as the initial code of every new program:

```javascript
/**
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
```

The contract is the return value of `chooseMove`. There is no `play(size, cell)`. A side-effecting `play` would give student code a callback into the page and would make a second call ambiguous. One returned object is the whole turn.

`chooseMove` must be synchronous. If it returns a Promise, that is an error ("return a move, not a Promise"). Do not `await` student code.

### State passed to the program

A JSON object, built fresh each turn:

```javascript
{
  you: 0,
  board: [ /* 9 cells, { S, M, L } values are 0, 1, or null */ ],
  legalMoves: [ /* listLegalMoves(board) */ ],
  history: [ /* { seat, size, cell }, oldest first */ ]
}
```

`you` equals the seat to move. Owners on the board are numbers, not strings. Document that in the tutorial so students do not compare with `"0"`.

Do not pass functions, the DOM, Vue proxies, or a live reference. `JSON.parse(JSON.stringify(state))` before the run. Mutations of the received object have no effect on the match. Only `size` and `cell` on the returned object are read.

### Console

A read-only log under the editor. It is not a REPL: nothing typed there runs.

Each program turn appends a header (`Random · move 7`), then the captured `console.log` / `info` / `warn` / `error` / `debug` lines, then the move or the error. Random is included, because it is a program. Human turns are not logged here; they appear in the move log.

Cap one turn at 50 console lines inside the iframe, then suppress. Cap the panel at 200 lines, dropping the oldest. A Clear button empties the panel. Stringify objects with a depth limit and a cycle guard, and truncate long strings, so a logged board cannot freeze the page.

### Sandbox

Student code runs in a sandboxed iframe, never on the page thread and never in a same-origin Worker. A same-origin Worker can `fetch` the Laravel API with the user's cookies. That is unacceptable on this app. A JS interpreter (JS-Interpreter, QuickJS, or similar) is also out of scope: the iframe is the boundary.

For each turn:

1. Create an iframe with `sandbox="allow-scripts"` and no other sandbox flag. In particular, do not set `allow-same-origin`.
2. Its document is `srcdoc` and starts with `<meta http-equiv="Content-Security-Policy" content="default-src 'none'; script-src 'unsafe-inline' 'unsafe-eval';">`. `unsafe-eval` is required so the frame can compile `chooseMove`. It does not grant network access or access to the parent page.
3. The parent sends `{ type: 'run', id, code, state }`. `id` is a fresh nonce. `state` is the JSON object above.
4. The iframe keeps `const send = parent.postMessage.bind(parent)` before the student code runs. It then builds a function in the iframe, with parameters `state` and `console`, whose body is exactly: `"use strict";`, then the student source, then `if (typeof chooseMove !== "function") throw new Error("Define function chooseMove(state)");`, then `return chooseMove(state);`.
5. The function's console stores at most 50 `{ level, text }` entries.
6. The iframe sends `{ type: 'result', id, ok: true, move, logs }` or `{ type: 'result', id, ok: false, error, logs }`.
7. The parent accepts a message only when `event.source` is that iframe, `event.origin` is `"null"`, `event.data.type` is `"result"`, and `event.data.id` is the nonce. Anything else is ignored.
8. If no accepted result arrives within 1500ms, remove the iframe, report a time limit, and ignore a late message with that nonce.
9. Remove the iframe after every result, including failure. One run at a time.

The parent page must not `eval` student code and must not construct a function from it. An infinite loop is stopped by destroying the iframe; it must not freeze Vue. A student can still burn memory inside the iframe until the browser kills that frame. Do not try to solve that with a second runtime.

Syntax errors, thrown errors, a missing function, a bad return, and the time limit all use the same Retry path from section 6.

## 8. Persistence

Save in `localStorage` under `pim.jardin-botanique.v1`:

```javascript
{
  programs: [{ id, name, code }],
  delay: 600
}
```

`id` comes from `crypto.randomUUID()`. Debounce writes by 300ms. On a parse error or a missing key, fall back to a single program named `Random` with the starter code and delay 600. If `localStorage` throws, keep the session in memory and show one warning.

The match itself is not saved. A refresh returns to setup with the programs intact.

This is per browser, on purpose. Two students on the same machine and the same browser profile share and overwrite these programs. Do not add an account API. Guests must be able to keep their programs on this browser without signing in.

## 9. Scale

The match runs entirely in the visitor's browser. A move does not call Laravel. One thousand, five thousand, or ten thousand people playing at once are one thousand, five thousand, or ten thousand browsers downloading the SPA, the same cost as the other ressources.

Keep it that way:

- No request per move, no polling, no websocket, no server-side execution of student code
- CodeMirror in its own chunk, loaded on first use of an AI tab
- One iframe, destroyed after the turn
- Debounced `localStorage`
- Nine buttons, no game loop, no canvas

There is no game bottleneck at 10k concurrent players. The limit that does exist is the site's existing capacity to serve the SPA, and it is shared with every other page. Running student programs on the server would create a bottleneck this design does not have, and it would mean executing untrusted code on the school server. Do not add it in the name of scale.

## 10. Build order

1. Rules module and the check script. Stop when the script exits 0.
2. Game tab: human vs human, then human vs Random, with the delay.
3. Catalog entry, cover, tutorial, both locale files.
4. Editor, sandbox, program seats, Retry, `localStorage`.

## 11. Acceptance

The check script exits 0.

On the page, in both locales and in both themes:

- The catalog card links to `/ressources/jardin-botanique` and shows the cover.
- Tutorial opens, paginates, and closes.
- Start stays disabled until both seats are chosen, then a human-vs-human match can be played to a win by a same-size line, by an ordered line, and by a full parcel. `S,L,M` does not win.
- A taken slot cannot be played by either company. The plant never moves.
- Player A moves first. The overlay, the highlight, and the move log agree on the winner and the cells.
- On a fresh profile there is one program tab named Random, its code is the starter, and both the name and the code can be edited.
- Adding a tab gives it a different name and a copy of the starter. Editing that copy leaves the Random tab unchanged. A new tab still receives the starter after Random itself has been rewritten.
- Random in both seats finishes on its own, the board updates between moves, and delay 0 does not freeze the tab. Those turns run in the sandbox.
- Random, assigned to seat B against a human, plays as seat 1 and can win or lose without throwing.
- `while (true) {}` does not freeze the page; within about 1.5s the turn fails and Retry works.
- A program that returns a full slot, a string size, or no value does not change the board and does not pass the turn.
- `document.body` inside `chooseMove` cannot read or modify the app. A program cannot call the API as the signed-in user.
- Closing and reopening the browser keeps the programs and drops the in-progress match.
- A seventh program cannot be added. Two programs cannot share a name. The game tab cannot be closed.
- Play again keeps the seats and clears the board. Abandon asks for confirmation and does not name a winner.
- At a phone width, a human can pick a size and a parcel. The editor remains usable; tabs scroll.

## 12. Out of scope

- Online multiplayer, rooms, spectating, hidden programs, accounts, sharing a program by link
- Saving code on the server, tournaments, leaderboards, ratings, history beyond the current match
- Undo, hints, sound, a REPL, a step debugger, more than two seats, any board other than 3×3
- Languages other than JavaScript, imports and npm inside student code, async `chooseMove`
- Monaco, Ace, a Vue CodeMirror wrapper, QuickJS, JS-Interpreter, a same-origin Worker
- Pinia, new API routes, PHP tests, a new frontend test runner
