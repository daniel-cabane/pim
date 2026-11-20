<template>
    <v-card>
        <v-card-title class="pimTitleFont" style="font-size: 48px;">
            {{ mainTitle[locale] }}
        </v-card-title>
        <v-card-text>
            <v-row>
                <v-col cols="12" md="8">
                    <v-window v-model="onboarding">
                        <v-window-item v-for="(slide, index) in slides" :key="index">
                            <v-card flat class="px-2 py-2" min-height="380">
                                <div v-for="(item, i) in slide" :key="i" class="mb-4">
                                    <span :class="item.class" v-html="item[locale]" />
                                </div>
                            </v-card>
                        </v-window-item>
                    </v-window>
                </v-col>
                <v-col
                    cols="4"
                    class="d-none d-md-flex justify-center align-center pimSubtitleFont text-center"
                    style="font-size: 42px; height: 420px;"
                >
                    {{ titles[onboarding][locale] }}
                </v-col>
            </v-row>
        </v-card-text>
        <v-card-actions style="display: block;">
            <div style="display: flex; justify-content: space-around;">
                <v-btn icon="mdi-chevron-left" :aria-label="titles[onboarding][locale]" @click="prev" />
                <v-item-group v-model="onboarding" class="text-center" mandatory>
                    <v-item v-for="n in length" :key="`btn-${n}`" v-slot="{ toggle }">
                        <v-btn icon @click="toggle" :aria-label="titles[n - 1][locale]">
                            <v-icon
                                :icon="n === onboarding + 1 ? 'mdi-record-circle-outline' : 'mdi-record'"
                                :color="n === onboarding + 1 ? 'primary' : 'captionColor'"
                            />
                        </v-btn>
                    </v-item>
                </v-item-group>
                <v-btn icon="mdi-chevron-right" :aria-label="titles[onboarding][locale]" @click="next" />
            </div>
            <div style="display: flex;">
                <v-spacer />
                <v-btn color="primary" variant="flat" style="width: 200px;" @click="emit('close')">
                    OK !
                </v-btn>
            </div>
        </v-card-actions>
    </v-card>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();
const emit = defineEmits(['close']);
const onboarding = ref(0);

const mainTitle = {
    en: 'The botanical garden',
    fr: 'Le jardin botanique',
};

const titles = [
    { en: 'The trial', fr: "L'épreuve" },
    { en: 'The rules', fr: 'Les règles' },
    { en: 'Your turn', fr: 'À toi de jouer' },
    { en: 'Your program', fr: 'Ton programme' },
];

const length = titles.length;

const slides = [
    [
        {
            class: 'text-h6 d-block',
            en: 'A city offers its botanical garden to the player who wins a planting trial.',
            fr: 'Une ville confie son jardin botanique au joueur qui remporte une épreuve de plantation.',
        },
        {
            class: 'text-body-1 d-block',
            en: 'Two players, <b>A</b> and <b>B</b>, share a board of nine parcels. <b>A plays first.</b> Each parcel has one small slot (S), one medium slot (M), and one large slot (L).',
            fr: 'Deux joueurs, <b>A</b> et <b>B</b>, partagent un plateau de neuf parcelles. <b>A commence.</b> Chaque parcelle a un emplacement petit (S), un moyen (M) et un grand (L).',
        },
        {
            class: 'text-body-1 d-block',
            en: 'On your turn you plant one plant of your color in an empty slot. A plant never moves, and the other player cannot take that slot.',
            fr: 'À ton tour, tu plantes une plante de ta couleur dans un emplacement libre. Une plante ne bouge plus, et l’autre joueur ne peut pas prendre cet emplacement.',
        },
    ],
    [
        {
            class: 'text-h6 d-block',
            en: 'You win as soon as your own plants form one of these:',
            fr: 'Tu gagnes dès que tes propres plantes forment l’une de ces figures :',
        },
        {
            class: 'text-body-1 d-block',
            en: '1. <b>Three plants of the same size</b> on a row, a column, or a diagonal.',
            fr: '1. <b>Trois plantes de la même taille</b> sur une ligne, une colonne ou une diagonale.',
        },
        {
            class: 'text-body-1 d-block',
            en: '2. <b>S, then M, then L</b> along a line, in either direction. <b>S, L, M does not count.</b>',
            fr: '2. <b>S, puis M, puis L</b> le long d’une ligne, dans un sens ou dans l’autre. <b>S, L, M ne compte pas.</b>',
        },
        {
            class: 'text-body-1 d-block',
            en: '3. <b>S, M, and L in the same parcel.</b> If the other player owns one of those sizes, the parcel is blocked.',
            fr: '3. <b>S, M et L dans la même parcelle.</b> Si l’autre joueur possède une de ces tailles, la parcelle est bloquée.',
        },
    ],
    [
        {
            class: 'text-h6 d-block',
            en: 'Humans play on the same screen. The board stays visible.',
            fr: 'Les humains jouent sur le même écran. Le plateau reste visible.',
        },
        {
            class: 'text-body-1 d-block',
            en: '1. Choose a size: <b>S</b> small, <b>M</b> medium, or <b>L</b> large.',
            fr: '1. Choisis une taille : <b>S</b> petite, <b>M</b> moyenne ou <b>L</b> grande.',
        },
        {
            class: 'text-body-1 d-block',
            en: '2. Then choose a parcel. Columns are <b>A B C</b>, rows are <b>1 2 3</b> from the top. The small number in the corner is the cell index, from 0 to 8.',
            fr: '2. Choisis ensuite une parcelle. Les colonnes sont <b>A B C</b>, les lignes <b>1 2 3</b> depuis le haut. Le petit nombre dans le coin est l’indice de la case, de 0 à 8.',
        },
        {
            class: 'text-body-1 d-block',
            en: 'Green plants marked <b>A</b> belong to player A. Orange plants marked <b>B</b> belong to player B.',
            fr: 'Les plantes vertes marquées <b>A</b> appartiennent au joueur A. Les plantes orange marquées <b>B</b> appartiennent au joueur B.',
        },
    ],
    [
        {
            class: 'text-h6 d-block',
            en: 'The <b>Random</b> tab is already a working program. You can rename it and rewrite it. A new tab starts with a copy of that same code.',
            fr: 'L’onglet <b>Random</b> est déjà un programme qui joue. Tu peux le renommer et le réécrire. Un nouvel onglet commence avec une copie de ce même code.',
        },
        {
            class: 'text-body-1 d-block',
            en: 'Each turn, the page calls <b>chooseMove(state)</b> and plays the move you return. <b>state.you</b> is 0 or 1: your plants are the slots equal to that number. Do not hardcode 0, because the same program may sit in seat B.',
            fr: 'À chaque tour, la page appelle <b>chooseMove(state)</b> et joue le coup renvoyé. <b>state.you</b> vaut 0 ou 1 : tes plantes sont les cases égales à ce nombre. N’écris pas 0 en dur, car le même programme peut être le joueur B.',
        },
        {
            class: 'text-body-1 d-block',
            en: '<b>state.board[cell]</b> is <b>{ S, M, L }</b>. <b>state.legalMoves</b> lists every legal <b>{ size, cell }</b>. A turn that runs longer than <b>1.5 seconds</b> is stopped. A search of the whole game will not finish in time.',
            fr: '<b>state.board[cell]</b> vaut <b>{ S, M, L }</b>. <b>state.legalMoves</b> liste chaque coup légal <b>{ size, cell }</b>. Un tour qui dépasse <b>1,5 seconde</b> est arrêté. Une recherche de toute la partie n’aura pas le temps de finir.',
        },
    ],
];

function next() {
    onboarding.value = onboarding.value + 1 < length ? onboarding.value + 1 : 0;
}

function prev() {
    onboarding.value = onboarding.value - 1 >= 0 ? onboarding.value - 1 : length - 1;
}
</script>
