<template>
    <v-card>
        <v-card-title class="pimTitleFont" style="font-size: 48px;">
            {{ mainTitle[locale] }}
        </v-card-title>
        <v-card-text>
            <v-row>
                <v-col cols="12" md="8">
                    <v-window v-model="onboarding">
                        <!-- Slide 0: What is the Game of Life? -->
                        <v-window-item :key="0">
                            <v-card flat class="px-2 py-2" min-height="380">
                                <div v-for="(item, i) in slides[0]" :key="i" class="mb-4">
                                    <span :class="item.class" v-html="item[locale]" />
                                </div>
                                <div class="d-flex justify-center align-center">
                                    <img height='180px'  src="/images/ressources/game-of-life/conway.png"/>
                                    <span class="ml-4 text-center">
                                        <div class="text-h4">John Conway</div>
                                        <div>1937 - 2020</div>
                                    </span>
                                </div>
                            </v-card>
                        </v-window-item>
                        <!-- Slide 1: Rules -->
                        <v-window-item :key="1">
                            <v-card flat class="px-2 py-2" min-height="380">
                                <div v-for="(item, i) in slides[1]" :key="i" class="mb-4">
                                    <span :class="item.class" v-html="item[locale]" />
                                </div>
                            </v-card>
                        </v-window-item>
                        <!-- Slide 2: Controls -->
                        <v-window-item :key="2">
                            <v-card flat class="px-2 py-2" min-height="380">
                                <div v-for="(item, i) in slides[2]" :key="i" class="mb-4">
                                    <span :class="item.class" v-html="item[locale]" />
                                </div>
                            </v-card>
                        </v-window-item>
                    </v-window>
                </v-col>
                <v-col
                    cols="4"
                    class="d-none d-md-flex justify-center align-center pimSubtitleFont"
                    style="font-size: 52px; height: 420px;"
                >
                    {{ titles[onboarding][locale] }}
                </v-col>
            </v-row>
        </v-card-text>
        <v-card-actions style="display: block;">
            <div style="display: flex; justify-content: space-around;">
                <v-btn icon @click="prev">
                    <v-icon>mdi-chevron-left</v-icon>
                </v-btn>
                <v-item-group v-model="onboarding" class="text-center" mandatory>
                    <v-item v-for="n in length" :key="`btn-${n}`" v-slot="{ toggle }">
                        <v-btn icon @click="toggle">
                            <v-icon
                                :icon="n === onboarding + 1 ? 'mdi-record-circle-outline' : 'mdi-record'"
                                :color="n === onboarding + 1 ? 'primary' : 'captionColor'"
                            />
                        </v-btn>
                    </v-item>
                </v-item-group>
                <v-btn icon @click="next">
                    <v-icon>mdi-chevron-right</v-icon>
                </v-btn>
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
    en: "Game of Life",
    fr: "Jeu de la vie",
};

const titles = [
    { en: "A game that plays itself", fr: "Un jeu sans joueur" },
    { en: "The Rules", fr: "Les règles" },
    { en: "Controls", fr: "Contrôles" },
];

const length = titles.length;

const slides = [
    // Slide 0
    [
        {
            class: "text-h6 d-block",
            en: "Conway's Game of Life is a <b>cellular automaton</b> devised by mathematician John Horton Conway in 1970.",
            fr: "Le Jeu de la vie de Conway est un <b>automate cellulaire</b> imaginé par le mathématicien John Horton Conway en 1970.",
        },
        {
            class: "text-body-1 d-block",
            en: "The board is an infinite two-dimensional grid of square cells, each of which is either <b>alive</b> or <b>dead</b>.",
            fr: "Le plateau est une grille infinie de cellules carrées, chacune étant soit <b>vivante</b> soit <b>morte</b>.",
        },
        {
            class: "text-body-1 d-block",
            en: "At each time step, the state of every cell is updated simultaneously according to simple rules based on its <b>8 neighbours</b>. Complex and beautiful patterns emerge from these simple rules.",
            fr: "À chaque étape, l'état de chaque cellule est mis à jour simultanément selon des règles simples basées sur ses <b>8 voisins</b>. Des structures complexes et élégantes émergent de ces règles simples.",
        },
    ],
    // Slide 1
    [
        {
            class: "text-h6 d-block",
            en: "The 4 rules applied at each step:",
            fr: "Les 4 règles appliquées à chaque étape :",
        },
        {
            class: "text-body-1 d-block",
            en: "1. A live cell with <b>fewer than 2</b> live neighbours dies (underpopulation).",
            fr: "1. Une cellule vivante avec <b>moins de 2</b> voisins vivants meurt (sous-peuplement).",
        },
        {
            class: "text-body-1 d-block",
            en: "2. A live cell with <b>2 or 3</b> live neighbours survives.",
            fr: "2. Une cellule vivante avec <b>2 ou 3</b> voisins vivants survit.",
        },
        {
            class: "text-body-1 d-block",
            en: "3. A live cell with <b>more than 3</b> live neighbours dies (overpopulation).",
            fr: "3. Une cellule vivante avec <b>plus de 3</b> voisins vivants meurt (surpopulation).",
        },
        {
            class: "text-body-1 d-block",
            en: "4. A dead cell with <b>exactly 3</b> live neighbours becomes alive (reproduction).",
            fr: "4. Une cellule morte avec <b>exactement 3</b> voisins vivants devient vivante (reproduction).",
        },
    ],
    // Slide 2
    [
        {
            class: "text-h6 d-block",
            en: "How to use the simulation:",
            fr: "Comment utiliser la simulation :",
        },
        {
            class: "text-body-1 d-block",
            en: "🖱️ <b>Left click</b> (or drag) on the board to <b>draw</b> live cells.",
            fr: "🖱️ <b>Clic gauche</b> (ou glisser) sur le plateau pour <b>dessiner</b> des cellules vivantes.",
        },
        {
            class: "text-body-1 d-block",
            en: "🖱️ <b>Right click</b> (or drag) on the board to <b>erase</b> cells.",
            fr: "🖱️ <b>Clic droit</b> (ou glisser) sur le plateau pour <b>effacer</b> des cellules.",
        },
        {
            class: "text-body-1 d-block",
            en: "🖱️ <b>Middle mouse drag</b> or <b>drag while running</b> to <b>pan</b> the board.",
            fr: "🖱️ <b>Clic molette glissé</b> ou <b>glisser pendant l'exécution</b> pour <b>déplacer</b> le plateau.",
        },
        {
            class: "text-body-1 d-block",
            en: "🖱️ <b>Mouse wheel</b> or the <b>zoom slider</b> to zoom in and out.",
            fr: "🖱️ <b>Molette</b> ou le <b>curseur de zoom</b> pour zoomer.",
        },
        {
            class: "text-body-1 d-block",
            en: "⏮️ Use the <b>Previous step</b> button to undo up to 100 generations.",
            fr: "⏮️ Utilisez le bouton <b>Étape précédente</b> pour revenir en arrière jusqu'à 100 générations.",
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
