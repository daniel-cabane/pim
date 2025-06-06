<template>
    <v-container v-if="isReady">
        <div class="pb-4">
            <back-btn />
        </div>
        <v-card>
            <workshop-display :workshop="workshop" @editWorkshop="editWorkshop" />
            <!-- ////////////////////////////////////////////////// -->
             <div v-if="user && user.is && user.is.teacher"/>
             <div class="px-3 pb-3 d-flex justify-end" v-else-if="workshop.status == 'confirmed'">
                 <v-dialog v-model="showApplication" max-width="500" v-if="workshop.acceptingStudents">
                     <template v-slot:activator="{ props: activatorProps }">
                        <v-btn color="success" append-icon="mdi-check-bold" v-bind="activatorProps" v-if="workshop.application && workshop.application.submitted">
                            {{ $t("Registered") }}
                        </v-btn>
                         <v-btn color="primary" append-icon="mdi-human-greeting" v-bind="activatorProps" v-else>
                             {{ $t("I'm interested") }}
                         </v-btn>
                     </template>
     
                     <template v-slot:default="{ isActive }">
                         <v-card :title="$t(`I'm interested`)">
                            <v-card-text v-if="!user">
                                <div>
                                    {{ $t('You need to be signed in to express interest') }}
                                </div>
                                <div class="text-center">
                                    <google-button />
                                </div>
                                <div class="py-2 text-caption text-captionColor text-center">
                                    <span v-if="locale == 'en'">
                                    @g.lfis.edu.hk accounts only
                                    </span>
                                    <span v-else>
                                    Comptes @g.lfis.edu.hk uniquement
                                    </span>
                                </div>
                            </v-card-text>
                            <v-card-text v-else-if="user.is && user.is.student && workshop.joinable">
                                <v-alert type="info" class="mb-3">
                                    {{ $t('Be sure that you are available for the sessions. Lessons ALWAYS take precedence over workshops !') }}
                                </v-alert>
                                <div class="text-caption text-captionColor">
                                    {{ $t('Availability') }}
                                </div>
                                <div>
                                    <v-radio-group 
                                        v-model="workshop.application.available"
                                        :disabled="applyLoading || withdrawLoading"
                                    >
                                        <v-radio :label="$t('I\'m available')" :value="true" />
                                        <v-radio :label="$t('I\'m interested but not available at the time specified')" :value="false" />
                                    </v-radio-group>
                                    <v-textarea
                                        :label="$t('Comment (optional)')" v-model="workshop.application.comment"
                                        :disabled="applyLoading || withdrawLoading" 
                                        :resize="false"
                                    />
                                </div>
                            </v-card-text>
                            <v-card-text v-else>
                                <v-alert type="warning" class="mb-3">
                                    {{ $t('This workshop is not open for students of your level') }}
                                </v-alert>
                                <div class="text-caption text-captionColor">
                                    {{ $t('Contact PIM') }}
                                </div>
                                <v-textarea
                                    label="Message" v-model="messageToPim"
                                    :disabled="applyLoading || withdrawLoading" 
                                    :resize="false"
                                    :error-messages="textAreaErrorMessage"
                                />
                            </v-card-text>
                            <v-card-actions>
                                <v-btn 
                                    variant="tonal" color="warning" 
                                    :loading="withdrawLoading"
                                    :disabled="applyLoading" @click="handleWithdraw"
                                    v-if="workshop.application && workshop.application.submitted"
                                >
                                    {{ $t('Withdraw') }}
                                </v-btn>
                                <v-spacer/>
                                <v-btn color="error" :text="$t('Close')" :disabled="applyLoading || messageSending || withdrawLoading" @click="isActive.value = false"/>
                                <v-btn color="primary" variant="elevated" :text="$t('Update')" :loading="applyLoading" @click="handleApply" v-if="workshop.application && workshop.application.submitted"/>
                                <v-btn color="primary" variant="elevated" style="width:100px" :text="$t('Send')" :loading="messageSending" @click="sendMessageToPim" v-else-if="!workshop.joinable"/>
                                <v-btn color="primary" variant="elevated" :text="$t('Submit')" :loading="applyLoading" @click="handleApply" v-else-if="user"/>
                            </v-card-actions>
                         </v-card>
                     </template>
                 </v-dialog>
                 <v-tooltip :text="$t('This workshop is not accepting applications at this point')" v-else>
                        <template v-slot:activator="{ props }">
                            <span v-bind="props">
                                <v-btn append-icon="mdi-human-greeting" disabled>
                                    {{ $t("I'm interested") }}
                                </v-btn>
                            </span>
                        </template>
                    </v-tooltip>
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
    import { useI18n } from 'vue-i18n';
    const { locale, t } = useI18n();
    
    const workshopStore = useWorkshopStore();
    const { getWorkshop, applyWorkshop, withdrawWorkshop } = workshopStore;
    const { workshop, isReady } = storeToRefs(workshopStore);
    
    const route = useRoute();
    getWorkshop(route.params.id);

    const authStore = useAuthStore();
    const { user, msgToAdmin } = authStore;

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
    // const applyButtonTooltipText = computed (() => workshop.value.joinable ? 'This workshop is not accepting applications at this point' : 'This workshop is not open for your class level');

    const messageToPim = ref('');
    const messageSending = ref(false);
    const textAreaErrorMessage = ref(null);
    const sendMessageToPim = async () => {
        if(messageToPim.value.length == 0){
            textAreaErrorMessage.value = t('Please enter a message')
        } else {
            textAreaErrorMessage.value = null;
            messageSending.value = true;
            const res = await msgToAdmin(`About ${workshop.value.mainTitle} : ${messageToPim.value}`);
            messageSending.value = false;
            messageToPim.value = '';
            showApplication.value = false;
        }
    }
</script>