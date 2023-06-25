<template>
    <v-dialog v-model="dialog" width="380">
        <template v-slot:activator="{ props }">
            <v-list-item v-bind="props">
                <template v-slot:prepend>
                    <v-icon icon="mdi-account"></v-icon>
                </template>
                <v-list-item-title>{{ $t("See profile") }}</v-list-item-title>
            </v-list-item>
        </template>
        <v-card>
            <v-card-title>
                Profile
            </v-card-title>
            <v-card-text>
                Lorem ipsum dolor sit, amet consectetur adipisicing elit. Impedit, eveniet.
            </v-card-text>
            <div class="d-flex pa-2">
                <v-spacer/>
                <v-btn variant="text" class="mr-2" min-width="150" color="error" @click="closeDialog">{{ $t('Close') }}</v-btn>
                <v-btn color="primary" min-width="150" @click="submit">{{ $t('Submit') }}</v-btn>
            </div>
        </v-card>
    </v-dialog>
</template>
<script setup>
    import { ref } from 'vue';

    let dialog = ref(false);

    const submit = () => {
        console.log('submit');
    }
    const closeDialog = () => {
        dialog.value = false;
    }
</script>