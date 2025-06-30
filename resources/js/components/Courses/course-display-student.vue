<template>
    <v-container>
        <v-row>
            <v-col cols="12" class="text-h3 d-flex justify-space-between">
                <span>
                    {{ pickLg(course.title)}}
                </span>
                <span class="text-captionColor">
                    <div class="d-flex justify-center">
                        {{ globalScores.total }}pts
                    </div>
                    <div class="text-h6 d-flex justify-center">
                        {{ levelName }}
                    </div>
                </span>
            </v-col>
            <v-col cols="12" class="text-captionColor">
                {{ pickLg(course.description)}}
            </v-col>
        </v-row>
        <v-row>
            <v-tabs v-model="tab" color="primary" direction="vertical">
                <v-tab 
                    v-for="(section, index) in course.sections"
                    class="font-weight-bold"
                    prepend-icon="mdi-star-four-points-box" 
                    :text="`${pickLg(section.title)} (${globalScores.section[index].userScore}/${globalScores.section[index].points})`"
                    :value="index"
                />
                <v-tab class="font-weight-bold" prepend-icon="mdi-plus-circle"  :text="`Bonus (??)`" value="bonus"/>
            </v-tabs>
            <v-tabs-window v-model="tab" style="flex:1" direction="vertical">
                <v-tabs-window-item v-for="(section, index) in course.sections" class="pa-2" :value="index">
                    <div class="text-h4 font-weight-bold">
                        {{ pickLg(section.title) }}
                    </div>
                    <div class="text-subtitle-1 text-captionColor mb-4">
                        {{ pickLg(section.description)}}
                    </div>
                    <div v-for="objective in section.objectives" class="my-2">
                    <div class="d-flex align-center">
                        <span class="text-h6">
                            {{ pickLg(objective.title) }}
                        </span>
                        <span class="dots"/>
                        <span class="text-h6">
                            {{ objective.userScore }}/{{ objective.points }}
                        </span>
                        <v-icon 
                            :icon="objective.userScore > objective.points*course.rewards.objectives.blue/100 ? 'mdi-check-decagram' : 'mdi-decagram'"
                            size="large" 
                            :color="objectiveIconColor(objective.userScore, objective.points)" class="ml-1"
                        />
                    </div>
                    <div class="text-subtitle-2 text-captionColor">
                        {{ pickLg(objective.description)}}
                    </div>
                </div>
                </v-tabs-window-item>
                <v-tabs-window-item value="bonus">
                    <span class="d-flex align-center justify-center text-captionColor text-h4 pa-12">
                        Bonuses will come later...
                    </span>
                </v-tabs-window-item>
            </v-tabs-window>
        </v-row>
    </v-container>
</template>
<script setup>
    import { ref, computed } from "vue";
    import { useI18n } from 'vue-i18n';

    const { locale } = useI18n();

    const props = defineProps({ course: Object });

    const tab = ref(0);

    const globalScores = computed(() => {
        const section = [];
        let total = 0;
        props.course.sections.forEach(s => {
            const userScore = s.objectives.reduce((sum, o) => sum + (o.userScore || 0), 0);
            const points = s.objectives.reduce((sum, o) => sum + (o.points || 0), 0);
            section.push({ userScore, points });
            total += userScore;
        });

        return { section, total }
    });

    const pickLg = o => {
        if(o){
            if(locale.value == 'en'){
                return o.en && o.en.length ? o.en : o.fr
            }
            return o.fr && o.fr.length ? o.fr : o.en
        }
        return '';
    }

    const objectiveIconColor = (userScore, points) => {
        if(userScore > points*props.course.rewards.objectives.green/100){
            return 'success';
        }
        if(userScore > points*props.course.rewards.objectives.blue/100){
            return 'primary';
        }
        return 'grey-lighten-2';
    }
    const levelName = computed(() => {
        if (!props.course.rewards?.levels || !props.course.rewards.levels.length) return '';
        // Filter levels with points <= total, then get the one with the highest points
        const eligible = props.course.rewards.levels
            .filter(lvl => (lvl.points ?? 0) <= globalScores.value.total);
        if (!eligible.length) return '';
        const highest = eligible.reduce((max, lvl) => (lvl.points > max.points ? lvl : max), eligible[0]);
        return pickLg(highest.name);
    });
</script>

<style scoped>
    /* .dots {
        flex: 1 1 auto;
        border-bottom: 1px dotted #888;
        margin: 0 0.5em;
        height: 1em;
    } */
    .dots {
        flex: 1 1 auto;
        margin: 0 0.5em;
        height: 1em;
        background-image: repeating-linear-gradient(
            to right,
            #BBB 0 2px,
            transparent 3px 6px
        );
        background-position: bottom;
        background-repeat: repeat-x;
        background-size: auto 1px;
        content: "";
        display: block;
    }
</style>