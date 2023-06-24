<template>
    <v-app-bar color="grey">
            <v-img class="ml-4" max-width='80px' min-width='80px' src="/images/pim logo.png" contain/>
            <v-spacer/>
            <div>
                <v-tabs centered stacked>
                    <v-tab value="tab-1" to="/">
                        <v-icon>mdi-home</v-icon>
                        {{ t('home') }}
                    </v-tab>
                    <v-tab value="tab-2" to="/workshops">
                        <v-icon>mdi-shape-plus</v-icon>
                        {{ t('workshop') }}
                    </v-tab>
                    <v-tab value="tab-3" to="/calendar">
                          <v-icon>mdi-calendar</v-icon>
                          {{ t('calendar') }}
                      </v-tab>
                </v-tabs>
            </div>
            <v-spacer/>

            <template v-slot:append>
              <profile-menu v-if="user"/>
              <div v-else>
                <signin-dialog/>
                <v-menu :close-on-content-click="false">
                  <template v-slot:activator="{ props }">
                    <v-btn icon="mdi-dots-vertical" v-bind="props"></v-btn>
                  </template>
                  <v-list>
                    <theme-and-language-picker/>
                  </v-list>
                </v-menu>
              </div>
            </template>
          </v-app-bar>
</template>
<script setup>
  import { defineProps } from 'vue';
  import { useI18n } from 'vue-i18n';
  import { useAuthStore } from '@/stores/useAuthStore';
  import { storeToRefs } from 'pinia';
  import { useTheme } from 'vuetify';
  
  const { t } = useI18n();
  
  
  const authStore = useAuthStore();
  const { user } = storeToRefs(authStore);
  const { defineUser } = authStore;
  
  const props = defineProps({ user: Object });
  if(props.user){
    defineUser(props.user);
  }

  const theme = useTheme()
  theme.global.name.value = 'light';
  const browserTheme = localStorage.getItem('theme');
  if (browserTheme) {
    theme.global.name.value = browserTheme;
  } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    theme.global.name.value = 'dark';
  }
</script>
