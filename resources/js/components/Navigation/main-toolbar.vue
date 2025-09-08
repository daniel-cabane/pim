<template>
  <v-app-bar color="grey">
    <div class="ml-4 d-flex">
      <v-app-bar-nav-icon variant="text" @click.stop="drawer = !drawer" v-if="isWindowSm"/>
      <v-img max-width='80px' min-width='80px' src="/images/pim logo.png" contain />
    </div>
    <v-spacer />
    <div>
      <v-tabs centered stacked v-model="navigationTab" exact :mandatory="false" v-if="!isWindowSm">
        <v-tab v-for="(link, index) in links" :to="link.url" :value="`tab-${index}`">
          <v-icon :icon="link.icon"/>
          {{ $t(link.name) }}
        </v-tab>
      </v-tabs>
    </div>
    <v-spacer />

    <template v-slot:append>
      <div style="white-space:nowrap;display:flex;align-items:center;" v-if="user">
        <messages-dialog :messages="user.unread_messages" class="mr-2" v-if="user.unread_messages.length"/>
        <survey-menu :surveys="user.open_surveys" class="mr-2" v-if="user.open_surveys.length"/>
        <action-menu v-if="user.is.teacher || user.is.publisher" />
        <profile-menu />
      </div>
      <div style="white-space:nowrap;display:flex;align-items:center;" v-else>
        <signin-dialog />
        <v-menu :close-on-content-click="false">
          <template v-slot:activator="{ props }">
            <v-btn icon="mdi-dots-vertical" v-bind="props"></v-btn>
          </template>
          <v-list>
            <theme-and-language-picker />
          </v-list>
        </v-menu>
      </div>
    </template>
  </v-app-bar>
  <v-navigation-drawer v-model="drawer" temporary>
      <v-list>
        <v-list-item height="75" v-for="link in links" :to="link.url">
          <template v-slot:prepend>
            <v-icon :icon="link.icon"></v-icon>
          </template>
          <v-list-item-title>{{ ucFirst($t(link.name)) }}</v-list-item-title>
        </v-list-item>
      </v-list>
  </v-navigation-drawer>
</template>
<script setup>
  import { ref, computed, watch } from 'vue';
  import { useAuthStore } from '@/stores/useAuthStore';
  import { useThemeStore } from '@/stores/useThemeStore';
  import { storeToRefs } from 'pinia';
  import { useDisplay } from 'vuetify';
  import { useRoute } from 'vue-router';

  let navigationTab = ref('tab-1');
  
  const authStore = useAuthStore();
  const { user } = storeToRefs(authStore);
  const { defineUser } = authStore;

  const themeStore = useThemeStore();
  const { getThemes } = themeStore;
  getThemes();
  
  const props = defineProps({ user: Object });
  if(props.user){
    defineUser(props.user);
  }

  const { name } = useDisplay();
  const isWindowSm = computed(() => ['xs', 'sm'].includes(name.value));

  const links = [
    { name: 'home', icon: 'mdi-home', url: '/' },
    { name: 'workshops', icon: 'mdi-puzzle', url: '/workshops' },
    { name: 'calendar', icon: 'mdi-calendar', url: '/calendar' },
    // { name: 'ressources', icon: 'mdi-folder-star-outline', url: '/ressources' },
  ];
  const ucFirst = str => str ? str.charAt(0).toUpperCase() + str.slice(1) : '';

  const route = useRoute();
  watch(() => route.name, (newVal, oldVal) => {
    switch(newVal){
      case 'Home':
        navigationTab.value = 'tab-1';
      break;
      case 'Workshops':
        navigationTab.value = 'tab-2';
      break;
      case 'Calendar':
        navigationTab.value = 'tab-3';
      break;
      case 'Ressources':
        navigationTab.value = 'tab-4';
      break;
      default:
        navigationTab.value = null;
      break;
    }
  });

  const drawer = ref(false);
</script>
