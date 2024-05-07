<template>
    <v-container>
        <v-card>
            <div class="pa-4">
                <back-btn/>
            </div>
            <workshop-display :workshop="workshop" v-if="isReady"/>
            <div class="pa-4 d-flex justify-space-between align-center">
                <back-btn/>
                <span v-if="user && user.is.teacher">
                    <v-btn color="primary" append-icon="mdi-pencil" v-if="workshop.editable" @click="editWorkshop">
                        {{ $t('Edit') }}
                    </v-btn>
                </span>
                <span v-else>
                    <v-btn color="primary" append-icon="mdi-human-greeting" v-if="user && user.is.student">
                        {{ $t('Apply') }}
                    </v-btn>
                    <v-tooltip :text="$t('You need to be a verified student to apply')" v-else>
                        <template v-slot:activator="{ props }">
                            <span v-bind="props">
                                <v-btn append-icon="mdi-human-greeting" disabled>
                                    {{ $t("I'm interested") }}
                                </v-btn>
                            </span>
                        </template>
                    </v-tooltip>
                </span>
            </div>
        </v-card>
    </v-container>
</template>
<script setup>
    import { useRoute, useRouter } from 'vue-router';
    import { useWorkshopStore } from '@/stores/useWorkshopStore';
    import { useAuthStore } from '@/stores/useAuthStore';
    import { storeToRefs } from 'pinia';
    
    const workshopStore = useWorkshopStore();
    const { getWorkshop } = workshopStore;
    const { workshop, isReady } = storeToRefs(workshopStore);
    
    const route = useRoute();
    getWorkshop(route.params.id);

    const authStore = useAuthStore();
    const { user } = authStore;

    const router = useRouter();
    const editWorkshop = () => {
        router.push(`/workshops/${route.params.id}/edit`);
    }
</script>