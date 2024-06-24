/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';

// Vuetify
import '@mdi/font/css/materialdesignicons.css';
import 'vuetify/styles';
import { createVuetify, useTheme } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';

const customLight = {
    dark: false,
    colors: {
        background: '#FAFAFA',
        surface: '#F3F3F3',
        primary: '#003d7d',
        'primary-darken-1': '#012953',
        secondary: '#919195',
        'secondary-darken-1': '#646469',
        error: '#e31b23',
        info: '#007dc3',
        success: '#acc37e',
        warning: '#fbaf5c',
        captionColor: '#9E9E9E'
    }
}
const customDark = {
    dark: true,
    colors: {
        background: '#18191A',
        surface: '#797b7c',
        primary: '#003d7d',
        'primary-darken-1': '#012953',
        secondary: '#919195',
        'secondary-darken-1': '#646469',
        error: '#e31b23',
        info: '#007dc3',
        success: '#acc37e',
        warning: '#fbaf5c',
        captionColor: '#CDCECE'
    }
}

let defaultTheme = 'customLight';
const browserTheme = localStorage.getItem('theme');
if (browserTheme) {
    defaultTheme = browserTheme;
} else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    defaultTheme = 'customDark';
}

const vuetify = createVuetify({
    components,
    directives,
    theme: {
        defaultTheme,
        themes: {
            customLight, customDark
        },
    },
});


// i18n
import { createI18n } from 'vue-i18n';
/*
 * The i18n resources in the path specified in the plugin `include` option can be read
 * as vue-i18n optimized locale messages using the import syntax
 */
import en from '../locales/en.json';
import fr from '../locales/fr.json';


const i18n = createI18n({
    locale: localStorage.getItem('locale') ? localStorage.getItem('locale') : 'en',
    messages: { en, fr }
});


import router from "./router";


/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.
 */

const app = createApp({});

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

Object.entries(import.meta.glob('./**/*.vue', { eager: true })).forEach(([path, definition]) => {
    app.component(path.split('/').pop().replace(/\.\w+$/, ''), definition.default);
});

/**
 * Finally, we will attach the application instance to a HTML element with
 * an "id" attribute of "app". This element is included with the "auth"
 * scaffolding. Otherwise, you will need to add an element yourself.
 */

app.use(vuetify).use(router).use(i18n).use(createPinia()).mount('#app');