<template>
    <div>
        <div class="d-flex justify-space-between mt-2">
            <span style="max-width:50%">
                <div class="text-caption text-captionColor">
                    {{ $t('Teacher') }}
                </div>
                <div class="text-truncate">
                    {{ event.teacher.name }}
                </div>
            </span>
            <span style="max-width:50%">
                <div class="text-caption text-captionColor text-right">
                    {{ $t('Room nb') }}
                </div>
                <div class="text-right">
                    {{ event.roomNb }}
                </div>
            </span>
        </div>
        <div class="text-caption text-captionColor mt-2">
            {{ $t('Schedule') }}
        </div>
        <div class="d-flex justify-space-around">
            <span>
                {{ event.start }}
            </span>
            <span>
                →
            </span>
            <span>
                {{ event.end }}
            </span>
        </div>
        <div v-if="event.url" class="text-right mt-2">
            <v-btn size="small" variant="tonal" append-icon="mdi-open-in-new" :to="event.url">
                {{ $t('more info') }}
            </v-btn>
        </div>
    </div>
</template>
<script setup>
    const props = defineProps({ event: Object });
</script>