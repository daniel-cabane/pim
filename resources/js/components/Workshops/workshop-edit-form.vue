<template>
    <v-tabs v-model="tab">
        <v-tab value="t-d">{{ $t('Title and description') }}</v-tab>
        <v-tab value="details">{{ $t('Details') }}</v-tab>
        <v-tab value="poster">{{ $t('Poster') }}</v-tab>
        <v-tab value="sessions" v-if="user.is.admin || workshop.status == 'launched'">{{ $t('Sessions') }}</v-tab>
        <v-spacer />
        <v-select variant="plain" flat :label="$t('Status')" :disabled="statusMenuDisabled" :items="statusOptions"
            v-model="workshop.status" />
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
                    <div class="text-caption text-caption-color">
                        Description (fr)
                    </div>
                    <Editor api-key="c6xujr454hv9o3u7uqat7dlla2v61j7n3syp29hhj0k4aeeu" :init="{
                        plugins: 'lists link image table code help wordcount'
                    }" v-model="workshop.description.fr" v-if="workshop.language != 'en'" />
                </div>
                <div v-if="workshop.language !='fr'" class=" mb-3">
                    <div class="text-caption text-caption-color">
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
                        <v-select v-model="workshop.teacherId" :items="teachersOptions" label="Teacher"
                            variant="outlined" />
                    </div>
                    <div class="d-block d-sm-flex align-start" style="gap:5px;">
                        <v-select label="Campus" :items="['BPR', 'TKO']" v-model="workshop.details.campus"
                            variant="outlined" />
                        <v-text-field :rules="[rules.required]" v-model="workshop.details.roomNb" :label="$t('Room')"
                            variant="outlined" validate-on="blur" />
                        <v-btn icon="mdi-pi" @click='initRoomNb' class="mt-1"
                            :disabled="workshop.details.campus != 'BPR'" />
                    </div>
                    <div class="d-block d-sm-flex align-center" style="gap:5px;">
                        <v-text-field :rules="[rules.required]" type="number" min="1" max="99"
                            v-model="workshop.details.nbSessions" :label="$t('Nb sessions')" variant="outlined"
                            validate-on="blur" style="flex:1" />
                        <v-text-field :rules="[rules.required]" type="number" min="1" max="99"
                            v-model="workshop.details.maxStudents" :label="$t('Nb students max')" variant="outlined"
                            validate-on="blur" style="flex:1" />
                        <v-switch :label="$t('Registration open')" color="primary" v-model="workshop.acceptingStudents"
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
                        <v-select variant="outlined" :label="$t('Day')" v-model="session.day"
                            style="flex:2" />
                        <v-text-field :rules="[rules.required]" :label="$t('Start')" variant="outlined"
                            validate-on="blur" type="time" v-model="session.start" style="flex:1" />
                        <v-text-field :rules="[rules.required]" :label="$t('Finish')" variant="outlined"
                            validate-on="blur" type="time" v-model="session.finish" style="flex:1" />
                        <v-btn variant="text" size="large" style="margin-right:5px;margin-bottom:21px;"
                            icon="mdi-close-octagon-outline" color="red-lighten-2" @click="removeSession(index)" />
                    </div>
                    <div style="display: flex;gap:5px;">
                        <v-text-field :label="$t('Start date')" variant="outlined" validate-on="blur" type="date"
                            v-model="workshop.startDate" clearable />
                        <v-select :label="$t('Term') " variant="outlined" validate-on="blur" :items="[1,2,3]"
                            v-model="workshop.term" />
                    </div>
                </div>
            </v-window-item>
            <v-window-item class="pt-2" value="poster">
                <v-file-input accept="image/png, image/jpeg, image/jpg" v-model="poster" ref="file"
                    @update:modelValue="posterUpdated" style='display:none;' />
                <poster-picker :language="workshop.language" :details="workshop.details" :imageLoading="imageLoading"
                    @pickPoster="handlePickPoster" @deletePoster="handleDeletePoster"
                    v-if="workshop.language != 'both'" />
                <v-window show-arrows="hover" v-else>
                    <v-window-item v-for="lg in ['fr', 'en']" :key="lg">
                        <poster-picker :language="lg" :details="workshop.details" :imageLoading="imageLoading"
                            @pickPoster="handlePickPoster" />
                    </v-window-item>
                </v-window>
            </v-window-item>
            <v-window-item class="pt-2" value="sessions">
                <v-dialog max-width="850" v-model="finalizeDialog">
                    <template v-slot:default="{ isActive }">
                        <v-card :title="t('Finalize workshop')" style="position:relative;">
                            <v-progress-linear color="primary" indeterminate style="position:absolute;top:0px;"
                                class="pa-0" v-if="isLoading && !isLaunching" />
                            <v-card-text>
                                <sessions-table :sessions="sessions" :isLoading="isLoading"
                                    :daysOfTheWeek="daysOfTheWeek" @deleteSession="deleteSession"
                                    @refreshSessions="prepareFinalizeWorkshop" />
                            </v-card-text>
                            <div style="display:flex;justify-content:flex-end;" class="pa-3">
                                <v-btn variant="tonal" class="mr-3" color="error" :disabled="isLoading"
                                    @click="finalizeDialog = false">
                                    {{ $t('Cancel') }}
                                </v-btn>
                                <v-btn color="success" theme="dark" :disabled="isLoading && sessions.length == 0"
                                    :loading="isLoading && isLaunching" @click="launchWorkshop">
                                    {{ $t('Launch') }}
                                </v-btn>
                            </div>
                        </v-card>
                    </template>
                </v-dialog>
                <div v-if="workshop.status == 'confirmed'" class="text-center pa-4">
                    <v-btn color="primary" @click="prepareFinalizeWorkshop">
                        {{ t('Finalize workshop') }}
                    </v-btn>
                </div>
            </v-window-item>
        </v-window>
    </v-card-text>
</template>
<script setup>
    import { ref, reactive, defineEmits, computed } from 'vue';
    import Editor from '@tinymce/tinymce-vue';
    import { useTheme } from 'vuetify';
    import { useAuthStore } from '@/stores/useAuthStore';
    import { useI18n } from 'vue-i18n';
    import { useDisplay } from 'vuetify';
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    import { storeToRefs } from 'pinia';

    const workshopStore = useWorkshopStore();
const { prepareLaunch, launch } = workshopStore;
    const { isLoading } = storeToRefs(workshopStore);

    const { smAndDown } = useDisplay()

    const theme = useTheme();

    const props = defineProps({ workshop: Object, availableThemes: Array, imageLoading: Boolean });
    const emit = defineEmits(['imageUpdated', 'imageDeleted']);

    const tab = ref('t-d');

    const { t } = useI18n();
    const statusOptions = [{value: 'draft', title: t('Draft')}, {value: 'submitted', title : t('Submitted')}];
    const { user, getTeachers } = useAuthStore();
    let teachers = [];
    teachers = await getTeachers();
    const teachersOptions = computed(() => {
        return teachers.map(t => ({title: t.name, value: t.id}));
    });
    
    if(user.is.admin){
        statusOptions.push({value: 'confirmed', title: t('Confirmed')});
    }
    const statusMenuDisabled = computed(() => {
        return !user.is.admin || !['draft', 'submitted'].includes(props.workshop.status);
    });

    const rules = {
        required: value => !!value || 'Required.',
        minLengthTitle: value => value.length >= 5 || 'The title must at least 5 characters long',
    };

    const daysOfTheWeek = reactive([
        { value: 'Monday', title: t('Monday') },
        { value: 'Tuesday', title: t('Tuesday') },
        { value: 'Wednesday', title: t('Wednesday') },
        { value: 'Thursday', title: t('Thursday') },
        { value: 'Friday', title: t('Friday') },
        { value: 'Saturday', title: t('Saturday') },
        { value: 'Sunday', title: t('Sunday') },
    ]);

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
    const file = ref(null);
    const posterLanguage = ref(null);
    const handlePickPoster = (language) => {
        posterLanguage.value = language;
        file.value.click();
    }
    const handleDeletePoster = (language) => {
        emit('imageDeleted', language);
    }
    const posterUpdated = () => {
        emit('imageUpdated', {language: posterLanguage.value, file: poster.value});       
    }

    const finalizeDialog = ref(false);
    const sessions = ref([]);
    const prepareFinalizeWorkshop = async () => {
        finalizeDialog.value = true;
        const info = await prepareLaunch();
        sessions.value = info.sessions;
    }
    const deleteSession = id => {
        sessions.value = sessions.value.filter(s => s.id != id);
    }

    const isLaunching = ref(false);
    const launchWorkshop = async () => {
        isLaunching.value = true;
        const workshop = await launch({sessions: sessions.value});
    }
</script>