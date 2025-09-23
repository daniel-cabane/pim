<template>
     <v-container style='max-width:960px;margin:auto'>
    <v-row class="text-center mt-3">
      <v-col cols="6" md="3" style='display:flex;justify-content:center;align-items:center'>
        <v-select 
            variant="outlined"
            :disabled='started'
            hide-details
            :items="[1,2,3,4]"
            v-model='nbOfDice'
            :label="$t('Nb of dice')"
        />
      </v-col>
      <v-col cols="6" md="3" style='display:flex;justify-content:center;align-items:center'>
        <v-select
            variant="outlined"
            :disabled='started'
            hide-details
            :items="biasList"
            v-model='bias'
            :label="$t('Maximum Bias')"
        />
      </v-col>
      <v-col cols="6" md="3" style='display:flex;justify-content:center;align-items:center'>
        <v-select
            variant="outlined"
            hide-details
            :items="trackList"
            v-model='tracking'
            :label="$t('Keep track of')"
        />
      </v-col>
      <v-col cols="6" md="3" style='display:flex;justify-content:center;align-items:center'>
        <v-btn 
            size="x-large"
            color='error'
            style='width:100%'
            v-if='started'
            variant="outlined"
            :text="$t('Restart')"
            @click='init'
        />
        <v-btn
            :disabled='nbOfDice == null || bias == null'
            size="x-large"
            color='primary'
            style='width:80%'
            @click='setUpDice'
            :text="$t('Set up')"
            v-else
        />
      </v-col>
      <v-col cols="12" md="9">
        <div class='mt-2 text-h4'>
          {{ $t('Nb of rolls') }} : <b>{{ tries }}</b>
        </div>
        <div class='d-flex flex-wrap justify-space-around mb-10'>
          <div class='ma-1' v-for='(die, index) in dice' :key='index'>
            <biased-dice-single-dice :result='die.result' :rolling='rolling'/>
            <div v-if='tracking == "avg"'>
                <div class='text-h6'>
                  {{ $t('Average') }} :<br/><span class='text-h4'>
                    <b>{{ die.avg.toFixed(2) }}</b>
                  </span>
                </div>
            </div>
            <div v-else>
              <div class='text-captionColor'>
                {{ $t('Nb of success') }} : {{ die.success }}
              </div>
              <div class='text-h6'>
                {{ $t('Success ratio ') }}:<br/><span class='text-h4'><b>{{ die.ratio }}%</b></span>
              </div>
            </div>
            <div style='position:relative' v-if='bias!=0'>
                <v-select 
                    hide-details solo dense
                    v-model='die.presumed'
                    :disabled='revealed'
                    :color='presumeColor(die.presumed)' 
                    :base-color='presumeColor(die.presumed)'
                    :bg-color='presumeColor(die.presumed)'
                    :dark='presumeColor(die.presumed)!="white"'
                    :items='["Balanced", "Biased"]' 
                    style='width:150px'
                    class='mt-3'
                    variant="outlined"
                />
                <div style='position:absolute;top:-15px;right:-5px;' v-if='revealed'>
                    <v-icon 
                    icon="mdi-check-circle"
                    color="success"
                    size="72"
                    v-if='die.presumed == "Balanced" && die.bias == 0 || die.presumed == "Biased" && die.bias != 0'
                />
                <v-icon 
                    icon="mdi-close-circle"
                    color="error"
                    size="72"
                    v-else
                />
                </div>
            </div>
            <v-card large color='black' style='width:150px;height:50px' class='mt-3 d-flex justify-center align-center text-h5' dark v-if='bias == 0'>
              {{ $t('Balanced') }}
            </v-card>
            <div v-if='revealed'>
              <v-card large :color='colors.balanced' style='width:150px;height:50px' class='mt-3 d-flex justify-center align-center text-h5' dark v-if='die.bias == 0'>
                {{ $t('Balanced') }}
              </v-card>
              <v-card large :color='colors.biased' style='width:150px;height:50px' class='mt-3 d-flex justify-center align-center text-h5' dark v-else>
                {{ $t('Biased') }}
              </v-card>
            </div>
          </div>
        </div>
        <div v-if='dice.length && bias!=0'>
          <div class='text-center text-h4' v-if='revealed' v-html="gameRecap"/>
          <v-btn color='success' x-large style='width:100%' @click='revealBias' variant="outlined" v-else>Reveal</v-btn>
        </div>
      </v-col>
      <v-col cols="12" md="3" v-if='started'>
        <div style="height:75px;" v-if="!isWindowSm"/>
        <v-select 
            hide-details
            class='mb-4'
            :items="[1,20,50,100,1000]"
            v-model='nbOfRolls'
            :label="$t('Nb of rolls')"
            variant="outlined"
        />
        <div v-if='rolling' style='display:flex;height:44px;'>
            <v-card flat tile color='primary' style='height:44px;' :style='`flex:${rollRatio}`'/>
            <v-card flat tile color='primary' style='opacity:0.3;height:44px' :style='`flex:${100-rollRatio}`'/>
        </div>
        <v-btn
            :disabled='rolling'
            color='primary'
            size="large"
            style='width:200px'
            :text="$t('roll')"
            @click='rollDice'
            v-else
        />
        <div style="height:75px;" v-if="!isWindowSm"/>
        <div style="height:20px;" v-else/>
        <div class="d-flex flex-wrap justify-center ga-4">
            <biased-dice-help-dialog :dice="[]"/>
            <biased-dice-history-dialog :history='history'/>
        </div>
      </v-col>
    </v-row>
  </v-container>
</template>
<script setup>
    import { ref, computed } from 'vue';
    import { useDisplay } from 'vuetify';
    import { useI18n } from 'vue-i18n';

    const { t, locale } = useI18n();

    const { name } = useDisplay();
    const isWindowSm = computed(() => ['xs', 'sm'].includes(name.value));

    const dice = ref([]);
    const nbOfDice = ref(null);
    const biasList = computed(() => [
        { title: t('None'), value: 0 },
        { title: `${t('Negligible')} (+/-1%)`, value: 0.01 },
        { title: `${t('Subtle')} (+/-2%)`, value: 0.02 },
        { title: `${t('Visible')} (+/-5%)`, value: 0.05 },
        { title: `${t('Obvious')} (+/-10%)`, value: 0.1 }
    ]);
    const bias = ref(null);
    const trackList = computed(() => [
        { title: t('Number of 6s'), value: 'six' },
        { title: t('Average'), value: 'avg' }
    ]);
    const tracking = ref(null);
    const started = ref(false);
    const rolling = ref(false);
    const tries = ref(0);
    const nbOfRolls = ref(1);
    const rollsToDo = ref(0);
    const roll = ref(null);
    const revealed = ref(false);
    // const gameRecap = ref('');
    const history = ref([]);
    const colors = { balanced: '#457B7C', biased: '#D63939' };

    const rollRatio = computed(() => 100 * rollsToDo.value / nbOfRolls.value);

    const setUpDice = () => {
        dice.value = [];
        for (let i = 0; i < nbOfDice.value; i++) {
            dice.value.push({
                result: 1,
                bias: Math.random() < 0.5 ? Math.sign(Math.random() - 0.5) * bias.value : 0,
                success: 0,
                ratio: 0,
                avg: 0
            });
        }
        started.value = true;
    };

    const init = () => {
        nbOfDice.value = 1;
        dice.value = [];
        bias.value = 0;
        tracking.value = null;
        tries.value = 0;
        started.value = false;
        clearInterval(roll.value);
        rolling.value = false;
        nbOfRolls.value = 1;
        revealed.value = false;
    };

    const rollOnce = () => {
    tries.value++;
    dice.value.forEach(die => {
        let result = Math.random() < 0.16667 + die.bias ? 6 : Math.round(Math.random() * 4 + 1);
        die.result = result;
        if (result === 6) {
        die.success += 1;
        }
        die.ratio = (100 * die.success / tries.value).toFixed(1);
        die.avg = (die.avg * (tries.value - 1) + result) / tries.value;
    });
    };

    const rollDice = () => {
        if (nbOfRolls.value === 1) return rollOnce();
        rolling.value = true;
        const delay = Math.max(10, 100 - nbOfRolls.value);
        rollsToDo.value = nbOfRolls.value;
        roll.value = setInterval(() => {
            rollOnce();
            rollsToDo.value--;
            if (rollsToDo.value <= 0) {
            clearInterval(roll.value);
            rolling.value = false;
            }
        }, delay);
    };

    const presumeColor = presumed => presumed ? presumed == 'Biased' ? colors.biased : colors.balanced : 'surface';

    const revealBias = () => {
      const nbCorrectGuesses = dice.value.reduce((acc, die) => 
          (die.bias === 0 && die.presumed === "Balanced") || (die.bias !== 0 && die.presumed === "Biased") ? ++acc : acc, 0
      );

      const biasItem = biasList.value.find(b => b.value === bias.value);
      history.value.push({
          correct: nbCorrectGuesses,
          outOf: nbOfDice.value,
          after: tries.value,
          bias: biasItem.title
      });
      revealed.value = true;
    };

    const gameRecap = computed(() => {
      if(revealed.value){
        const nbCorrectGuesses = dice.value.reduce((acc, die) => 
            (die.bias === 0 && die.presumed === "Balanced") || (die.bias !== 0 && die.presumed === "Biased") ? ++acc : acc, 0
        );
        if(locale.value == 'fr'){
          return  `${nbCorrectGuesses} bonne${nbCorrectGuesses > 1 ? 's' : ''} réponse${nbCorrectGuesses > 1 ? 's' : ''} sur ${nbOfDice.value} apres <b>${tries.value}</b> lancer${tries.value > 1 ? 's' : ''}`;
        }

        return `${nbCorrectGuesses} out of ${nbOfDice.value} correct guess${nbCorrectGuesses > 1 ? 'es' : ''} after <b>${tries.value}</b> roll${tries.value > 1 ? 's' : ''}`;
      }

      return '';
    })
</script>