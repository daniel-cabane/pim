<template>
    <v-container v-if="isReady">
        <div class="pb-4">
            <back-btn />
        </div>
        <v-card>
            <workshop-display :workshop="workshop" @editWorkshop="editWorkshop" />
            <div style="overflow-y:hidden;transition:all .5s"
                :style="`max-height:${showApplication ? 320 : 0}px;${!showApplication ? 'content-visibility:hidden;' : ''}`"
                v-if='workshop.application'>
                <div class="pa-4">
                    <v-divider color="black" class="py-2" />
                    <v-alert type="info" class="mb-3">
                        {{ $t('Be sure thate you are available for the sessions. Lessons ALWAYS take precedence over workshops !') }}
                    </v-alert>
                    <div class="text-caption text-captionColor">
                        {{ $t('Availability') }}
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
            </div>
            <div class="px-4 pb-3" v-if="user && user.is && user.is.student">
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
                        <v-chip label size="large" variant="elevated" color="success" append-icon="mdi-check">
                            {{ $t('Applied !') }}
                        </v-chip>
                        <v-btn icon="mdi-eye" color="success" class="ml-3" variant="text"
                            @click="showApplication = true" v-if="workshop.acceptingStudents" />
                    </div>
                </div>
                <div class="d-flex justify-end" v-else-if="workshop.acceptingStudents && workshop.joinable">
                    <v-btn color="primary" append-icon="mdi-human-greeting" @click="showApplication = true"
                        v-if="!showApplication">
                        {{ $t("I'm interested") }}
                    </v-btn>
                </div>
                <div class="d-flex justify-end" v-else>
                    <v-tooltip :text="$t(applyButtonTooltipText)">
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
        </v-card>
    </v-container>
</template>
<script setup>
    import { ref, computed } from "vue";
    import { useRoute, useRouter } from 'vue-router';
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    import { useAuthStore } from '@/stores/useAuthStore';
    import { storeToRefs } from 'pinia';
    
    const workshopStore = useWorkshopStore();
    const { getWorkshop, applyWorkshop, withdrawWorkshop } = workshopStore;
    const { workshop, isReady } = storeToRefs(workshopStore);
    
    const route = useRoute();
    getWorkshop(route.params.id);

    const authStore = useAuthStore();
    const { user } = authStore;

    const router = useRouter();
    const editWorkshop = () => {
        router.push(`/workshops/${route.params.id}/edit`);
    }

    const showApplication = ref(false);
    const applyLoading = ref(false);
    const withdrawLoading = ref(false);
    const handleApply = async () => {
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
    const applyButtonTooltipText = computed (() => workshop.value.joinable ? 'This workshop is not accepting applications at this point' : 'This workshop is not open for your class level');
</script>