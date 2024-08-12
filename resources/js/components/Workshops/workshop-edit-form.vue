<template>
    <v-tabs v-model="tab">
        <v-tab value="t-d">{{ $t('Title and description') }}</v-tab>
        <v-tab value="details">{{ $t('Details') }}</v-tab>
        <v-tab value="poster">{{ $t('Poster') }}</v-tab>
        <v-tab value="sessions" v-if="user.is.admin || workshop.status == 'launched'">{{ $t('Sessions') }}</v-tab>
        <v-spacer />
        <v-chip label size="large" color="success" class="ma-2" v-if="workshop.status == 'launched'">
            {{ $t('Launched') }}
        </v-chip>
        <v-select variant="plain" flat :label="$t('Status')" :disabled="statusMenuDisabled" :items="statusOptions"
            v-model="workshop.status" v-else />
    </v-tabs>
    <v-card-text v-if="isReady">
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
                    <div class="text-caption text-captionColor">
                        Description (fr)
                    </div>
                    <Editor api-key="c6xujr454hv9o3u7uqat7dlla2v61j7n3syp29hhj0k4aeeu" :init="{
                        plugins: 'lists link image table code help wordcount', height: 300, resize: false
                    }" v-model="workshop.description.fr" v-if="workshop.language != 'en'" />
                </div>
                <div v-if="workshop.language !='fr'" class=" mb-3">
                    <div class="text-caption text-captionColor">
                        Description (en)
                    </div>
                    <Editor api-key="c6xujr454hv9o3u7uqat7dlla2v61j7n3syp29hhj0k4aeeu" :init="{
                        plugins: 'lists link image table code help wordcount', height: 300, resize: false
                    }" v-model="workshop.description.en" v-if="workshop.language != 'fr'" />
                </div>
                <v-divider class="my-6"/>
                <survey-table :workshopId="workshop.id"/>
            </v-window-item>
            <v-window-item class="pt-2" value="details">
                <v-form :disabled="workshop.status == 'launched'">
                    <div style='position:relative'>
                        <div class="d-block d-sm-flex align-center" style="gap:5px;">
                            <v-select v-model="workshop.themes" :items="availableThemes" label="Themes" multiple chips
                                variant="outlined" />
                            <v-select v-model="workshop.teacherId" :items="teachersOptions" :disabled="!user.is.admin"
                                label="Teacher" variant="outlined" />
                        </div>
                        <div class="d-block d-sm-flex align-start" style="gap:5px;">
                            <v-select label="Campus" :items="['BPR', 'TKO']" v-model="workshop.campus"
                                variant="outlined" />
                            <v-text-field :rules="[rules.required]" v-model="workshop.details.roomNb"
                                :label="$t('Room')" variant="outlined" validate-on="blur" />
                            <v-btn icon="mdi-pi" @click='initRoomNb' class="mt-1"
                                :disabled="workshop.campus != 'BPR'" />
                        </div>
                        <div class="d-block d-sm-flex align-center" style="gap:5px;">
                            <v-text-field :rules="[rules.required]" type="number" min="1" max="99"
                                v-model="workshop.details.nbSessions" :label="$t('Nb sessions')" variant="outlined"
                                validate-on="blur" style="flex:1" />
                            <v-text-field :rules="[rules.required]" type="number" min="1" max="99"
                                v-model="workshop.details.maxStudents" :label="$t('Nb students max')" variant="outlined"
                                validate-on="blur" style="flex:1" />
                            <v-switch :label="$t('Registration open')" color="primary"
                                v-model="workshop.acceptingStudents" style="flex:1"
                                :disabled="workshop.status != 'confirmed'" />
                        </div>
                        <div class="d-flex justify-space-between align-center px-3 mb-3">
                            <span class="text-subtitle-1 text-captionColor">
                                {{ $t('Schedule') }}
                            </span>
                            <v-btn color="secondary" size="small" :disabled="workshop.status == 'launched'"
                                @click="addSession">
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
                        <div style="display: flex;gap:5px;">
                            <v-text-field :label="$t('Start date')" variant="outlined" validate-on="blur" type="date"
                                v-model="workshop.startDate" clearable />
                            <v-select :label="$t('Term') " variant="outlined" validate-on="blur" :items="[1,2,3]"
                                v-model="workshop.term" />
                        </div>
                    </div>
                </v-form>
            </v-window-item>
            <v-window-item class="pt-2" value="poster">
                <v-file-input accept="image/png, image/jpeg, image/jpg" v-model="poster" ref="file"
                    @update:modelValue="updateImage({ language: posterLanguage, file: poster })"
                    style='display:none;' />
                <poster-picker :language="workshop.language" :details="workshop.details" :imageLoading="imageLoading"
                    @pickPoster="handlePickPoster(workshop.language)" @deletePoster="deleteImage"
                    v-if="workshop.language != 'both'" />
                <v-window show-arrows="hover" v-else>
                    <v-window-item v-for="lg in ['fr', 'en']" :key="lg">
                        <poster-picker :language="lg" :details="workshop.details" :imageLoading="imageLoading"
                            @pickPoster="handlePickPoster(lg)" />
                    </v-window-item>
                </v-window>
            </v-window-item>
            <v-window-item class="pt-2" value="sessions">
                <v-dialog scrollable max-width="850" v-model="finalizeDialog">
                    <template v-slot:default="{ isActive }">
                        <v-card :title="`${t('Finalize workshop')} - ${workshop.mainTitle}`" style="position:relative;">
                            <v-progress-linear color="primary" indeterminate style="position:absolute;top:0px;"
                                class="pa-0" v-if="isLoading && !isLaunching" />
                            <v-card-text>
                                <v-tabs v-model="finalizeTab">
                                    <v-tab value="0">
                                        {{ $t('Sessions') }} ({{ workshop.sessions.length }})
                                    </v-tab>
                                    <v-tab value="1">
                                        {{ $t('Applicants') }} ({{ applicantsCount}})
                                    </v-tab>
                                    <v-tab value="2">
                                        {{ $t('Recap') }}
                                    </v-tab>
                                </v-tabs>
                                <v-tabs-window v-model="finalizeTab">
                                    <v-tabs-window-item value="0">
                                        <sessions-table :workshop="workshop" :isLoading="isLoading"
                                            @deleteSession="handleDeleteSession" @refreshSessions="prepareFinalizeWorkshop"
                                            @createSession="handleCreateSession" @editSession="handleEditSession"
                                            @orderSessions="orderSessions" />
                                    </v-tabs-window-item>
                                    <v-tabs-window-item value="1">
                                        <applicants-table :applicants="workshop.applicants" @confirmMaxApplicants="confirmMaxApplicants"/>
                                    </v-tabs-window-item>
                                    <v-tabs-window-item value="2">
                                        <workshop-recap :workshop="workshop" />
                                    </v-tabs-window-item>
                                </v-tabs-window>
                            </v-card-text>
                            <div style=" display:flex;justify-content:flex-end;" class="pa-3">
                                <v-btn variant="tonal" class="mr-3" color="error" :disabled="isLoading"
                                    @click="finalizeDialog = false">
                                    {{ $t('Cancel') }}
                                </v-btn>
                                <v-btn color="primary" v-if="finalizeTab < 2" @click="finalizeTab++">
                                    {{ $t('Next') }}
                                </v-btn>
                                <v-btn color="success" :disabled="isLoading || workshop.sessions.length == 0"
                                    :loading="isLoading && isLaunching" @click="launchWorkshop" v-else>
                                    {{ $t('Launch') }}
                                </v-btn>
                            </div>
                        </v-card>
                    </template>
                </v-dialog>
                <div v-if="workshop.status == 'confirmed'" class="text-center pa-4">
                    <v-btn color="primary" @click="prepareFinalizeWorkshop">
                        {{ $t('Finalize workshop') }}
                    </v-btn>
                </div>
                <div v-if="workshop.status == 'launched'">
                    <sessions-table :workshop="workshop" :isLoading="isLoading" @deleteSession="handleDeleteSession"
                        @refreshSessions="refreshSessions" @createSession="handleCreateSession"
                        @editSession="handleEditSession" @orderSessions="orderSessions" />
                </div>
            </v-window-item>
        </v-window>
    </v-card-text>
</template>
<script setup>
    import { ref, reactive, computed } from 'vue';
    import Editor from '@tinymce/tinymce-vue';
    import { useAuthStore } from '@/stores/useAuthStore';
    import { useI18n } from 'vue-i18n';
    import { useDisplay } from 'vuetify';
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    import { storeToRefs } from 'pinia';
    import { useRoute } from 'vue-router';

    const workshopStore = useWorkshopStore();
    const { prepareLaunch, launch, getWorkshop, getThemes, updateImage, deleteImage, deleteSession, createSession, updateSession, orderSessions } = workshopStore;
    const { workshop, isReady, themes, imageLoading, isLoading } = storeToRefs(workshopStore);

    const route = useRoute();
    getWorkshop(route.params.id);

    getThemes();
    const { locale, t } = useI18n();
    const availableThemes = computed(() => themes.value.map(theme => {
        return {
            title: locale == 'en' ? theme.title_en : theme.title_fr,
            value: theme.id
        }
    }));

    const { smAndDown } = useDisplay()

    // const props = defineProps({ workshop: Object, availableThemes: Array, imageLoading: Boolean });
    // const emit = defineEmits(['imageUpdated', 'imageDeleted']);

    const tab = ref('t-d');

    const statusOptions = [{value: 'draft', title: t('Draft')}, {value: 'submitted', title : t('Submitted')}];
    const { user, getTeachers } = useAuthStore();
    const teachers = await getTeachers();
    const teachersOptions = computed(() => {
        return teachers.map(t => ({title: t.name, value: t.id}));
    });
    
    if(user.is.admin){
        statusOptions.push({value: 'confirmed', title: t('Confirmed')});
    }
    const statusMenuDisabled = computed(() => {
        return !user.is.admin && !['draft', 'submitted'].includes(workshop.value.status);
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
        workshop.value.details.schedule.push({day: 'Monday', start: '17:30', finish: '18:30'});
    }
    const removeSession = index => {
        workshop.value.details.schedule.splice(index, 1);
    }

    const initRoomNb = () => {
        workshop.value.details.roomNb = 'π (314 BPR)';
    }

    const poster = ref(null);
    const file = ref(null);
    const posterLanguage = ref(null);
    const handlePickPoster = language => {
        posterLanguage.value = language;
        file.value.click();
    }

    const finalizeDialog = ref(false);
    const prepareFinalizeWorkshop = async () => {
        workshop.value.notifyApplicants = {confirmed: true, unconfirmed: true};
        finalizeDialog.value = true;
        const info = await prepareLaunch();
        workshop.value.sessions = info.sessions;
        // backUpSessions.value = [...info.sessions];
    }
    const refreshSessions = () => {
        // workshop.value.sessions = backUpSessions; <------------------------------------------------ FIX THAT
    }
    const handleDeleteSession = id => {
        if(workshop.value.status == 'launched'){
            deleteSession(id);
        } else {
            workshop.value.sessions = workshop.value.sessions.filter(s => s.id != id);
        }
    }
    const handleCreateSession = () => { 
        if (workshop.value.status == 'launched') {
            createSession();
        } else { // TODO -> MAKE THIS SMARTER (NEXT SESSION NOT JUST NEXT WEEK)
            const lastSession = workshop.value.sessions[workshop.value.sessions.length-1];
            const lastSessionDate = new Date(lastSession.date)
            const date = new Date(lastSessionDate.setDate(lastSessionDate.getDate() + 7));
            workshop.value.sessions.push({
                date: date.toISOString().slice(0, 10), id: lastSession.id+1, start: lastSession.start, finish: lastSession.finish, status: 'confirmed'
            });
        }
    }

    const handleEditSession = session => {
        if (workshop.value.status == 'launched') {
            updateSession(session);
        } else {
            workshop.value.sessions = workshop.value.sessions.map(s => s.id == session.id ? session : s);
        }
    }

    const finalizeTab = ref(0);
    const applicantsCount = computed(() => {
        const confirmed = workshop.value.applicants.filter(applicant => applicant.confirmed).length;
        return `${confirmed}/${workshop.value.details.maxStudents}`
    });
    const confirmMaxApplicants = () => {
        workshop.value.applicants.forEach(a => {
            if(a.available && workshop.value.applicants.filter(applicant => applicant.confirmed).length < workshop.value.details.maxStudents) {
                a.confirmed = true;
            }
        });
    }
    
    const isLaunching = ref(false);
    const launchWorkshop = async () => {
        isLaunching.value = true;
        await launch({sessions: workshop.value.sessions, applicants: workshop.value.applicants, notifyApplicants: workshop.value.notifyApplicants});
        finalizeDialog.value = false;
    }
</script>