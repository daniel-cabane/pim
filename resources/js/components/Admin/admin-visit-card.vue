<template>
    <v-card class="pa-3" width="300" :height="matches.length ? 375 : 240">
        <div class="text-center text-captionColor mb-4">
            Visit to review
        </div>
        <div>
            <v-text-field density="compact" hide-details class="mb-3" :label="$t('Tag number')" variant="outlined" v-model="tagNb"/>
            <v-text-field density="compact" hide-details class="mb-3" label="Email" variant="outlined" v-model="email"/>
            <v-text-field density="compact" hide-details class="mb-3" :label="$t('Name')" variant="outlined" v-model="name"/>
        </div>
        <div class="d-flex justify-end" v-if="matches.length == 0">
            <v-btn size="x-small" color="primary" :disabled="isLoading" :text="$t('Find match')" @click="handleFindMatch({email, name})"/>
        </div>
        <div v-else>
            <v-card 
                class="mb-2"
                v-for="match in matches"
                variant="elevated"
                :title="match.name"
                :subtitle="match.email"
                color="primary"
            >
            <div class="d-flex px-3 pb-3">
                <v-icon icon="mdi-cancel" color="error" @click="removeMatch(match.id)"/>
                <v-spacer/>
                <v-icon icon="mdi-check" color="success" @click="confirmMatch(visit.id, match.id)"/>
            </div>
            </v-card>
        </div>
    </v-card>
</template>
<script setup>
    import { ref } from 'vue';
    import { useOpenDoorStore } from '@/stores/useOpenDoorStore';
    import { storeToRefs } from 'pinia';

    const openDoorStore = useOpenDoorStore();
    const { findMatch, confirmMatch } = openDoorStore;
    const { isLoading } = storeToRefs(openDoorStore);

    const props = defineProps({ visit: Object });

    const tagNb = ref(props.visit.tagNb);
    const email = ref(props.visit.data.email);
    const name = ref(props.visit.data.name);

    const matches = ref([]);
    const handleFindMatch = async data => {
        matches.value = await findMatch(data);
    }
    const removeMatch = id => {
        matches.value = matches.value.filter(m => m.id !=id);
    }
</script>