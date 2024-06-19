<template>
    <v-tabs v-model="tab">
        <v-tab value="t-d">{{ $t('Title and description') }}</v-tab>
        <v-tab value="details">{{ $t('Details') }}</v-tab>
        <v-tab value="poster">{{ $t('Poster') }}</v-tab>
    </v-tabs>
    <v-card-text>
        <v-window v-model="tab">
            <v-window-item class="pt-2" value="t-d">
                <div style="display:flex;gap:10px;" :style="smAndDown ? 'flex-direction:column-reverse' : ''">
                    <v-text-field :rules="[rules.required, rules.minLengthTitle]" v-model="workshop.title.fr"
                        label="Titre (fr)" variant="outlined" validate-on="blur" style="flex:3"
                        v-if="workshop.language != 'en'" />
                    <v-text-field :rules="[rules.required, rules.minLengthTitle]" v-model="workshop.title.en"
                        label="Title (en)" variant="outlined" validate-on="blur" style="flex:3"
                        v-if="workshop.language != 'fr'" />
                    <v-select :label="$t('Language')" variant="outlined" v-model="workshop.language"
                        :items="[{ value: 'fr', title: $t('French') }, { value: 'en', title: $t('English') }, { value: 'both', title: $t('Bilingual') }]" />
                </div>
                <div v-if="workshop.language != 'en'" class="mb-3">
                    <div class="text-caption"
                        :class="theme.global.name.value == 'customDark' ? 'text-grey-lighten-2' : 'text-grey'">
                        Description (fr)
                    </div>
                    <Editor api-key="c6xujr454hv9o3u7uqat7dlla2v61j7n3syp29hhj0k4aeeu" :init="{
                        plugins: 'lists link image table code help wordcount'
                    }" v-model="workshop.description.fr" v-if="workshop.language != 'en'" />
                </div>
                <div v-if="workshop.language !='fr'" class=" mb-3">
                    <div class="text-caption"
                        :class="theme.global.name.value == 'customDark' ? 'text-grey-lighten-2' : 'text-grey'">
                        Description (en)
                    </div>
                    <Editor api-key="c6xujr454hv9o3u7uqat7dlla2v61j7n3syp29hhj0k4aeeu" :init="{
                        plugins: 'lists link image table code help wordcount'
                    }" v-model="workshop.description.en" v-if="workshop.language != 'fr'" />
                </div>
            </v-window-item>
            <v-window-item class="pt-2" value="details">
                <div>
                    <div class="d-block d-sm-flex align-center" style="gap:5px;">
                        <v-select v-model="workshop.themes" :items="availableThemes" label="Themes" multiple chips
                            variant="outlined" />
                        <v-select v-model="workshop.teacher" :items="availableThemes" label="Teacher"
                            variant="outlined" />
                    </div>
                    <div class="d-block d-sm-flex align-start" style="gap:5px;">
                        <v-select label="Campus" :items="['BPR', 'TKO']" v-model="workshop.details.campus"
                            variant="outlined" />
                        <v-text-field :rules="[rules.required]" v-model="workshop.details.roomNb" :label="$t('Room')"
                            variant="outlined" validate-on="blur" />
                        <v-btn icon="mdi-pi" @click='initRoomNb' class="mt-1" />
                    </div>
                    <div class="d-block d-sm-flex align-center" style="gap:5px;">
                        <v-text-field :rules="[rules.required]" type="number" min="1" max="99"
                            v-model="workshop.details.nbSessions" :label="$t('Nb sessions')" variant="outlined"
                            validate-on="blur" style="flex:1" />
                        <v-text-field :rules="[rules.required]" type="number" min="1" max="99"
                            v-model="workshop.details.maxStudents" :label="$t('Nb students max')" variant="outlined"
                            validate-on="blur" style="flex:1" />
                        <v-switch :label="$t('Open to registration')" v-model="workshop.accepting_students"
                            style="flex:1" :disabled="workshop.status != 'confirmed'" />
                    </div>
                    <div class="d-flex justify-space-between align-center px-3 mb-3">
                        <span class="text-subtitle-1"
                            :class="theme.global.name.value == 'customDark' ? 'text-grey-lighten-2' : 'text-grey'">
                            {{ $t('Schedule') }}
                        </span>
                        <v-btn color="secondary" size="small" @click="addSession">
                            {{ $t('Add session') }}
                        </v-btn>
                    </div>
                    <div class="d-block d-sm-flex align-center" style="gap:5px;"
                        v-for="(session, index) in workshop.details.schedule" :key="index">
                        <v-select variant="outlined" :label="$t('Day')" :items="daysOfTheWeek" v-model="session.day"
                            style="flex:2" />
                        <v-text-field :rules="[rules.required]" :label="$t('Start')" variant="outlined"
                            validate-on="blur" type="time" v-model="session.start" style="flex:1" />
                        <v-text-field :rules="[rules.required]" :label="$t('Finish')" variant="outlined"
                            validate-on="blur" type="time" v-model="session.finish" style="flex:1" />
                        <v-btn variant="text" size="large" style="margin-right:5px;margin-bottom:21px;"
                            icon="mdi-close-octagon-outline" color="red-lighten-2" @click="removeSession(index)" />
                    </div>
                    <div class="px-3 my-3">
                        <span class="text-subtitle-1"
                            :class="theme.global.name.value == 'customDark' ? 'text-grey-lighten-2' : 'text-grey'">
                            {{ $t('Confirmation') }}
                        </span>
                    </div>
                    <div class="d-block d-sm-flex align-center" style="gap:5px;">
                        <v-text-field :label="$t('Start date')" variant="outlined" validate-on="blur" type="date"
                            v-model="workshop.start_date" style="flex:1" />
                        <v-select variant="outlined" :label="$t('Status')" :items="statusOptions"
                            v-model="workshop.status" style="flex:1" />
                    </div>
                </div>
            </v-window-item>
            <v-window-item class="pt-2" value="poster">
                <v-file-input accept="image/png, image/jpeg, image/jpg" v-model="poster" ref="file"
                    @update:modelValue="posterUpdated" style='display:none;' />
                <v-window show-arrows="hover">
                    <v-window-item v-for="lg in ['fr', 'en']" :key="lg">
                        <poster-picker :language="lg" :details="workshop.details" :imageLoading="imageLoading"
                            @pickPoster="handlePickPoster" />
                    </v-window-item>
                </v-window>
            </v-window-item>
        </v-window>
    </v-card-text>
</template>
<script setup>
    import { ref, defineEmits } from 'vue';
    import Editor from '@tinymce/tinymce-vue';
    import { useTheme } from 'vuetify';
    import { useAuthStore } from '@/stores/useAuthStore';
    import { useI18n } from 'vue-i18n';
    import { useDisplay } from 'vuetify';

    const { smAndDown } = useDisplay()

    const theme = useTheme();

    const props = defineProps({ workshop: Object, availableThemes: Array, imageLoading: Boolean });
    console.log(props.workshop);

    let tab = ref('t-d');

    const { t } = useI18n();
    const statusOptions = [{value: 'draft', title: t('Draft')}, {value: 'submitted', title : t('Submitted')}];
    const authStore = useAuthStore();
    const { user } = authStore;
    
    if(user.is.admin){
        statusOptions.push({value: 'confirmed', title: t('Confirmed')});
    }

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

    const initRoomNb = () => {
        props.workshop.details.roomNb = 'π (314 BPR)';
    }

    const poster = ref(null);
    const file = ref(null)
    const posterLanguage = ref(null);
    const emit = defineEmits(['imageUpdated']);
    const handlePickPoster = (language) => {
        posterLanguage.value = language;
        file.value.click();
    }
    const posterUpdated = () => {
        emit('imageUpdated', {language: posterLanguage.value, file: poster.value});       
    }
</script>