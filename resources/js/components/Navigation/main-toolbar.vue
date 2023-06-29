<template>
    <v-app-bar color="grey">
      <div class="ml-4 d-none d-sm-flex">
        <v-img max-width='80px' min-width='80px' src="/images/pim logo.png" contain/>
      </div>
      <v-spacer/>
      <div>
          <v-tabs centered :mandatory="false" v-model="navigationTab" v-if="isWindowXs">
            <v-tab value="tab-1" to="/">
                <v-icon size="x-large">mdi-home</v-icon>
            </v-tab>
            <v-tab value="tab-2" to="/workshops">
                <v-icon size="x-large">mdi-shape-plus</v-icon>
            </v-tab>
            <v-tab value="tab-3" to="/calendar">
                  <v-icon size="x-large">mdi-calendar</v-icon>
              </v-tab>
          </v-tabs>
          <v-tabs centered stacked v-model="navigationTab" exact :mandatory="false" v-else>
            <v-tab value="tab-1" to="/">
                <v-icon>mdi-home</v-icon>
                {{ $t('home') }}
            </v-tab>
            <v-tab value="tab-2" to="/workshops">
                <v-icon>mdi-shape-plus</v-icon>
                {{ $t('workshops') }}
            </v-tab>
            <v-tab value="tab-3" to="/calendar">
                  <v-icon>mdi-calendar</v-icon>
                  {{ $t('calendar') }}
              </v-tab>
          </v-tabs>
      </div>
      <v-spacer/>

      <template v-slot:append>
        <div style="white-space:nowrap;display:flex;align-items:center;" v-if="user">
          <v-menu v-if="user.is.teacher">
            <template v-slot:activator="{ props }">
                <v-btn dark variant="elevated" icon="mdi-menu" v-bind="props"/>
              </template>
              <action-menu-home/>
          </v-menu>
          <profile-menu/>
        </div>
        <div style="white-space:nowrap;display:flex;align-items:center;" v-else>
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
  import { ref, computed, watch } from 'vue';
  import { useAuthStore } from '@/stores/useAuthStore';
  import { storeToRefs } from 'pinia';
  import { useDisplay } from 'vuetify';
  import { useRoute } from 'vue-router';

  let navigationTab = ref('tab-1');
  
  const authStore = useAuthStore();
  const { user } = storeToRefs(authStore);
  const { defineUser } = authStore;
  
  const props = defineProps({ user: Object });
  if(props.user){
    defineUser(props.user);
  }

  const { name } = useDisplay();
  const isWindowXs = computed(() => name.value == 'xs');

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
      default:
        navigationTab.value = null;
      break;
    }
  })
</script>
