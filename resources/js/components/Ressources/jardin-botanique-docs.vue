<template>
    <v-dialog
        :model-value="modelValue"
        max-width="880"
        scrollable
        @update:model-value="emit('update:modelValue', $event)"
    >
        <v-card>
            <v-card-title class="d-flex align-center">
                <span class="pimTitleFont docs-title">{{ text(title) }}</span>
                <v-spacer />
                <v-btn icon="mdi-close" variant="text" :aria-label="$t('Close')" @click="emit('update:modelValue', false)" />
            </v-card-title>
            <v-tabs v-model="tab" color="primary" show-arrows>
                <v-tab v-for="item in tabs" :key="item.id" :value="item.id">{{ text(item.title) }}</v-tab>
            </v-tabs>
            <v-card-text class="docs-body">
                <template v-for="item in tabs" :key="item.id">
                    <div v-if="tab === item.id">
                    <template v-for="(block, index) in item.blocks" :key="index">
                        <h3 v-if="block.kind === 'h'" class="text-h6 mt-4 mb-2">{{ text(block) }}</h3>
                        <p v-else-if="block.kind === 'p'" class="mb-3">{{ text(block) }}</p>
                        <pre v-else-if="block.kind === 'code'" class="docs-code">{{ block.text }}</pre>
                        <ul v-else-if="block.kind === 'ul'" class="mb-3">
                            <li v-for="(entry, entryIndex) in block.items" :key="entryIndex" class="mb-1">{{ text(entry) }}</li>
                        </ul>
                        <v-table v-else-if="block.kind === 'table'" density="compact" class="docs-table mb-3">
                            <thead>
                                <tr>
                                    <th v-for="(header, headerIndex) in block.headers" :key="headerIndex">{{ text(header) }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(row, rowIndex) in block.rows" :key="rowIndex">
                                    <td v-for="(cell, cellIndex) in row" :key="cellIndex">
                                        <code v-if="cell.code">{{ cell.code }}</code>
                                        <span v-else>{{ text(cell) }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </v-table>
                    </template>
                    </div>
                </template>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

defineProps({ modelValue: Boolean });
const emit = defineEmits(['update:modelValue']);

const { locale } = useI18n();
const tab = ref('function');

function text(value) {
    if (!value) return '';
    if (typeof value === 'string') return value;
    return value[locale.value] || value.en;
}

const title = { en: 'Coding guide', fr: 'Guide de programmation' };

const en = (english, french) => ({ en: english, fr: french });
const codeCell = (source) => ({ code: source });

const tabs = [
    {
        id: 'function',
        title: en('Function', 'Fonction'),
        blocks: [
            { kind: 'h', ...en('chooseMove(state)', 'chooseMove(state)') },
            { kind: 'p', ...en(
                'The page calls this function once on each of your turns. The name must be exactly chooseMove.',
                'La page appelle cette fonction une fois à chacun de tes tours. Le nom doit être exactement chooseMove.',
            ) },
            { kind: 'code', text:
`function chooseMove(state) {
  return state.legalMoves[0];
}` },
            { kind: 'h', ...en('What you return', 'Ce que tu renvoies') },
            { kind: 'p', ...en(
                'One object: { size, cell }. That object is the whole turn. There is no play() function.',
                'Un seul objet : { size, cell }. Cet objet est tout le tour. Il n’y a pas de fonction play().',
            ) },
            { kind: 'table',
                headers: [en('Field', 'Champ'), en('Accepted value', 'Valeur acceptée')],
                rows: [
                    [codeCell('size'), en('"S", "M", or "L". Lowercase is rejected.', '"S", "M" ou "L". Une minuscule est refusée.')],
                    [codeCell('cell'), en('A number from 0 to 8, or the string "0" to "8".', 'Un nombre de 0 à 8, ou le texte "0" à "8".')],
                    [en('anything else', 'le reste'), en('Ignored.', 'Ignoré.')],
                ],
            },
            { kind: 'p', ...en(
                'The move must be one of state.legalMoves. If the slot is already full, the size is wrong, or you return nothing, no plant is placed and the turn does not pass. The match waits so you can correct the code and press Retry.',
                'Le coup doit être dans state.legalMoves. Si l’emplacement est déjà pris, si la taille est mauvaise, ou si tu ne renvoies rien, aucune plante n’est posée et le tour ne change pas. La partie attend pour que tu corriges le code et appuies sur Réessayer.',
            ) },
            { kind: 'h', ...en('It has to finish on the spot', 'Le coup doit être renvoyé tout de suite') },
            { kind: 'p', ...en(
                'chooseMove must return the move itself, not a Promise. Do not mark it async, and do not use await. A return written outside the function ends the turn before chooseMove runs.',
                'chooseMove doit renvoyer le coup lui-même, pas une Promise. Ne la marque pas async, et n’utilise pas await. Un return écrit en dehors de la fonction termine le tour avant l’appel de chooseMove.',
            ) },
            { kind: 'h', ...en('Helpers', 'Fonctions auxiliaires') },
            { kind: 'p', ...en(
                'You can write as many other functions as you want. Only chooseMove is called.',
                'Tu peux écrire autant d’autres fonctions que tu veux. Seule chooseMove est appelée.',
            ) },
            { kind: 'code', text:
`function movesOfSize(state, size) {
  return state.legalMoves.filter(move => move.size === size);
}

function chooseMove(state) {
  const small = movesOfSize(state, "S");
  return small[0] || state.legalMoves[0];
}` },
        ],
    },
    {
        id: 'state',
        title: en('state', 'state'),
        blocks: [
            { kind: 'p', ...en(
                'state is a new copy on every turn. Changing it does not change the real board. Owners are the numbers 0 and 1, not the strings "0" and "1".',
                'state est une copie neuve à chaque tour. Le modifier ne change pas le vrai plateau. Les propriétaires sont les nombres 0 et 1, pas les textes "0" et "1".',
            ) },
            { kind: 'table',
                headers: [en('Name', 'Nom'), en('Meaning', 'Sens')],
                rows: [
                    [codeCell('state.you'), en('0 when you are player A, 1 when you are player B. Do not hardcode 0: the same program can sit in either seat.', '0 si tu es le joueur A, 1 si tu es le joueur B. N’écris pas 0 en dur : le même programme peut occuper l’un ou l’autre siège.')],
                    [codeCell('state.board'), en('The nine cells, from index 0 to 8. See the Board tab.', 'Les neuf cases, de l’indice 0 à 8. Voir l’onglet Plateau.')],
                    [codeCell('state.board[cell].S'), en('null if empty, 0 if player A owns it, 1 if player B owns it. .M and .L work the same way.', 'null si c’est vide, 0 si le joueur A l’occupe, 1 si le joueur B l’occupe. .M et .L fonctionnent pareil.')],
                    [codeCell('state.legalMoves'), en('Every move you may play now. Each item is { size, cell }. Ordered by cell 0, then 1, up to 8. Inside a cell: S, then M, then L.', 'Tous les coups autorisés maintenant. Chaque élément est { size, cell }. Ordre : case 0, puis 1, jusqu’à 8. Dans une case : S, puis M, puis L.')],
                    [codeCell('state.history'), en('Moves already played, oldest first. Each item is { seat, size, cell }. seat is 0 or 1.', 'Les coups déjà joués, du plus ancien au plus récent. Chaque élément est { seat, size, cell }. seat vaut 0 ou 1.')],
                ],
            },
            { kind: 'p', ...en(
                'Nothing else is given to you. There is no list of winning lines, no score, and no separate opponent object. Your plants are the slots equal to state.you. The other number is the other player.',
                'Rien d’autre ne t’est donné. Il n’y a pas de liste des lignes gagnantes, pas de score, et pas d’objet adverse. Tes plantes sont les emplacements égaux à state.you. L’autre nombre est l’autre joueur.',
            ) },
            { kind: 'h', ...en('console', 'console') },
            { kind: 'p', ...en(
                'console.log, console.info, console.warn, console.error, and console.debug are copied into the console under the editor. They do not play a move. A turn keeps at most 50 lines. Long values are shortened.',
                'console.log, console.info, console.warn, console.error et console.debug sont copiés dans la console sous l’éditeur. Ils ne jouent pas un coup. Un tour garde au plus 50 lignes. Les longues valeurs sont raccourcies.',
            ) },
            { kind: 'code', text:
`function chooseMove(state) {
  console.log(state.you, state.legalMoves.length);
  const moves = state.legalMoves;
  return moves[Math.floor(Math.random() * moves.length)];
}` },
        ],
    },
    {
        id: 'board',
        title: en('Board', 'Plateau'),
        blocks: [
            { kind: 'p', ...en(
                'Cells are numbered from the top left. Columns are A B C, rows are 1 2 3 from the top. The coordinates are what the move log shows. The number is what state uses.',
                'Les cases sont numérotées depuis le haut à gauche. Les colonnes sont A B C, les lignes 1 2 3 depuis le haut. Les coordonnées sont celles du journal des coups. Le nombre est celui qu’utilise state.',
            ) },
            { kind: 'code', text:
`     A   B   C
1    0   1   2
2    3   4   5
3    6   7   8` },
            { kind: 'p', ...en(
                'cell = row * 3 + col, with row 0 at the top and col 0 on the left. Cell 4 is B2.',
                'cell = row * 3 + col, avec row 0 en haut et col 0 à gauche. La case 4 est B2.',
            ) },
            { kind: 'code', text:
`function place(cell) {
  return {
    row: Math.floor(cell / 3),
    col: cell % 3,
  };
}` },
            { kind: 'h', ...en('Sizes', 'Tailles') },
            { kind: 'p', ...en(
                'S is small, M is medium, L is large, and S < M < L. Each cell has one slot of each size. null means the slot is empty. A cell never holds two plants of the same size.',
                'S est petite, M est moyenne, L est grande, et S < M < L. Chaque case a un emplacement de chaque taille. null veut dire que l’emplacement est vide. Une case ne contient jamais deux plantes de la même taille.',
            ) },
            { kind: 'h', ...en('The eight lines', 'Les huit lignes') },
            { kind: 'p', ...en(
                'These are the only lines, written from the start of the line toward its end.',
                'Ce sont les seules lignes, écrites du début de la ligne vers sa fin.',
            ) },
            { kind: 'code', text:
`[0, 1, 2]   [3, 4, 5]   [6, 7, 8]   rows
[0, 3, 6]   [1, 4, 7]   [2, 5, 8]   columns, top to bottom
[0, 4, 8]   [2, 4, 6]               diagonals` },
        ],
    },
    {
        id: 'rules',
        title: en('Rules', 'Règles'),
        blocks: [
            { kind: 'p', ...en(
                'After your move, the page looks only at your plants: the slots whose value is state.you. The other player’s plants do not count for you, and you cannot complete their pattern.',
                'Après ton coup, la page ne regarde que tes plantes : les emplacements dont la valeur est state.you. Les plantes de l’autre joueur ne comptent pas pour toi, et tu ne peux pas compléter sa figure.',
            ) },
            { kind: 'ul', items: [
                en('Three of your plants of one size on one of the eight lines.', 'Trois de tes plantes d’une même taille sur l’une des huit lignes.'),
                en('S, then M, then L along a line, in that direction or in reverse. S, L, M is not a win.', 'S, puis M, puis L le long d’une ligne, dans ce sens ou dans l’autre. S, L, M n’est pas une victoire.'),
                en('S, M, and L that you own in the same cell.', 'S, M et L qui t’appartiennent dans la même case.'),
            ] },
            { kind: 'p', ...en(
                'The match ends on the move that completes any of these. One move can complete more than one. The earliest possible win is player A’s third plant, which is move 5. Player B cannot win before move 6.',
                'La partie s’arrête au coup qui complète l’une de ces figures. Un coup peut en compléter plusieurs. La victoire la plus tôt possible est la troisième plante du joueur A, au coup 5. Le joueur B ne peut pas gagner avant le coup 6.',
            ) },
            { kind: 'p', ...en(
                'A filled slot is blocked for both players. Plants are never moved. If a size is already taken in a cell, that pair is simply absent from state.legalMoves.',
                'Un emplacement rempli est bloqué pour les deux joueurs. Les plantes ne bougent jamais. Si une taille est déjà prise dans une case, cette paire n’est simplement pas dans state.legalMoves.',
            ) },
        ],
    },
    {
        id: 'limits',
        title: en('Limits', 'Limites'),
        blocks: [
            { kind: 'p', ...en(
                'You can use ordinary JavaScript: variables, loops, functions, arrays, objects, Math, and JSON. You cannot read the page, call the network, or load a library.',
                'Tu peux utiliser le JavaScript ordinaire : variables, boucles, fonctions, tableaux, objets, Math et JSON. Tu ne peux pas lire la page, appeler le réseau, ou charger une bibliothèque.',
            ) },
            { kind: 'ul', items: [
                en('A turn is stopped after 1.5 seconds. Searching every future move of the whole game will not finish.', 'Un tour est arrêté après 1,5 seconde. Chercher tous les coups futurs de la partie entière ne finira pas.'),
                en('The program can be at most 50 000 characters.', 'Le programme peut faire au plus 50 000 caractères.'),
                en('import, require, and fetch do not work.', 'import, require et fetch ne marchent pas.'),
                en('document and the rest of the page are out of reach. Reading them cannot see the board.', 'document et le reste de la page sont hors d’atteinte. Les lire ne montre pas le plateau.'),
            ] },
            { kind: 'h', ...en('When a turn fails', 'Quand un tour échoue') },
            { kind: 'p', ...en(
                'A syntax error, a thrown error, a missing chooseMove, a time limit, or an illegal move leaves the board unchanged. The error is shown on the game tab and in the console. Correct the code and press Retry.',
                'Une erreur de syntaxe, une erreur lancée, un chooseMove manquant, un temps dépassé ou un coup illégal laissent le plateau inchangé. L’erreur s’affiche dans l’onglet de la partie et dans la console. Corrige le code et appuie sur Réessayer.',
            ) },
            { kind: 'code', text:
`function chooseMove(state) {
  const moves = state.legalMoves;
  return moves[Math.floor(Math.random() * moves.length)];
}` },
            { kind: 'p', ...en(
                'That program is a legal player. It is the code in the Random tab, and the code a new tab starts with.',
                'Ce programme est un joueur légal. C’est le code de l’onglet Random, et le code avec lequel un nouvel onglet commence.',
            ) },
        ],
    },
];
</script>

<style scoped>
.docs-title { font-size: 1.75rem; }
.docs-body { max-height: 70vh; }
.docs-code {
    margin: 0 0 16px;
    padding: 12px 14px;
    overflow: auto;
    border-radius: 8px;
    background: #1e1e1e;
    color: #e8e8e8;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 13px;
    line-height: 1.45;
    white-space: pre;
}
.docs-table code { font-size: 0.92em; }
</style>
