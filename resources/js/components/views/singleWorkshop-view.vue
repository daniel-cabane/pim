<template>
    <v-container v-if="isReady">
        <div class="pa-4">
            <back-btn />
        </div>
        <v-card>
            <workshop-display :workshop="workshop" @editApplicantName="editApplicantName" />
            <div class="pa-4" style="overflow-y:hidden;transition:all .5s"
                :style="`max-height:${showApplication ? 250 : 0}px;${!showApplication ? 'content-visibility:hidden;' : ''}`"
                v-if='workshop.application'>
                <v-divider color="black" class="py-2" />
                <div class="text-caption text-caption-color">
                    Availability
                </div>
                <div>
                    <v-radio-group v-model="workshop.application.available"
                        :disabled="applyLoading || withdrawLoading || !workshop.acceptingStudents">
                        <v-radio :label="$t('I\'m available')" :value="true" />
                        <v-radio :label="$t('I\'m interested but not available at the time specified')"
                            :value="false" />
                    </v-radio-group>
                    <v-text-field label="Comment (optional)" v-model="workshop.application.comment"
                        :disabled="applyLoading || withdrawLoading || !workshop.acceptingStudents" />
                </div>
            </div>
            <div class="pa-4">
                <div class="d-flex justify-end" v-if="workshop.editable">
                    <span>
                        <v-btn color="primary" append-icon="mdi-pencil" @click="editWorkshop">
                            {{ $t('Edit') }}
                        </v-btn>
                    </span>
                </div>
                <div v-if="user && user.is && user.is.student">
                    <div class="d-flex justify-space-between" v-if="showApplication">
                        <v-btn variant="tonal" color="warning" :loading="applyLoading"
                            :disabled="applyLoading || !workshop.acceptingStudents" @click="handleWithdraw"
                            v-if="workshop.application.submitted">
                            {{ $t('Withdraw') }}
                        </v-btn>
                        <span v-else />
                        <span>
                            <v-btn variant="tonal" class="mr-3" color="error"
                                :disabled="applyLoading || withdrawLoading || !workshop.acceptingStudents"
                                @click="showApplication = false">
                                {{ $t('Cancel') }}
                            </v-btn>
                            <v-btn color="primary" :loading="applyLoading"
                                :disabled="withdrawLoading || !workshop.acceptingStudents" @click="handleApply">
                                {{ workshop.application.submitted ? $t('Update') : $t('Submit') }}
                            </v-btn>
                        </span>
                    </div>
                    <div class="d-flex justify-end" v-else-if="workshop.application.submitted">
                        <div>
                            <v-chip label size="large" color="success" append-icon="mdi-check">
                                {{ $t('Applied !') }}
                            </v-chip>
                            <v-btn icon="mdi-eye" color="success" size="small" class="ml-3"
                                variant="outlined" @click="showApplication = true" />
                        </div>
                    </div>
                    <div class="d-flex justify-end" v-else-if="workshop.acceptingStudents">
                        <v-btn color="primary" append-icon="mdi-human-greeting" @click="showApplication = true"
                            v-if="!showApplication">
                            {{ $t("I'm interested") }}
                        </v-btn>
                    </div>
                    <div class="d-flex justify-end" v-else>
                        <v-tooltip :text="$t('This workshop is not accepting applications at this point')">
                            <template v-slot:activator="{ props }">
                                <span v-bind="props">
                                    <v-btn append-icon="mdi-human-greeting" disabled>
                                        {{ $t("I'm interested") }}
                                    </v-btn>
                                </span>
                            </template>
                        </v-tooltip>
                    </div>
                </div>
            </div>
        </v-card>
    </v-container>
</template>
<script setup>
    import { ref } from "vue";
    import { useRoute, useRouter } from 'vue-router';
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    import { useAuthStore } from '@/stores/useAuthStore';
    import { storeToRefs } from 'pinia';
    
    const workshopStore = useWorkshopStore();
    const { getWorkshop, applyWorkshop, withdrawWorkshop, editApplicantName } = workshopStore;
    const { workshop, isReady } = storeToRefs(workshopStore);
    
    const route = useRoute();
    getWorkshop(route.params.id);

    const authStore = useAuthStore();
    const { user } = authStore;

    const router = useRouter();
    const editWorkshop = () => {
        router.push(`/workshops/${route.params.id}/edit`);
    }

    // const applyDialog = ref(false);
    const showApplication = ref(false);
    const applyLoading = ref(false);
    const withdrawLoading = ref(false);
    // const availability = ref(true);
    // const comment = ref('');
    const handleApply = async () => {
        console.log(workshop.value);
        applyLoading.value = true;
        await applyWorkshop({ availability: workshop.value.application.available, comment: workshop.value.application.comment});
        showApplication.value = false;
        applyLoading.value = false;
    }

    const handleWithdraw = async () => {
        withdrawLoading.value = true;
        await withdrawWorkshop();
        showApplication.value = false;
        withdrawLoading.value = false;
    }
</script>