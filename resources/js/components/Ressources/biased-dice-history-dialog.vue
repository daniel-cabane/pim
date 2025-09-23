<template>
  <v-dialog v-model="showDialog" width="650">
      <template v-slot:activator="{ props: activatorProps }">
        <v-btn
            color='secondary'
            size="large"
            style='width:200px'
            variant="outlined"
            text="Show history"
            v-bind="activatorProps"
        />
      </template>
      <v-card title="Guess history">
        <v-card-text>
            <v-data-table :headers="headers" :items="history" :items-per-page="25"/>
        </v-card-text>
      </v-card>
    </v-dialog>
</template>
<script setup>
    import { ref, computed } from 'vue';
    import { useI18n } from 'vue-i18n';

    const { t } = useI18n();

    const props = defineProps({ history: Array });

    const showDialog = ref(false);

    const headers = computed(() => [
        { title: t('Correct guesses'), value: 'correct', align: 'center', cellClass:'font-weight-bold text-h5' },
        { title: t('Nb of dice'), value: 'outOf' },
        { title: t('Nb of rolls'), value: 'after' },
        { title: t('Bias'), value: 'bias' },
    ]);
</script>