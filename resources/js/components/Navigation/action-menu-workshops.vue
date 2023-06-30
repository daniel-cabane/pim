<template>
    <v-list>
        <v-list-item @click="gotoMyWorkshops">
            <template v-slot:prepend>
                <v-icon icon="mdi-puzzle"></v-icon>
            </template>
            <v-list-item-title>{{ $t("My workshops") }}</v-list-item-title>
        </v-list-item>
        <v-dialog v-model="newWorkshopDialog" width="450">
            <template v-slot:activator="{ props }">
                <v-list-item v-bind="props">
                    <template v-slot:prepend>
                        <v-icon icon="mdi-puzzle-plus"></v-icon>
                    </template>
                    <v-list-item-title>{{ $t("New workshop") }}</v-list-item-title>
                </v-list-item>
            </template>
            <v-card>
                <v-card-title>{{ $t("New workshop") }}</v-card-title>
                <v-card-text>
                    <v-text-field
                        :rules="[rules.required, rules.minLengthTitle]"
                        v-model="workshop.title"
                        :label="$t('Title')" 
                        variant="outlined"
                        validate-on="blur"
                    />
                    <v-textarea 
                        :rules="[rules.required, rules.minLengthDescription]"
                        v-model="workshop.description"
                        :label="$t('Description')" 
                        variant="outlined"
                        validate-on="blur"
                    />
                </v-card-text>
                <div class="d-flex pa-2">
                    <v-spacer/>
                    <v-btn variant="text" class="mr-2" min-width="150" :disabled="newWorkshopLoading" color="error" @click="newWorkshopDialog = false">{{ $t('Close') }}</v-btn>
                    <v-btn color="primary" min-width="150" :loading="newWorkshopLoading" @click="submitNewWorkshop">{{ $t('Submit') }}</v-btn>
                </div>
            </v-card>
        </v-dialog>
    </v-list>
</template>
<script setup>
    import { ref, reactive } from 'vue';
    // import { useRouter } from 'vue-router';

    let newWorkshopDialog = ref(false);
    let newWorkshopLoading = ref(false);
    let workshop = reactive({ title: '', description: '' });

    let rules = {
        required: value => !!value || 'Required.',
        minLengthTitle: value => value.length >= 8 || 'The title must at least 8 characters long',
        minLengthDescription: value => value.length >= 20 || 'The description must at least 20 characters long',
    };

    const gotoMyWorkshops = () => {
        console.log('goto')
    }

    const submitNewWorkshop = () => {
        console.log('new');
    }
</script>