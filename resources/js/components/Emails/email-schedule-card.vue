<template>
    <v-card :title="$t('Update schedule')">
        <v-window v-model="window">
            <v-window-item :key="0">
                <v-card-text>
                    <v-text-field :label="$t('Date')" variant="outlined" type="date" v-model="date" />
                    <v-text-field :label="$t('Time')" variant="outlined" type="time" v-model="time" />
                    <v-btn style="flex:1" color="primary" variant="tonal" block @click="initDatetime">
                        {{ $t('Clear Schedule') }}
                    </v-btn>
                    <div style="display:flex;gap:15px;">
                    </div>
                </v-card-text>
                <v-card-actions>
                    <v-btn color="primary" variant="tonal" @click="window=1">
                        {{ $t('Send') }}
                    </v-btn>
                    <v-spacer />
                    <v-btn variant="text" color="error" :disabled="isLoading" @click="emit('closeDialog')">
                        {{ $t('Cancel') }}
                    </v-btn>
                    <v-btn variant="flat" color="success" :loading="isLoading"
                        @click="emit('updateSchedule', date == null && time == null ? null : `${date} ${time}:00`)">
                        {{ $t('Submit') }}
                    </v-btn>
                </v-card-actions>
            </v-window-item>
            <v-window-item :key="1">
                <v-btn variant="text" prepend-icon="mdi-chevron-left" @click="window=0">
                    Back
                </v-btn>
                <v-card-text>
                    <div class="pb-6 d-flex align-center">
                        <v-icon icon="mdi-alert-box" color="secondary" class="mr-3" size="56px"/>
                        <span>
                            {{ $t('Are you sure the message is ready to be sent straight away') }} ?
                        </span>
                    </div>
                    <v-btn color="primary" block @click="emit('sendMail', email)" :loading="isLoading">
                        {{ $t('Send now') }}
                    </v-btn>
                </v-card-text>
            </v-window-item>
        </v-window>
    </v-card>
</template>
<script setup>
    import { ref } from "vue";

    const props = defineProps({ email: Object, isLoading: Boolean });
    const emit = defineEmits(['closeDialog', 'updateSchedule', 'sendMail']);

    const date = ref(props.email.schedule ? props.email.schedule.split(" ")[0] : null);
    const time = ref(props.email.schedule ? props.email.schedule.split(" ")[1].slice(0, -3) : null);

    const initDatetime = () => {
        date.value = null;
        time.value = null;
    }

    const window = ref(0);
</script>