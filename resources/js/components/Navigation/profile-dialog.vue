<template>
    <v-dialog v-model="dialog" width="380">
        <template v-slot:activator="{ props }">
            <v-list-item v-bind="props">
                <template v-slot:prepend>
                    <v-icon icon="mdi-account"></v-icon>
                </template>
                <v-list-item-title>{{ $t("See profile") }}</v-list-item-title>
            </v-list-item>
        </template>
        <v-card title="Profile">
            <v-card-text>
                <div class="text-caption text-grey">
                    {{ $t('Email') }}
                </div>
                <div class="mb-4">
                    {{ user.email }}
                </div>
                <div v-if="user.is.teacher">
                    <v-text-field variant="outlined" :label="$t('Name')" v-model="user.name" />
                    <v-text-field variant="outlined" :label="$t('Title')" placeholder="M., Mr, Mme, Ms..."
                        v-model="user.preferences.title" />
                </div>
                <div v-else>
                    <div class="text-caption text-captionColor">{{ $t('Name') }}</div>
                    <div class="text-subtitle-1 mb-4">{{ user.name }}</div>
                </div>
                <div class="d-flex">
                    <div style="flex:1">
                        <div class="text-caption text-captionColor">{{ $t('Language') }}</div>
                        <v-radio-group v-model="user.preferences.language">
                            <v-radio label="Français" value="fr"></v-radio>
                            <v-radio label="English" value="en"></v-radio>
                            <v-radio :label="$t('Both') " value="both"></v-radio>
                        </v-radio-group>
                    </div>
                    <div style="flex:1">
                        <div class="text-caption text-captionColor">Campus</div>
                        <v-radio-group v-model="user.preferences.campus">
                            <v-radio label="BPR" value="BPR"></v-radio>
                            <v-radio label="TKO" value="TKO"></v-radio>
                            <v-radio :label="$t('Both')" value="both"></v-radio>
                        </v-radio-group>
                    </div>
                </div>
            </v-card-text>
            <div class="d-flex pa-2">
                <v-spacer />
                <v-btn variant="text" class="mr-2" min-width="150" color="error" @click="dialog=false">
                    {{ $t('Close') }}
                </v-btn>
                <v-btn color="primary" min-width="150" @click="handleupdateDetails">
                    {{ $t('Submit') }}
                </v-btn>
            </div>
        </v-card>
    </v-dialog>
</template>
<script setup>
    import { ref } from 'vue';
    import { useAuthStore } from '@/stores/useAuthStore';

    const { updateDetails } = useAuthStore();

    const props = defineProps({ user: Object });

    const dialog = ref(false);

    const handleupdateDetails = async () => {
        let details = {};
        if(props.user.is.teacher){
            details = {
                title: props.user.preferences.title,
                name: props.user.name,
                language: props.user.preferences.language,
                campus: props.user.preferences.campus
            }
        } else {
            details = {
                language: props.user.preferences.language,
                campus: props.user.preferences.campus
            }
        }
        await updateDetails(details);
        dialog.value = false;
    }
</script>