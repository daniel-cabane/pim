<template>
    <div>
        <v-card-title class="d-flex justify-space-between align-center pb-0">
            <span>{{ title }}</span>
            <div class="d-flex align-center">
                <v-chip label class="mr-4" :color="statusColor" :elevated="workshop.status == 'submitted'" v-if="workshop.editable">
                    {{ workshop.status }}
                </v-chip>
                <v-img src="/images/flag fr.png" :width="30" class="mr-2"
                    v-if="['fr', 'both'].includes(workshop.language)" />
                <v-img src="/images/flag uk.png" :width="30" class="mr-2"
                    v-if="['en', 'both'].includes(workshop.language) " />
                <v-chip size="small" :color="workshop.details.campus == 'BPR' ? 'blue' : 'red'">
                    {{ workshop.details.campus}}
                </v-chip>
            </div>
        </v-card-title>
    </div>
    <v-card-subtitle class="font-italic">
        {{ $t('By') }} {{ workshop.teacher }}
    </v-card-subtitle>
</template>
<script setup>
    import { computed } from "vue";

    const props = defineProps({ title: String, workshop: Object });

    const statusColor = computed(() => {
        const colors = {draft: 'secondary', submitted: 'warning', confirmed: 'success'}
        return colors[props.workshop.status]
    });
</script>