<template>
    <v-dialog scrollable width="850">
  <template v-slot:activator="{ props: activatorProps }">
    <v-btn icon="mdi-message" v-bind="activatorProps"/>
  </template>
  <template v-slot:default="{ isActive }">
    <v-card title="Messages">
      <v-card-text>
        <admin-message-card class="ma-2" v-for="message in messages" :message="message"/>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn :text="$t('Close')" @click="isActive.value = false"/>
      </v-card-actions>
    </v-card>
  </template>
</v-dialog>
</template>
<script setup>
    const props = defineProps({ messages: Array });
</script>