<template>
    <div class="px-2 py-8">
        <v-dialog v-model="dialog" max-width="500">
            <template v-slot:activator="{ props: activatorProps }">
                <v-btn block :text="$t('Initialise new year')" color="error" v-bind="activatorProps"/>
            </template>
            <template v-slot:default="{ isActive }">
                <v-card :title="$t('Initialise new year')">
                <v-card-text>
                    <v-checkbox hide-details color="error" :label="$t('Archive all workshops')" v-model="checkWorkshop"/>
                    <v-checkbox hide-details color="error" :label="$t('Delete all open door sessions')" v-model="checkOpenDoors"/>
                    <v-checkbox hide-details color="error" :label="$t('Remove class from all students')" v-model="checkStudents"/>
                    <v-text-field type="password" variant="outlined" v-model="password" :label="$t('Password')"/>
                </v-card-text>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn variant="tonal" color="primary" :text="$t('Close')" @click="isActive.value = false"/>
                    <v-btn 
                        variant="flat"
                        color="error"
                        :text="$t('Submit')"
                        :disabled="!(checkWorkshop && checkOpenDoors && checkStudents)" 
                        :loading="isLoading"
                        @click="initNewYear"
                    />
                </v-card-actions>
                </v-card>
            </template>
        </v-dialog>
    </div>
</template>
<script setup>
    import { ref } from "vue";
    import useAPI from '@/composables/useAPI';

    const { post, isLoading } = useAPI();

    const dialog = ref(false);
    const checkWorkshop = ref(false);
    const checkOpenDoors = ref(false);
    const checkStudents = ref(false);
    const password = ref('');

    const initNewYear = async () => {
        const res = await post('/api/admin/newYear', { password: password.value });
        console.log(res);
        if(res.success){
            dialog.value = false;
            checkWorkshop.value = false;
            checkOpenDoors.value = false;
            checkStudents.value = false;
            password.value = '';
        }
    }
</script>