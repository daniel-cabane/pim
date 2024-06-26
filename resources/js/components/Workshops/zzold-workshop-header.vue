<template>
    <div style="display:flex;flex-wrap:nowrap;max-width:100%">
        <div style="flex:1;max-width: calc(100%-100px);">
            <v-card-title class="pb-0" style="text-overflow:ellipsis;">
                {{ title }}
            </v-card-title>
            <v-card-subtitle class="font-italic">
                {{ $t('By') }} {{ workshop.teacher }}
            </v-card-subtitle>
        </div>
        <div class="text-right pa-2">
            <div class="d-flex align-center">
                <v-img src="/images/flag fr.png" :width="30" class="mr-2"
                    v-if="['fr', 'both'].includes(workshop.language)" />
                <v-img src="/images/flag en.png" :width="30" class="mr-2"
                    v-if="['en', 'both'].includes(workshop.language)" />
                <v-chip variant="elevated" theme="dark" size="small"
                    :color="workshop.details.campus == 'BPR' ? 'blue' : 'red'">
                    {{ workshop.details.campus }}
                </v-chip>
            </div>
            <v-chip label :variant="workshop.status == 'draft' ? 'tonal' : 'elevated'" theme="dark" class="mt-1"
                :color="statusColor" :text="$t(workshop.status)" v-if="workshop.editable" />
        </div>
    </div>
</template>
<script setup>
    import { computed } from "vue";

    const props = defineProps({ title: String, workshop: Object });

    const statusColor = computed(() => {
        const colors = {draft: 'secondary', submitted: 'warning', confirmed: 'success'}
        return colors[props.workshop.status]
    });
</script>