<template>
    <v-container @keydown.enter='submitAnswer'>
        <v-alert class="mb-8" type="error" v-if='["xs", "sm"].includes(name)'>
            Votre écran n'est pas assez large pour afficher cette application correctement
        </v-alert>
        <v-snackbar v-model="snackbar" top :color='snackColor'>
            {{ snackText }}
            <template v-slot:action="{ attrs }">
            <v-btn :color="snackColor" text v-bind="attrs" @click="snackbar = false">
                Close
            </v-btn>
            </template>
        </v-snackbar>
        <v-row>
            <v-col cols='9'>
                <v-row class="mt-8">
                    <v-col cols='5' style='display:flex;justify-content:center;align-items:center;'>
                    <relative-elevator-token-panel 
                        :index='0'
                        :tokenList="tokens[0]"
                        :locked="PanelLocked[0]"
                        @addWhite="addWhite"
                        @addBlack="addBlack"
                        @clear="clearPanel"
                    />
                    </v-col>
                    <v-col cols='2' style='display:flex;justify-content:center;align-items:center;'>
                    <v-hover v-slot:default="{ hover }">
                        <v-card
                        class='text-h1 font-weight-bold'
                        :elevation="hover && !challengeActive ? 15 : 0"
                        :color="hover ? 'surface' : 'background'"
                        height="80" width="80"
                        style='display:flex;justify-content:center;align-items:center;cursor:pointer'
                        @click='switchOperation'
                    >
                        <span v-if='operation'>+</span>
                        <span style="margin-top:-18px;" v-else>–</span>
                        </v-card>
                    </v-hover>
                    </v-col>
                    <v-col cols='5' style='display:flex;justify-content:center;align-items:center;'>
                    <relative-elevator-token-panel 
                        :index='1'
                        :tokenList="tokens[1]"
                        :locked="PanelLocked[1]"
                        @addWhite="addWhite"
                        @addBlack="addBlack"
                        @clear="clearPanel"
                    />
                    </v-col>
                </v-row>
                <v-btn block color='primary' x-large class='mt-4' @click='moveLift' :disabled='challengeActive'>
                    Effectuer
                </v-btn>
                </v-col>
                <v-col cols='1'></v-col>
                <v-col cols='2' style='position:relative' class='py-0'>
                    <v-card x-large color="primary" outlined class='text-h3 font-weight-bold text-xs-center white--text' style='z-index:10' min-width='120px' max-width='120px'>
                    <div style='width:100%;display:flex;justify-content:center'>
                        {{ formatNb(result) }}
                    </div>
                    </v-card>
                <v-img 
                    min-width='170px' 
                    max-width='170px' 
                    :src="theme.name.value == 'customDark' ? '/images/ressources/relative-elevator/axe dark.png' : '/images/ressources/relative-elevator/axe.png'"
                />
                <v-img 
                    id='lift' 
                    class='smoothMove'
                    min-width='75px'
                    max-width='75px'
                    src="/images/ressources/relative-elevator/lift.png"
                    style='position:absolute;top:228px;left:34px'
                />
            </v-col>
        </v-row>
        <v-row class='mt-4'>
            <v-select v-model='challengeNb' :items="challenges" class='mx-2' label="Sélectionner un défi" :disabled='challengeActive' variant='outlined'></v-select>
            <div style='display:flex' v-if='challengeActive'>
            <v-text-field type='number' v-model='challengeResult' label='Résultat' autofocus outlined v-if='challengeNb%2 == 1'/>
            <v-btn class="mx-2" fab dark color="success" @click='submitAnswer'>
                <v-icon dark>done</v-icon>
            </v-btn>
            <v-btn class="mx-2" fab dark color="error" @click='endChallenge'>
                <v-icon dark>clear</v-icon>
            </v-btn>
            </div>
            <v-btn style='width:215px' size='x-large' class='mx-2' color='primary' @click='startChallenge' :disabled="!challengeNb" v-else>
            Lancer le défi
            </v-btn>
        </v-row>
        <v-row class='headline mb-4'>
            <v-spacer></v-spacer>
            <span v-html='challengeText'></span>
            <v-spacer></v-spacer>
        </v-row>
    </v-container>
</template>
<script setup>
    import { ref, onMounted } from 'vue';
    import { useDisplay, useTheme } from 'vuetify';
    
    const theme = useTheme();
    console.log(theme.name.value)
    const { name } = useDisplay();

    const operation = ref(true);
    const result = ref(0);
    const challenges = ref([
        { title: 'Défi 1 - Addition', value: 1 },
        { title: 'Défi 2 - Addition incomplète', value: 2 },
        { title: 'Défi 3 - Soustraction', value: 3 },
        { title: 'Défi 4 - Soustraction incomplète', value: 4 },
    ]);
    const challengeNb = ref(null);
    const challengeResult = ref('');
    const challengeActive = ref(false);
    const challengeText = ref('');
    const expectedResult = ref(0);
    const snackbar = ref(false);
    const snackColor = ref('success');
    const snackText = ref('Exact !');

    const tokens = ref([[], []]);
    const PanelLocked = ref([false, false]);
    const addWhite = index => tokens.value[index].push(true);
    const addBlack = index => tokens.value[index].push(false);
    const clearPanel = index => tokens.value[index] = [];

    const formatNb = (nb) => {
        if (nb === 0) return 0;
        if (nb > 0) return `+${nb}`;
        return `–${-nb}`;
    };

    const moveLift = () => {
        const res0 = tokens.value[0].reduce((accum, num) => accum + (num ? 1 : -1), 0);
        const res1 = tokens.value[1].reduce((accum, num) => accum + (num ? 1 : -1), 0);
        const calcResult = operation.value ? res0 + res1 : res0 - res1;
        document.getElementById("lift").style.transform = `translateY(${-calcResult * 12 + 1 + Math.sign(calcResult)}px)`;
        if (result.value !== calcResult) {
            const count = setInterval(() => {
            result.value += Math.sign(calcResult - result.value);
            if (result.value === calcResult) clearInterval(count);
            }, 500 / Math.abs(calcResult - result.value));
        }
    };

    const switchOperation = () => {
    if (!challengeActive.value) {
        operation.value = !operation.value;
    }
    };

    const startChallenge = () => {
    if (challengeNb.value > 0) {
        document.getElementById("lift").style.transform = 'translateY(0px)';
        if (result.value !== 0) {
        const count = setInterval(() => {
            result.value += Math.sign(-result.value);
            if (result.value === 0) clearInterval(count);
        }, 500 / Math.abs(-result.value));
        }
        challengeResult.value = '';
        // eventBus.$emit('startChallenge', challengeNb.value);
        operation.value = challengeNb.value <= 2;
        challengeActive.value = true;

        switch (challengeNb.value % 2) {
        case 0:
            expectedResult.value = Math.sign(Math.random() - 0.5) * Math.floor(Math.random() * 15);
            challengeText.value = `Complète l'opération pour arriver à l'étage <span class='display-1 font-weight-bold'>${expectedResult.value}</span>`;
            break;
        case 1:
            challengeText.value = "Effectue l'opération";
            break;
        }
    }
    };

    const submitAnswer = () => {
    if (challengeActive.value) {
        const resultA = operation.value ? parseInt(terms.value[0]) + parseInt(terms.value[1]) : parseInt(terms.value[0]) - parseInt(terms.value[1]);
        const resultB = challengeNb.value % 2 === 0 ? expectedResult.value : challengeResult.value;

        if (resultA === resultB) {
        snackColor.value = 'success';
        snackText.value = 'Exact !';
        snackbar.value = true;
        endChallenge();
        } else {
        challengeResult.value = '';
        snackColor.value = 'error';
        snackText.value = 'Non, essaie encore';
        snackbar.value = true;
        }
    }
    };

    const endChallenge = () => {
        moveLift();
        challengeActive.value = false;
        challengeText.value = '';
        // eventBus.$emit('endChallenge', {});
    };
</script>
<style scoped>
  .smoothMove {
    transition: all .5s
  }
</style>