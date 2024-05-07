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
        <v-card>
            <v-card-title class="d-flex align-center">
                <span>
                    Profile
                </span>
                <v-spacer/>
                <v-btn 
                    variant="text"
                    style="width:150px;"
                    :append-icon="tab == 1 ? 'mdi-chevron-right' : ''"
                    :prepend-icon="tab == 1 ? '' : 'mdi-chevron-left'"
                    @click="tab = tab%2 + 1"
                >
                    {{ tab == 1 ? 'Password' : 'Name'}}
                </v-btn>
            </v-card-title>
            <v-card-text>
                <v-window v-model="tab" style="min-height:154px;max-height:274px;" class="pt-4">
                    <v-window-item :value="1" :key="1">
                        <div class="text-caption text-grey">
                            {{ $t('Email') }}
                        </div>
                        <div class="mb-4">
                            {{ user.email }}
                        </div>
                        <v-text-field variant="outlined" :label="$t('Name')" v-model="user.name"/>
                    </v-window-item>
                    <v-window-item :value="2" :key="2">
                        <v-text-field 
                            variant="outlined" 
                            :label="$t('Current password')" 
                            v-model="pwd.current"
                            :rules="[rules.required, rules.minLength]"
                            validate-on="blur"
                            class="mb-3"
                        />
                        <v-text-field 
                            variant="outlined"
                            :label="$t('New password')"
                            v-model="pwd.new"
                            :rules="[rules.required, rules.minLength]"
                            validate-on="blur"
                            class="mb-3"
                        />
                        <v-text-field 
                            variant="outlined"
                            :label="$t('Confirm new password')"
                            v-model="pwd.confirm"
                            :rules="[rules.required, rules.matchPassword]"
                            validate-on="blur"
                        />
                    </v-window-item>
                </v-window>
            </v-card-text>
            <div class="d-flex pa-2">
                <v-spacer/>
                <v-btn variant="text" class="mr-2" min-width="150" color="error" @click="closeDialog">{{ $t('Close') }}</v-btn>
                <v-btn color="primary" min-width="150" @click="submit">{{ $t('Submit') }}</v-btn>
            </div>
        </v-card>
    </v-dialog>
</template>
<script setup>
    import { ref, reactive } from 'vue';
    import { useAuthStore } from '@/stores/useAuthStore';
    import { storeToRefs } from 'pinia';
    import { useI18n } from 'vue-i18n';
    const { t } = useI18n();

    const authStore = useAuthStore();
    const { editProfile } = authStore;
    const { user } = storeToRefs(authStore);

    let dialog = ref(false);
    let tab = ref(1);
    let pwd = reactive({current: '', new: '', confirm:''});

    const rules = {
    required: value => !!value || t('Required'),
    minLength: value => value.length >= 8 || t('The password must be at least 8 characters long'),
    matchPassword: value => value == pwd.new || t('Does not match the password provided')
  };

    const submit = () => {
        console.log(user.value);
    }
    const closeDialog = () => {
        dialog.value = false;
    }
</script>