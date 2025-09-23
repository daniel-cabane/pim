<template>
    <v-dialog v-model="dialog" max-width="500">
  <template v-slot:activator="{ props: activatorProps }">
    <v-btn
        color='secondary'
        size="large"
        style='width:200px'
        :text="$t('Help!')"
        variant="outlined"
        v-bind="activatorProps"
    />
  </template>
  <template v-slot:default="{ isActive }">
    <v-card title="Help!">
      <v-card-text class="text-h6 text-captionColor d-flex justify-center py-6">
        {{ $t('Coming soon') }}...
      </v-card-text>
      <v-card-actions>
        <v-spacer/>
        <v-btn variant="tonal" color="primary" :text="$t('Close')" @click="isActive.value = false"/>
      </v-card-actions>
    </v-card>
  </template>
</v-dialog>
</template>
<script setup>
    import { ref } from 'vue';

    const props = defineProps({ dice: Array });

    const dialog = ref(false);
</script>