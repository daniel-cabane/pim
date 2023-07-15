<template>
    <v-tabs v-model="tab">
        <v-tab value="t-d">{{ $t('Title and description') }}</v-tab>
        <v-tab value="details">{{ $t('Details') }}</v-tab>
    </v-tabs>
    <v-card-text>
        <v-window v-model="tab">
            <v-window-item class="pt-2" value="t-d">
                <v-text-field 
                    :rules="[rules.required, rules.minLengthTitle]"
                    v-model="workshop.title"
                    :label="$t('Title')"
                    variant="outlined" 
                    validate-on="blur"
                />
                <div class="text-caption" :class="theme.global.name.value == 'customDark' ? 'text-grey-lighten-2' : 'text-grey'">
                    Description
                </div>
                <Editor 
                    api-key="no-api-key" 
                    :init="{
                        plugins: 'lists link image table code help wordcount'
                    }" 
                    v-model="workshop.description" 
                />
            </v-window-item>
            <v-window-item class="pt-2" value="details">
                <div>
                    <v-select
                        v-model="workshop.themes"
                        :items="availableThemes"
                        label="Themes"
                        multiple
                        chips
                        variant="outlined"
                    />
                    <div class="d-block d-sm-flex align-center" style="gap:5px;">
                        <v-select 
                            label="Campus"
                            :items="['BPR', 'TKO']"
                            v-model="workshop.details.campus"
                            variant="outlined"
                        />
                        <v-text-field 
                            :rules="[rules.required]"
                            v-model="workshop.details.location"
                            :label="$t('Room')"
                            variant="outlined" 
                            validate-on="blur"
                        />
                    </div>
                    <div class="d-block d-sm-flex align-center" style="gap:5px;">
                        <v-text-field 
                            :rules="[rules.required]"
                            type="number"
                            min="1"
                            max="99"
                            v-model="workshop.details.nbSessions"
                            :label="$t('Nb sessions')"
                            variant="outlined" 
                            validate-on="blur"
                            style="flex:1"
                        />
                        <v-text-field 
                            :rules="[rules.required]"
                            type="number"
                            min="1"
                            max="99"
                            v-model="workshop.details.maxStudents"
                            :label="$t('Nb students max')"
                            variant="outlined" 
                            validate-on="blur"
                            style="flex:1"
                        />
                        <v-switch 
                            :label="$t('Open to registration')" 
                            v-model="workshop.accepting_students"
                            style="flex:1"
                            :disabled="workshop.status != 'confirmed'"
                        />
                    </div>
                    <div class="d-flex justify-space-between align-center px-3 mb-3">
                        <span 
                            class="text-subtitle-1" 
                            :class="theme.global.name.value == 'customDark' ? 'text-grey-lighten-2' : 'text-grey'"
                        >
                            {{ $t('Schedule') }}
                        </span>
                        <v-btn color="secondary" size="small" @click="addSession">{{ $t('Add session') }}</v-btn>
                    </div>
                    <div 
                        class="d-block d-sm-flex align-center" 
                        style="gap:5px;"
                        v-for="(session, index) in workshop.details.schedule"
                        :key="index"
                    >
                        <v-select 
                            variant="outlined" 
                            :label="$t('Day')" 
                            :items="daysOfTheWeek"
                            v-model="session.day"
                            style="flex:2"
                        />
                        <v-text-field 
                            :rules="[rules.required]"
                            :label="$t('Start')"
                            variant="outlined" 
                            validate-on="blur"
                            type="time"
                            v-model="session.start"
                            style="flex:1"
                        />
                        <v-text-field 
                            :rules="[rules.required]"
                            :label="$t('Finish')"
                            variant="outlined" 
                            validate-on="blur"
                            type="time"
                            v-model="session.finish"
                            style="flex:1"
                        />
                        <v-btn 
                            variant="text"
                            style="margin-right:5px;margin-bottom:21px;" 
                            icon="mdi-close-octagon-outline"
                            color="red-lighten-2"
                            @click="removeSession(index)"
                        />
                    </div>
                    <div class="px-3 my-3">
                        <span 
                            class="text-subtitle-1" 
                            :class="theme.global.name.value == 'customDark' ? 'text-grey-lighten-2' : 'text-grey'"
                        >
                            {{ $t('Confirmation') }}
                        </span>
                    </div>
                    <div class="d-block d-sm-flex align-center" style="gap:5px;">
                        <v-text-field 
                        :rules="[rules.required]"
                            :label="$t('Start date')"
                            variant="outlined" 
                            validate-on="blur"
                            type="date"
                            v-model="workshop.start_date"
                            style="flex:1"
                        />
                        <v-select 
                            variant="outlined" 
                            :label="$t('Status')" 
                            :items="['Draft', 'Submitted']"
                            v-model="workshop.status"
                            style="flex:1"
                        />
                    </div>
                </div>
            </v-window-item>
        </v-window>
    </v-card-text>
</template>
<script setup>
    import { ref } from 'vue';
    import Editor from '@tinymce/tinymce-vue';
    import { useTheme } from 'vuetify';

    const theme = useTheme();

    const props = defineProps({ workshop: Object, availableThemes: Array });

    let tab = ref('t-d');

    const rules = {
        required: value => !!value || 'Required.',
        minLengthTitle: value => value.length >= 5 || 'The title must at least 5 characters long',
    };

    const daysOfTheWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    const addSession = () => {
        props.workshop.details.schedule.push({day: 'Monday', start: '17:30', finish: '18:30'});
    }
    const removeSession = index => {
        props.workshop.details.schedule.splice(index, 1);
    }
</script>