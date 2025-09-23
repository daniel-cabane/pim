<template>
    <v-card>
        <v-card-title class='pimTitleFont' style="font-size:56px;">
            {{ mainTitle[locale] }}
        </v-card-title>
        <v-row class='fill-height'>
            <v-col cols='12' md="8">
                <v-window v-model="onboarding">
                    <v-window-item :key="`card-${0}`">
                        <v-card class='px-6 py-2' flat height="550">
                            <v-row>
                                <v-col cols='12' class='text-h6'>
                                    {{ text[0][locale] }}
                                </v-col>
                                <v-col class="d-flex justify-center" cols='4' v-for="i in 3" :key="`doors-1-${i}`">
                                    <img height='180px' contain src="/images/ressources/monty-hall/door.png"/>
                                </v-col>
                                <v-col cols='12' class='text-h6'>
                                    {{ text[1][locale] }}
                                </v-col>
                                <v-col cols='6' style='display:flex;flex-direction:column;align-items:center'>
                                    <img height='180px'  src="/images/ressources/monty-hall/door-goat.png"/>
                                    <span class='text-h4 font-weight-bold'>
                                        ×2
                                    </span>
                                </v-col>
                                <v-col cols='6' style='display:flex;flex-direction:column;align-items:center'>
                                    <img height='180px' contain src="/images/ressources/monty-hall/door-car.png"/>
                                    <span class='text-h4 font-weight-bold'>
                                        ×1
                                    </span>
                                </v-col>
                            </v-row>
                        </v-card>
                    </v-window-item>
                    <v-window-item :key="`card-${1}`">
                        <v-card class='px-6 py-2' flat height="550">
                            <v-row>
                                <v-col cols='12' class='text-h6'>
                                    {{ text[2][locale] }}
                                </v-col>
                                <v-col cols='6' style='display:flex;flex-direction:column;align-items:center'>
                                    <span class='text-h6'>
                                        {{ text[3][locale] }}
                                    </span>
                                    <img height='180px' src="/images/ressources/monty-hall/door-car-selected.png"/>
                                    <img class="mt-8" height='180px' src="/images/ressources/monty-hall/youwin.png"/>
                                </v-col>
                                <v-col cols='6' style='display:flex;flex-direction:column;align-items:center'>
                                    <span class='text-h6'>
                                        {{ text[4][locale] }}
                                    </span>
                                    <img height='180px' src="/images/ressources/monty-hall/door-goat-selected.png"/>
                                    <img class="mt-8" height='180px' src="/images/ressources/monty-hall/youlose.png"/>
                                </v-col>
                            </v-row>
                        </v-card>
                    </v-window-item>
                    <v-window-item :key="`card-${2}`">
                        <v-card class='px-6 py-2' flat height="550">
                            <v-row>
                                <v-col cols='12' class='text-h6'>
                                    {{ text[5][locale] }}
                                </v-col>
                                <v-col class="d-flex justify-center" cols='4'>
                                    <img height='180px' src="/images/ressources/monty-hall/door-selected.png"/>
                                </v-col>
                                <v-col class="d-flex justify-center" cols='4' v-for="i in 2" :key="`doors-3-${i}`">
                                    <img height='180px' src="/images/ressources/monty-hall/door.png"/>
                                </v-col>
                                <v-col cols='12' class='text-h6'>
                                    {{ text[6][locale] }}
                                </v-col>
                                <v-col class="d-flex justify-center" cols='4'>
                                    <img height='180px' src="/images/ressources/monty-hall/door-selected.png"/>
                                </v-col>
                                <v-col class="d-flex justify-center" cols='4'>
                                    <img height='180px' src="/images/ressources/monty-hall/door-goat.png"/>
                                </v-col>
                                <v-col class="d-flex justify-center" cols='4'>
                                    <img height='180px' src="/images/ressources/monty-hall/door.png"/>
                                </v-col>
                            </v-row>
                        </v-card>
                    </v-window-item>
                    <v-window-item :key="`card-${3}`">
                        <v-card class='px-6 py-2' flat height="550">
                            <v-row>
                                <v-col cols='12' class='text-h6'>
                                    {{ text[7][locale] }}                                    
                                </v-col>
                                <v-col cols='12'>
                                    <v-row>
                                        <v-col cols='4' class='text-center'>
                                            <v-btn class="mb-1" color="white" width='200px' :text="$t('Keep')"/>
                                            <div>
                                                {{ text[9][locale] }}
                                            </div>
                                        </v-col>
                                        <v-col cols='4' class='text-h2 text-center'>
                                            {{ text[8][locale] }}
                                        </v-col>
                                        <v-col cols='4' class='text-center'>
                                            <v-btn class="mb-1" color="black" width='200px' dark :text="$t('Change')"/>
                                            <div>
                                                {{ text[10][locale] }}
                                            </div>
                                        </v-col>
                                        <v-col class="d-flex justify-center" cols='4'>
                                            <img height='180px' src="/images/ressources/monty-hall/door-selected.png"/>
                                        </v-col>
                                        <v-col class="d-flex justify-center" cols='4'>
                                            <img height='180px' src="/images/ressources/monty-hall/door-goat.png"/>
                                        </v-col>
                                        <v-col class="d-flex justify-center" cols='4'>
                                            <img height='180px' src="/images/ressources/monty-hall/door.png"/>
                                        </v-col>
                                    </v-row>
                                </v-col>
                                <v-col cols='12' class='text-h4 mt-8 pb-0'>
                                    {{ text[11][locale] }}
                                </v-col>
                                <v-col cols='4' class='text-h6'>
                                    <span class='font-weight-bold'>1)</span> {{ text[12][locale] }} <v-chip label variant="flat" color='white' small class='elevation-5'>{{ $t('Keep').toUpperCase() }}</v-chip> {{ text[9][locale] }}.
                                </v-col>
                                <v-col cols='4' class='text-h6'>
                                    <span class='font-weight-bold'>2)</span> {{ text[12][locale] }} <v-chip label variant="flat" color='black' text-color='white' small class='elevation-5'>{{ $t('Change').toUpperCase() }}</v-chip> {{ text[10][locale] }}.
                                </v-col>
                                <v-col cols='4' class='text-h6'>
                                    <span class='font-weight-bold'>3)</span> {{ text[13][locale] }}
                                </v-col>
                                <v-col cols='12'>
                                </v-col>
                            </v-row>
                        </v-card>
                    </v-window-item>
                    <v-window-item :key="`card-${4}`">
                        <v-card class='px-6 py-2' flat height="550" style="overflow-y: auto;">
                            <div>
                                <img height='320px' style="float:right;margin-left:16px;margin-bottom:16px;" contain src="/images/ressources/monty-hall/MontyHall.png"/>
                                <div class='text-justify text-subtitle-1 mt-2' v-html="history[0][locale]"/>
                                <div class='text-justify text-subtitle-1 mt-4' v-html="history[1][locale]"/>
                            </div>
                        </v-card>
                    </v-window-item>
                </v-window>
            </v-col>
            <v-col 
                cols="4"
                class='d-none d-md-flex justify-center align-center pimSubtitleFont'
                style='font-size:60px;height:580px;'
            > 
                {{ titles[onboarding][locale] }}
            </v-col>
        </v-row>

        <v-card-actions style='display:block'>
            <div style='display:flex;justify-content:space-around'>
                <v-btn text @click="prev">
                    <v-icon>mdi-chevron-left</v-icon>
                </v-btn>
                <v-item-group v-model="onboarding" class="text-center" mandatory>
                    <v-item v-for="n in length" :key="`btn-${n}`" v-slot="{ active, toggle }">
                        <v-btn :input-value="active" icon @click="toggle">
                            <v-icon 
                                :icon="n == onboarding+1 ? 'mdi-record-circle-outline' : 'mdi-record'"
                                :color="n == onboarding+1 ? 'black' : 'captionColor'"
                            />
                        </v-btn>
                    </v-item>
                </v-item-group>
                <v-btn text @click="next">
                    <v-icon>mdi-chevron-right</v-icon>
                </v-btn>
            </div>
            <div style='display:flex'>
                <v-spacer/>
                <v-btn color='primary' variant="flat" style='width:200px' @click="emit('close')">
                    OK !
                </v-btn>
            </div>
        </v-card-actions>
    </v-card>
</template>
<script setup>
    import { ref } from "vue";
    import { useI18n } from 'vue-i18n';

    const { locale } = useI18n();

    const emit = defineEmits(['close']);

    const length = 5
    const onboarding = ref(0);

    const next = () => onboarding.value = onboarding.value + 1 === length ? 0 : onboarding.value + 1;
    const prev = () => onboarding.value = onboarding.value - 1 < 0 ? length - 1 : onboarding.value - 1;

    const mainTitle = { en: 'The Monty Hall Problem', fr: 'Le problème de Monty Hall' };
    const text = [
        {
            en: 'You are presented with three doors',
            fr: 'Vous vous trouvez devant trois portes'
        },
        {
            en: 'Two of the doors hide a goat and one of the doors hides a car',
            fr: 'Il y a une chèvre derrière deux des portes et une voiture derrière la troisième'
        },
        {
            en: 'The goal is to pick the right door :',
            fr: 'Le but est de choisir la bonne porte :'
        },
        {
            en: 'If you pick the car, you win.',
            fr: 'Si vous trouvez la voiture, vous avez gagné'
        },
        {
            en: 'If you pick the goat, you lose.',
            fr: 'Si vous découvrez une chèvre, vous avez perdu'
        },
        {
            en: 'The game goes like this : You start by picking a door',
            fr: 'Pour commencer le jeu, vous choisissez une porte'
        },
        {
            en: 'Then the host opens another door, revealing a goat',
            fr: 'Ensuite, le présentateur ouvre une autre porte derrière laquelle se trouve une chève'
        },
        {
            en: 'You now have a choice :',
            fr: 'Vous avez ensuite le choix :'
        },
        {
            en: 'OR',
            fr: 'OU'
        },
        {
            en: 'your door',
            fr: 'votre porte'
        },
        {
            en: 'your door',
            fr: 'de porte'
        },
        {
            en: 'Which one of the following phrases is correct ?',
            fr: 'Laquelle des propositions suivantes est correcte ?'
        },
        {
            en: 'You have better chances of winning if you',
            fr: 'Vous avez plus de chances de gagner si vous décidez de'
        },
        {
            en: 'It does not matter, it\'s a 50-50 choice.',
            fr: 'Cela n\'a pas d\'importance, on a autant de chances de gagner dans les deux cas.'
        }
    ];
    const titles = [
        {
            en: 'The Setup',
            fr: 'La situation'
        },
        {
            en: 'The Goal',
            fr: 'L\'objectif'
        },
        {
            en: 'First Choice',
            fr: 'Choix initial'
        },
        {
            en: 'The Dilemma',
            fr: 'Le dilemme'
        },
        {
            en: 'The History',
            fr: 'L\'histoire'
        },
    ]
    const history = [
        {
            en: "<b>Monty Hall</b> (1921-2017) was a Canadian radio and television show host who moved to the United States in 1955 to pursue a career in broadcasting. After starting out as a radio newsreader and sportscaster, he transitioned to television, where he became a prominent figure in the game show genre. Hall is best known as the host and producer of Let's Make a Deal, which debuted in 1963 and quickly became a cultural phenomenon. His engaging personality and ability to connect with contestants made the show a favorite among viewers. Beyond his television success, Hall was also dedicated to philanthropy, supporting various charitable causes throughout his life and leaving a lasting impact on both the entertainment industry and the community.",
            fr: "<b>Monty Hall</b> (1921-2017) était un animateur de radio et de télévision canadien qui a déménagé aux États-Unis en 1955 pour poursuivre une carrière dans la diffusion. Après avoir commencé comme lecteur de nouvelles à la radio et commentateur sportif, il a fait la transition vers la télévision, où il est devenu une figure emblématique du genre des jeux télévisés. Hall est surtout connu comme l'animateur et producteur de Let's Make a Deal, qui a fait ses débuts en 1963 et est rapidement devenu un phénomène culturel. Sa personnalité engageante et sa capacité à se connecter avec les candidats ont fait de l'émission un favori parmi les téléspectateurs. En plus de son succès à la télévision, Hall était également dévoué à la philanthropie, soutenant diverses causes caritatives tout au long de sa vie et laissant un impact durable tant sur l'industrie du divertissement que sur la communauté."
        },
        {
            en: "This game was derived from <b>Monty Hall</b>'s show by an american statistician named <b>Steve Selvin</b> (who also gave it its name). It has become quite famous in the mathematics community for reasons you will no doubt understand soon (if you haven't already !)",
            fr: "Ce jeu a été dérivé de l'émission de <b>Monty Hall</b> par un statisticien américain nommé <b>Steve Selvin</b> (qui lui a également donné son nom). Il est devenu assez célèbre dans la communauté mathématique pour des raisons que vous comprendrez sans aucun doute bientôt (si ce n'est pas déjà le cas !)"
        },
    ]
</script>