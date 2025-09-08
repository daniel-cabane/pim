<template>
  <v-dialog v-model="dialog" width="380" @keydown.enter="submit">
    <template v-slot:activator="{ props }">
      <v-btn variant="outlined" icon="mdi-account" v-bind="props" v-if="isWindowSmall" />
      <v-btn variant="outlined" append-icon="mdi-account" v-bind="props" v-else>
        {{ $t("Sign in") }}
      </v-btn>
    </template>
    <v-card class="pa-4">
      <google-button/>
      <div class="py-3 text-caption text-captionColor text-center">
        <span v-if="locale == 'en'">
          @g.lfis.edu.hk accounts only
        </span>
        <span v-else>
          Comptes @g.lfis.edu.hk uniquement
        </span>
      </div>
      <div v-if="localEnv">
        <v-tabs v-model="tab" color="primary" align-tabs="center">
          <v-tab :value="1">Sign in</v-tab>
          <v-tab :value="2">Register</v-tab>
        </v-tabs>
        <v-window v-model="tab" style="min-height:318px;max-height:318px" class="pt-4">
          <v-window-item :value="1" :key="1">
            <v-form v-model="validSignin">
              <div>
                <v-text-field :rules="[rules.required, rules.email]" v-model="form.email" label="Email" variant="outlined"
                  validate-on="blur" />
                <v-text-field class="mt-2" :rules="[rules.required]" type="password" v-model="form.password"
                  :label="$t('Password')" variant="outlined" validate-on="blur" />
                <v-checkbox :label="$t('Remember me')" density="compact" hide-details v-model="form.remember" />
              </div>
              <div class="text-caption text-right text-primary mb-4" style="cursor:pointer;" @click="tab=3">
                Forgot your password ?
              </div>
            </v-form>
            <div class="d-flex pa-2">
              <v-spacer />
              <v-btn variant="text" class="mr-2" min-width="150" :disabled="AuthLoading" color="error"
                @click="closeDialog">Close</v-btn>
              <v-btn color="primary" min-width="150" :loading="AuthLoading" @click="login({email, password} = form)">Sign
                in</v-btn>
            </div>
          </v-window-item>
          <v-window-item :value="2" :key="2">
            <v-form v-model="validRegister">
              <div>
                <v-text-field :rules="[rules.required, rules.email]" v-model="form.email" label="Email" variant="outlined"
                  validate-on="blur" />
                <v-text-field class="mt-2" :rules="[rules.required, rules.minLength]" type="password"
                  v-model="form.password" :label="$t('Password')" variant="outlined" validate-on="blur" />
                <v-text-field class="mt-2" :rules="[rules.matchPassword]" type="password"
                  v-model="form.password_confirmation" :label="$t('Confirm password')" variant="outlined"
                  validate-on="blur" />
              </div>
            </v-form>
            <div class="d-flex pa-2">
              <v-spacer />
              <v-btn variant="text" class="mr-2" min-width="150" :disabled="AuthLoading" color="error"
                @click="closeDialog">Close</v-btn>
              <v-btn color="primary" min-width="150" :loading="AuthLoading" @click="register(form)">Register</v-btn>
            </div>
          </v-window-item>
          <v-window-item :value="3" :key="3">
            <div class="title mb-2">
              {{ $t("Request password reset") }}
            </div>
            <div>
              <v-text-field :rules="[rules.required, rules.email]" v-model="form.email" label="Email" variant="outlined"
                validate-on="blur" />
            </div>
            <div class="d-flex pa-2">
              <v-spacer />
              <v-btn variant="text" class="mr-2" min-width="150" :disabled="AuthLoading" color="error"
                @click="tab=1">Cancel</v-btn>
              <v-btn color="primary" min-width="150" :loading="AuthLoading" @click="resetPassword(form)">Reset
                password</v-btn>
            </div>
          </v-window-item>
        </v-window>
      </div>
    </v-card>
  </v-dialog>
</template>
<script setup>
  import { ref, reactive, computed } from 'vue';
  import { useAuthStore } from '@/stores/useAuthStore';
  import { storeToRefs } from 'pinia';
  import { useDisplay } from 'vuetify';
  import { useI18n } from 'vue-i18n';
  const { t, locale } = useI18n();

  const authStore = useAuthStore();
  const { login, register, resetPassword } = authStore;
  const { loading: AuthLoading } = storeToRefs(authStore);

  let dialog = ref(false);
  let tab = ref(1);

  const rules = {
    required: value => !!value || t('Required'),
    email: value => {
      const pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
      return pattern.test(value) || t('Invalid e-mail')
    },
    minLength: value => value.length >= 8 || t('The password must be at least 8 characters long'),
    matchPassword: value => value == form.password || t('Does not match the password provided'
)  };
  let validSignin = ref(false);
  let validRegister = ref(false);
  let form = reactive({email: '', password: '', password_confirmation: '', remember: 0});
  const formReset = () => {
    Object.assign(form, {email: '', password: '', password_confirmation: '', remember: 0});
  }

  const submit = () => {
    if(tab.value == 1){
      login(form)
    } else if (tab.value == 2) {
      register(form)
    }
  }

  const closeDialog = () => {
    dialog.value = false;
    tab.value = 1;
    formReset();
  }

  const { name } = useDisplay();
  const isWindowSmall = computed(() => name.value == 'xs' || name.value == 'sm');

  const localEnv = computed(() => window.Laravel.env == 'local');
</script>