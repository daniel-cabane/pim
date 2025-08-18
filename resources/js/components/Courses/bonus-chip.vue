<template>
    <v-card 
        width="250"
        hover
        class="px-5 py-1 d-flex justify-space-between align-center"
        rounded="pill"
        color="secondary"
        @click="emit('edit', bonus)"
    >
        <span style="max-width: 160px; display: block;">
            <div class="text-truncate" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">
                {{ bonus.student.name }} ({{ bonus.student.className }})
            </div>
            <div v-tooltip="bonus.description" class="text-caption text-captionColor text-truncate" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">
                {{ bonus.description }}
            </div>
        </span>
        <span class="text-h5">
            +{{ bonus.score }}
        </span>
    </v-card>
</template>
<script setup>
    const props = defineProps({ bonus: Object });
    const emit = defineEmits(['edit']);
</script>