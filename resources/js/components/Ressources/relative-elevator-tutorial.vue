<template>
    <v-card>
        <v-card-title class='pimTitleFont' style="font-size:56px;">
            {{ mainTitle[locale] }}
        </v-card-title>
        <v-card-text>
            <v-row>
                <v-col cols='12' md="8">
                    <v-window v-model="onboarding">
                        <v-window-item :key="0">
                            <v-card class="d-flex">
                                <div style="flex:1">
                                    <div v-for="text in partOne" class="d-flex">
                                        <div class="d-flex align-center">
                                            <v-icon 
                                                size="x-large" 
                                                :icon="`mdi-arrow-${text.token == 'white' ? 'up' : 'down'}-bold-outline`" 
                                                v-if="text.token"
                                            />
                                            <v-avatar :color="text.token" class='ma-4 elevation-4' v-if="text.token">
                                                <v-icon :icon="text.token == 'white' ? 'mdi-plus' : 'mdi-minus'"/>
                                            </v-avatar>
                                            <div v-html="text[locale]"/>
                                        </div>
                                        <div class="d-flex justify-center" style="max-width: 50%;">
                                            <v-img 
                                                class="ml-3"
                                                width="350px"
                                                :src="theme.name.value == 'customDark' ? '/images/ressources/relative-elevator/panels dark.png' : '/images/ressources/relative-elevator/panels light.png'"
                                                v-if="text.image"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div style="position:relative;" class="mx-6">
                                    <img 
                                        style="margin-top:-12px;"
                                        width='120px' 
                                        :src="theme.name.value == 'customDark' ? '/images/ressources/relative-elevator/axe dark.png' : '/images/ressources/relative-elevator/axe.png'"
                                    />
                                    <img 
                                        id='lift' 
                                        class='smoothMove'
                                        width='53px'
                                        src="/images/ressources/relative-elevator/lift.png"
                                        style='position:absolute;top:112px;left:15px'
                                    />
                                </div>
                            </v-card>
                        </v-window-item>
                        <v-window-item :key="1">
                            <v-card>
                                <div v-for="text in partTwo" v-html="text[locale]" class="mb-2"/>
                                <div v-for="(item, index) in partTwoList" class="ml-8 mb-4">
                                    <div class="d-flex align-center">
                                        <v-icon :icon="`mdi-numeric-${index+1}-box`" class="mr-1"/>
                                        <span class="text-h5" v-html="item.title[locale]"/>
                                    </div>
                                    <div v-html="item.text[locale]"/>
                                </div>
                            </v-card>
                        </v-window-item>
                    </v-window>
                </v-col>
                <v-col 
                    cols="4"
                    class='d-none d-md-flex justify-center align-center pimSubtitleFont'
                    style='font-size:60px;height:450px;'
                > 
                    {{ titles[onboarding][locale] }}
                </v-col>
            </v-row>
        </v-card-text>
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
    import { useTheme } from 'vuetify';

    const { locale } = useI18n();
    const theme = useTheme();

    const emit = defineEmits(['close']);

    const length = 2;
    const onboarding = ref(0);

    const next = () => onboarding.value = onboarding.value + 1 === length ? 0 : onboarding.value + 1;
    const prev = () => onboarding.value = onboarding.value - 1 < 0 ? length - 1 : onboarding.value - 1;

    const mainTitle = { en: 'Relative Elevator', fr: 'Ascenceur relatif' };
    const titles = [
        { en: 'A very special elevator', fr: 'Un ascenceur très spécial'},
        { en: 'Challenges !', fr: 'Les défis !'},
    ]
    const partOne = [
        { 
            en: 'We are in a building with lots of floors both above and below ground. In this building, the elevator works with tokens.',
            fr: 'Nous sommes dans un bâtiment avec de nombreux étages, à la fois au-dessus et en dessous du sol. Dans ce bâtiment, l\'ascenseur fonctionne avec des jetons.'
        },
        { 
            en: 'A <b>white</b> token makes the elevator go <b>up</b> one floor', 
            fr: 'Un jeton <b>blanc</> fait <b>monter</b> l\'ascenceur d\'un étage', 
            token: 'white'
        },
        { 
            en: 'A <b>black</b> token makes the elevator go <b>down</b> one floor',
            fr: 'Un jeton <b>noir</> fait <b>descendre</b> l\'ascenceur d\'un étage', 
            token: 'black'
        },
        { 
            en: '<p>Tokens go into panels. There are two panels and the machine adds up the tokens from both panels before ordering the elevator to move.</p><p class=mt-3>You can change the operation from addition to subtraction by clicking on it.</p>',
            fr: '<p>Les jetons vont dans des panneaux. Il y a deux panneaux et la machine additionne les jetons des deux panneaux avant de commander le mouvement de l\'ascenseur.</p><p>Vous pouvez changer l\'opération de l\'addition à la soustraction en cliquant dessus.</p',
            image: true
        }
    ];
    const partTwo = [
        { 
            en: 'This app is designed to help students understand the concept of adding and subtracting relative numbers.',
            fr: 'Cette application est conçue pour aider les étudiants à comprendre le concept de l\'addition et de la soustraction de nombres relatifs.'
        },
        { 
            en: 'To put this understanding to the test, they can try one of four challenges available :',
            fr: 'Pour mettre cette compréhension à l\'épreuve, ils peuvent essayer l\'un des quatre défis disponibles :'
        },
    ];
    const partTwoList = [
        {
            title: {
                en: 'Challenge 1 - Addition',
                fr: 'Défi 1 - Addition'
            },
            text: {
                en: 'Both panels are locked and filled with a random number of tokens of either color. The operation is locked on addition. The student has to find which floor the elevator will end up on.',
                fr: 'Les deux panneaux sont verrouillés et remplis d\'un nombre aléatoire de jetons de chaque couleur. L\'opération est verrouillée sur l\'addition. L\'élève doit trouver à quel étage l\'ascenseur finira.'
            }
        },
        {
            title: {
                en: 'Challenge 2 - Incomplete Addition',
                fr: 'Défi 2 - Soustraction'
            },
            text: {
                en: 'The first panel is locked and filled with a random number of tokens of either color. The operation is locked on addition. The student has to fill the second panel to make the elevator go to a randomly picked floor.',
                fr: 'Le premier panneau est verrouillé et rempli d\'un nombre aléatoire de jetons de chaque couleur. L\'opération est verrouillée sur l\'addition. L\'élève doit remplir le deuxième panneau pour faire descendre l\'ascenseur à un étage choisi au hasard.'
            }
        },
        {
            title: {
                en: 'Challenge 3 - Subtraction',
                fr: 'Défi 3 - Soustraction à trou'
            },
            text: {
                en: 'Both panels are locked and filled with a random number of tokens of either color. The operation is locked on subtraction. The student has to find which floor the elevator will end up on.',
                fr: 'Les deux panneaux sont verrouillés et remplis d\'un nombre aléatoire de jetons de chaque couleur. L\'opération est verrouillée sur la soustraction. L\'élève doit déterminer à quel étage l\'ascenseur finira.'
            }
        },
        {
            title: {
                en: 'Challenge 4 - Incomplete Subtraction',
                fr: 'Défi 4 - Soustraction à trou'
            },
            text: {
                en: 'The first panel is locked and filled with a random number of tokens of either color. The operation is locked on subtraction. The student has to fill the second panel to make the elevator go to a randomly picked floor.',
                fr: 'Le premier panneau est verrouillé et rempli d\'un nombre aléatoire de jetons de chaque couleur. L\'opération est verrouillée sur la soustraction. L\'élève doit remplir le deuxième panneau pour faire descendre l\'ascenseur à un étage choisi au hasard.'
            }
        },
    ]
</script>