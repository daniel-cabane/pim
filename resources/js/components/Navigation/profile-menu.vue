<template>
    <v-menu :close-on-content-click="false">
        <template v-slot:activator="{ props }">
        <v-btn append-icon="mdi-menu-down" v-bind="props">
            {{ user.name }}
        </v-btn>
        </template>
        <v-list>
            <v-list-item @click="seeProfile">
                <template v-slot:prepend>
                <v-icon icon="mdi-account"></v-icon>
                </template>
                <v-list-item-title>{{ t("See profile") }}</v-list-item-title>
            </v-list-item>
            <v-divider/>
            <theme-and-language-picker/>
            <!-- <v-list-item>
                <div class="text-caption text-grey">
                    Language
                </div>
                <span style='display:flex;align-items:center;justify-content:center;'>
                    <v-img max-width='35px' min-width='35px' class='mr-3' :style='languageSwitch ? "filter: grayscale(80%)" : ""' src="/images/flag uk.png" contain/>
                    <v-switch :color="languageSwitch ? 'blue' : 'red'" density="compact" hide-details v-model='languageSwitch' @change="setLocale">
                        <template v-slot:label>
                            <v-img max-width='35px' min-width='35px' :style='languageSwitch ? "" : "filter: grayscale(80%)"' src="/images/flag fr.png" contain/>
                        </template>
                    </v-switch>
                </span>
            </v-list-item>
            <v-list-item>
                <div class="text-caption text-grey">
                    Theme
                </div>
                <span style='display:flex;align-items:center;justify-content:center;'>
                    <v-img max-width='30px' min-width='30px' class='mr-4' :style='themeSwitch ? "filter: grayscale(80%)" : ""' src="/images/sun.png" contain/>
                    <v-switch :color="themeSwitch ? 'blue' : 'red'" density="compact" hide-details v-model='themeSwitch' @change="setTheme">
                        <template v-slot:label>
                            <v-img max-width='30px' min-width='30px' :style='themeSwitch ? "" : "filter: brightness(10%)"' src="/images/moon.png" contain/>
                        </template>
                    </v-switch>
                </span>
            </v-list-item> -->
            <v-divider/>
            <v-list-item @click="logout">
                <template v-slot:prepend>
                    <v-icon icon="mdi-logout"></v-icon>
                </template>
                <v-list-item-title>{{ t("Sign out") }}</v-list-item-title>
            </v-list-item>
        </v-list>
    </v-menu>
</template>
<script setup>
    import { ref } from 'vue';
    import { useAuthStore } from '@/stores/useAuthStore';
    import { storeToRefs } from 'pinia';
    import { useI18n } from 'vue-i18n';
    // import { useTheme } from 'vuetify';

    const { t } = useI18n();

    const authStore = useAuthStore();
    const { logout } = authStore;
    const { user } = storeToRefs(authStore);

    const seeProfile = () => {
        console.log('see profile');
    }

    // const { locale } = useI18n();
    // let languageSwitch = ref(false);
    // const browserLocale = localStorage.getItem('locale');
    // if(browserLocale){
    //     locale.value = browserLocale;
    //     languageSwitch.value = browserLocale == 'fr';
    // }
    // const setLocale = () => {
    //     let newLocal = languageSwitch.value ? 'fr' : 'en';
    //     locale.value = newLocal;
    //     localStorage.setItem('locale', newLocal);
    // }

    // let themeSwitch = ref(theme.global.name.value == 'dark');
    // const theme = useTheme();
    // const setTheme = () => {
    //     theme.global.name.value = themeSwitch.value ? 'dark' : 'light';
    //     localStorage.setItem('theme', themeSwitch.value ? 'dark' : 'light');
    // }
</script>

<i18n>
  {
    "en": {
      "Sign out" : "Sign out",
      "See profile": "See profile"
    },
    "fr": {
      "Sign out": "Déconnexion",
      "See profile": "Voir profile"
    }
  }
</i18n>