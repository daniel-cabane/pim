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
                            <v-card>
                                <div v-for="text in partOne" class="mb-3">
                                    <span :class="text.class" v-html="text[locale]"/>
                                    <v-btn small class="ml-2" style='width:100px' color='primary' :text="$t(text.button)" v-if="text.button"/>
                                </div>
                            </v-card>
                        </v-window-item>
                        <v-window-item :key="1">
                            <v-card>
                                <v-card-text class="text-h6 text-captionColor d-flex justify-center py-6">
                                    {{ $t('Coming soon') }}...
                                </v-card-text>
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

    const { locale } = useI18n();

    const emit = defineEmits(['close']);

    const titles = [
        {
            en: 'Roll the dice', fr: 'Les dés sont jetés'
        },
        {
            en: 'Help !', fr: 'A l\'aide !'
        }
    ];
    const length = titles.length
    const onboarding = ref(0);

    const next = () => onboarding.value = onboarding.value + 1 === length ? 0 : onboarding.value + 1;
    const prev = () => onboarding.value = onboarding.value - 1 < 0 ? length - 1 : onboarding.value - 1;

    const mainTitle = { en: 'The biased dice', fr: 'Les dés truqués' };

    const partOne = [
        {
            en: 'This app allows you to simulate large number of dice throws with biased or balanced dice. This allows students to experience the <i>law of large numbers</i> and further study the theorems allowing to control the likelihood of a dice being balanced based on frequency analysis.',
            fr: "Cette application vous permet de simuler un grand nombre de lancers de dés avec des dés biaisés ou équilibrés. Cela permet aux étudiants de découvrir la <i>loi des grands nombres</i> et d'étudier davantage les théorèmes permettant de contrôler la probabilité qu'un dé soit équilibré en se basant sur une analyse de fréquence"
        },
        {
            en: 'First, choose the number of dice and the bias level. To set up the dice, click on',
            fr: "D'abord, choisissez le nombre de dés et le niveau de biais, puis cliquez sur",
            button: 'Set up'
        },
        {
            en: 'Then select the number of times you want the dice to roll and click on',
            fr: "Ensuite, sélectionnez le nombre de lancers et cliquez sur",
            button: 'roll'
        },
        {
            en: 'The app will calculate for you the average result or the frequency of success (getting a 6) for each dice.',
            fr: "L'application calculera pour vous la moyenne des résultats ou la fréquence de succès (obtenir un 6) pour chaque dé.",
        },
        {
            en : 'Some dice will be biased (probability of success will be higher or lower) and others won\'t. You have to decide which is which',
            fr: "Certains dés seront biaisés (la probabilité de succès sera plus élevée ou plus basse) et d'autres ne le seront pas. Vous devez décider lesquels sont biaisés et lesquels ne le sont pas."
        },
        {
            en: 'The probability of success with a balanced die is 1÷6 &nbsp; &#8776; &nbsp;<span class="text-h5 font-weight-bold">16.7%</span>',
            fr: 'La probabilité de succès avec un dé équilibré est égale à 1÷6 &nbsp; &#8776; &nbsp;<span class="text-h5 font-weight-bold">16.7%</span>',
            class: 'text-center text-h6'
        },
        {
            en: 'The average result with a balanced die is <span class="text-h5 font-weight-bold">3.5</span>',
            fr: 'Le résultat moyen d\'un dé équilibré est <span class="text-h5 font-weight-bold">3.5</span>',
            class: 'text-center text-h6'
        },
    ]
</script>