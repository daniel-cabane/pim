<template>
  <v-dialog v-model="dialog" width="380">
    <template v-slot:activator="{ props }">
      <v-btn variant="outlined" append-icon="mdi-account" v-bind="props">
          {{ t("Sign in") }} / {{ t("Register") }}
      </v-btn>
    </template>
    <v-card class="pa-4">
      <v-tabs v-model="tab" color="primary" align-tabs="center">
        <v-tab :value="1">Sign in</v-tab>
        <v-tab :value="2">Register</v-tab>
      </v-tabs>
      <v-window v-model="tab" class="pt-4">
        <v-window-item :value="1" :key="1">
          <v-form v-model="validSignin">
            <div>
              <v-text-field 
                :rules="[rules.required, rules.email]"
                v-model="form.email"
                label="Email"
                variant="outlined"
                validate-on="blur"
              />
              <v-text-field
                class="mt-2"
                :rules="[rules.required, rules.minLength]"
                type="password"
                v-model="form.password"
                :label="t('Password')" 
                variant="outlined"
                validate-on="blur"
              />
            </div>
            <div class="text-caption text-right text-primary mb-4" style="cursor:pointer;" @click="tab=3">
              Forgot your password ?
            </div>
          </v-form>
          <div class="d-flex pa-2">
            <v-spacer/>
            <v-btn variant="text" class="mr-2" min-width="150" color="error" @click="closeDialog">Close</v-btn>
            <v-btn color="primary" min-width="150" @click="login({email, password} = form)">Sign in</v-btn>
          </div>
        </v-window-item>
        <v-window-item :value="2" :key="2">
          <v-form v-model="validRegister">
            <div>
                <v-text-field 
                  :rules="[rules.required, rules.email]"
                  v-model="form.email"
                  label="Email"
                  variant="outlined"
                  validate-on="blur"
                />
                <v-text-field
                  class="mt-2"
                  :rules="[rules.required, rules.minLength]"
                  type="password"
                  v-model="form.password"
                  :label="t('Password')" 
                  variant="outlined"
                  validate-on="blur"
                />
                <v-text-field
                    class="mt-2"
                    :rules="[rules.matchPassword]"
                    type="password"
                    v-model="form.password_confirmation"
                    :label="t('Confirm password')"
                    variant="outlined"
                    validate-on="blur"
                  />
              </div>
            </v-form>
            <div class="d-flex pa-2">
              <v-spacer/>
              <v-btn variant="text" class="mr-2" min-width="150" color="error" @click="closeDialog">Close</v-btn>
              <v-btn color="primary" min-width="150" @click="register(form)">Register</v-btn>
            </div>
        </v-window-item>
        <v-window-item :value="3" :key="3">
          <div class="title mb-2">
            {{ t("Request password reset") }}
          </div>
          <div>
            <v-text-field 
                :rules="[rules.required, rules.email]"
                v-model="form.email"
                label="Email"
                variant="outlined"
                validate-on="blur"
              />
          </div>
          <div class="d-flex pa-2">
            <v-spacer/>
            <v-btn variant="text" class="mr-2" min-width="150" color="error" @click="tab=1">Cancel</v-btn>
            <v-btn color="primary" min-width="150" @click="resetPassword(form)">Reset password</v-btn>
          </div>
        </v-window-item>
      </v-window>
    </v-card>
  </v-dialog>
</template>
<script setup>
  import { ref, reactive } from 'vue';
  import { useAuthStore } from '@/stores/useAuthStore';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  const authStore = useAuthStore();
  const { login, register, resetPassword } = authStore;

  let dialog = ref(false);
  let tab = ref(1);

  let rules = {
    required: value => !!value || 'Required.',
    email: value => {
      const pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
      return pattern.test(value) || 'Invalid e-mail.'
    },
    minLength: value => value.length >= 8 || 'The password must at least 8 characters long',
    matchPassword: value => value == form.password || 'Does not match the password'
  };
  let validSignin = ref(false);
  let validRegister = ref(false);
  let form = reactive({email: '', password: '', password_confirmation: ''});
  const formReset = () => {
    Object.assign(form, { email: '', password: '', password_confirmation: '' });
  }

  const closeDialog = () => {
    dialog.value = false;
    tab.value = 1;
    formReset();
  }
</script>

<i18n>
  {
    "en": {
      "Sign out" : "Sign out",
      "See profile": "See profile",
      "Sign in": "Sign in",
      "Register": "Register",
      "Password": "Password",
      "Password confirmation": "Password confirmation",
      "Request password reset": "Request password reset"
    },
    "fr": {
      "Sign out": "Déconnexion",
      "See profile": "Voir profile",
      "Sign in": "Connexion",
      "Register": "Inscription",
      "Password": "Mot de passe",
      "Password confirmation": "Confirmation du mot de passe",
      "Request password reset": "Réinitialisation du mot de passe"
    }
  }
</i18n>
