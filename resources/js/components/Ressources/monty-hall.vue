<template>
    <v-row class="text-center">
      <v-col cols="12" md="8">
        <v-container>
          <v-row class="text-center">
            <v-col cols="12" class='text-h6'>
              {{ $t(instruction )}}
            </v-col>
            <v-col cols="12">
              <v-window v-model='controls' reverse>
                <v-window-item v-if="isWindowSm">
                    <v-btn 
                        v-for="i in 3"
                        :color="btnColors[i-1]" 
                        class='mx-3' 
                        :disabled='globalState != "start" && !doors[i-1].selected'
                        @click='pickADoor(i-1)'
                        :icon="`mdi-numeric-${i}-circle`"
                    />
                </v-window-item>
                <v-window-item v-else>
                    <v-btn 
                        v-for="i in 3"
                        :color="btnColors[i-1]" 
                        width='150px' 
                        class='mx-3' 
                        :disabled='globalState != "start" && !doors[i-1].selected'
                        @click='pickADoor(i-1)'
                        :text="`${$t('Door')} ${i}`"
                    />
                </v-window-item>
                <v-window-item>
                  <v-btn width='200px' class='mx-3' :disabled='globalState != "confirm"' @click='confirmDoor(true)'>Keep</v-btn>
                  <v-btn width='200px' dark class='mx-3' :disabled='globalState != "confirm"' @click='confirmDoor(false)'>Change</v-btn>
                </v-window-item>
              </v-window>
            </v-col>
            <v-col cols="4" v-for="door in doors">
              <monty-hall-door :door="door"/>
            </v-col>
          </v-row>
          <v-row>
              <v-col cols="6">
            <v-btn rounded outlined block class='mx-4' @click="toggleDoors">
                {{ openBtnDisplay }}
              </v-btn>
          </v-col>
          <v-col cols="6" class='text-center'>
            <v-dialog v-model="autoDialog" width="500">
                <template v-slot:activator="{ props: activatorProps }">
                <v-btn rounded outlined v-bind="activatorProps" block class='mx-4'>
                    {{ $t('Autoplay') }}
                </v-btn>
                </template>
                <v-card>
                    <v-card-title class="headline">
                        {{ $t('Launch') }} !
                    </v-card-title>
                    <v-card-text>
                        {{ $t('Play the game automatically multiple times') }}
                        <v-text-field class='mt-3' label="Repetitions" v-model="autoPlayTimes" type='number' outlined min='1' max='400'></v-text-field>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="error" width="150px" class='mx-1' dark @click="autoDialog = false">
                            {{ $t('Cancel') }}
                        </v-btn>
                        <v-btn color="success" variant="flat" width="150px" class='mx-1' dark @click="launchAutoPlay">
                            GO !
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
            </v-col>
          </v-row>
        </v-container>
    </v-col>
    <v-col cols="12" md="4">
        <v-container>
            <monty-hall-stats :history="history" @reset="resetStats"/>
        </v-container>
    </v-col>
    </v-row>
    <v-dialog v-model='winDialog' overlay-color="white" content-class="elevation-0">
        <v-img src="/images/ressources/monty-hall/youwin.png" width='350px' style='margin: auto'></v-img>
        <v-img src="/images/ressources/monty-hall/door-car.png" width='250px' style='margin: auto'></v-img>
      </v-dialog>
      <v-dialog v-model='loseDialog' content-class="elevation-0">
        <v-img src="/images/ressources/monty-hall/youlose.png" width='350px' style='margin: auto'></v-img>
        <v-img src="/images/ressources/monty-hall/door-goat.png" width='250px' style='margin: auto'></v-img>
      </v-dialog>
    <!-- <monty-hall-tutorial :showTutorial='showTutorial'/> -->
</template>
<script setup>
    import { ref, computed, onMounted } from 'vue';
    import { useDisplay } from 'vuetify';

    const globalState = ref('start');
    const controls = ref(0);
    const instruction = ref('Pick a door');
    const doors = ref([]);
    const doorNames = ref([]);
    const btnColors = ref(['primary', 'primary', 'primary']);
    const history = ref({ changeWin: 0, changeLose: 0, keepWin: 0, keepLose: 0 });
    const winDialog = ref(false);
    const loseDialog = ref(false);
    const autoDialog = ref(false);
    const forceUpdate = ref(0);
    const onAutoPlay = ref(false);
    const autoPlayTimes = ref(100);
    const openBtnDisplay = ref('open doors');
    const tutorialFlag = ref(true);
    const pickedDoor = ref(0);

    const { name } = useDisplay();
    const isWindowSm = computed(() => ['xs', 'sm'].includes(name.value));

    onMounted(() => {
        reset();
    });

    const reset = () => {
        globalState.value = 'start';
        controls.value = 0;
        btnColors.value = ['primary', 'primary', 'primary'];
        pickedDoor.value = 0;
        instruction.value = 'Pick a door';
        const winningDoor = Math.floor(Math.random() * 3);
        doors.value = Array.from({ length: 3 }, (_, i) => ({
            open: false,
            win: i === winningDoor,
            selected: false,
        }));
    };

    const pickADoor = (nb) => {
    if (globalState.value === 'start') {
        globalState.value = '';
        pickedDoor.value = nb;
        doors.value[nb].selected = true;
        btnColors.value[nb] = 'success';

        setTimeout(() => {
        let revealIndex = Math.floor(Math.random() * 3);
        while (doors.value[revealIndex].win || doors.value[revealIndex].selected) {
            revealIndex = (revealIndex + 1) % 3;
        }
        doors.value[revealIndex].open = true;
        }, 500);

        const waitingTime = onAutoPlay.value ? 0 : 500;
        setTimeout(() => {
        globalState.value = 'confirm';
        controls.value = 1;
        instruction.value = 'Keep the same door ?';
        }, waitingTime);
    }
    };

    const confirmDoor = (val) => {
    if (globalState.value === 'confirm') {
        globalState.value = '';
        let win = false;

        if (!val) {
        doors.value.forEach(door => {
            door.selected = door.open ? false : !door.selected;
        });
        }

        doors.value.forEach(door => {
        if (door.selected && !door.open) {
            door.open = true;
            if (door.win) {
            win = true;
            }
        }
        });

        const divider = onAutoPlay.value ? 50 : 1;
        if (win) {
        if (!onAutoPlay.value) {
            setTimeout(() => (winDialog.value = true), 500);
            setTimeout(() => (winDialog.value = false), 1500);
        }
        if (val) {
            history.value.keepWin++;
        } else {
            history.value.changeWin++;
        }
        } else {
        if (!onAutoPlay.value) {
            setTimeout(() => (loseDialog.value = true), 500);
            setTimeout(() => (loseDialog.value = false), 1500);
        }
        if (val) {
            history.value.keepLose++;
        } else {
            history.value.changeLose++;
        }
        }
        forceUpdate.value++;
        setTimeout(reset, 1500 / divider);
    }
    };

    const autoPlay = (times) => {
    if (times > 0) {
        pickADoor(Math.floor(Math.random() * 3));
        confirmDoor(Math.floor(Math.random() * 2) === 0);
        setTimeout(() => autoPlay(--times), 100);
    } else {
        onAutoPlay.value = false;
        setTimeout(reset, 500);
    }
    };

    const launchAutoPlay = () => {
        autoDialog.value = false;
        reset();
        history.value = { changeWin: 0, changeLose: 0, keepWin: 0, keepLose: 0 };
        onAutoPlay.value = true;
        autoPlay(Math.min(autoPlayTimes.value, 400));
    };

    const resetStats = () => {
        history.value = { changeWin: 0, changeLose: 0, keepWin: 0, keepLose: 0 };
    };

    const toggleDoors = () => {
    if (openBtnDisplay.value === 'open doors') {
        doors.value.forEach(door => (door.open = true));
        openBtnDisplay.value = 'reset doors';
    } else {
        reset();
        openBtnDisplay.value = 'open doors';
    }
    };
</script>