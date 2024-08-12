<template>
    <v-list-item>
        <div class="text-caption text-captionColor">
            Language
        </div>
        <span style='display:flex;align-items:center;justify-content:center;'>
            <v-img max-width='35px' min-width='35px' class='mr-3' :style='languageSwitch ? "filter: grayscale(80%)" : ""' src="/images/flag en.png" contain/>
            <v-switch density="compact" hide-details v-model='languageSwitch' @change="setLocale">
                <template v-slot:label>
                    <v-img max-width='35px' min-width='35px' :style='languageSwitch ? "" : "filter: grayscale(80%)"' src="/images/flag fr.png" contain/>
                </template>
            </v-switch>
        </span>
    </v-list-item>
    <v-list-item>
        <div class="text-caption text-captionColor">
            Theme
        </div>
        <span style='display:flex;align-items:center;justify-content:center;'>
            <v-img max-width='30px' min-width='30px' class='mr-4' :style='themeSwitch ? "filter: grayscale(80%)" : ""' src="/images/sun.png" contain/>
            <v-switch density="compact" hide-details v-model='themeSwitch' @change="setTheme">
                <template v-slot:label>
                    <v-img max-width='30px' min-width='30px' :style='themeSwitch ? "" : "filter: brightness(10%)"' src="/images/moon.png" contain/>
                </template>
            </v-switch>
        </span>
    </v-list-item>
</template>
<script setup>
    import { ref } from 'vue';
    import { useTheme } from 'vuetify';
    import { useI18n } from 'vue-i18n';

    const { locale } = useI18n();

    let languageSwitch = ref(locale == 'fr');
    const browserLocale = localStorage.getItem('locale');
    if (browserLocale) {
        locale.value = browserLocale;
        languageSwitch.value = browserLocale == 'fr';
    }
    const setLocale = () => {
        let newLocal = languageSwitch.value ? 'fr' : 'en';
        locale.value = newLocal;
        localStorage.setItem('locale', newLocal);
    }

    const theme = useTheme();
    let themeSwitch = ref(theme.global.name.value == 'customDark');
    const setTheme = () => {
        theme.global.name.value = themeSwitch.value ? 'customDark' : 'customLight';
        localStorage.setItem('theme', themeSwitch.value ? 'customDark' : 'customLight');
    }
</script>