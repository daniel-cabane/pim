<template>
    <div class="text-center pa-2">
        <div class="d-flex align-center">
            <v-img :src="`/images/flag ${workshop.language}.png`" :width="30" class="mr-2" />
            <v-chip variant="elevated" size="small" :color="workshop.campus == 'BPR' ? 'blue' : 'red'">
                {{ workshop.campus }}
            </v-chip>
        </div>
        <v-chip label :variant="workshop.status == 'draft' ? 'tonal' : 'elevated'" class="mt-1"
            :color="statusDetails[workshop.status].color" :text="$t(statusDetails[workshop.status].text)"
            v-if="workshop.editable || ['progress', 'finished'].includes(workshop.status)" />
    </div>
</template>
<script setup>
    defineProps({ workshop: Object });

    const statusDetails = {
        draft: { color: 'secondary', text: 'Draft' },
        submitted: { color: 'warning', text: 'Submitted' },
        confirmed: { color: 'primary', text: 'Confirmed' },
        launched: { color: 'success', text: 'Confirmed' },
        progress: { color: 'success', text: 'In progress' },
        finished: { color: 'grey', text: 'Finished' }
    }

</script>