<template>
    <div>
        <div class="d-flex justify-space-between">
            <span>
                <div class="text-caption text-white-darken-2">
                    {{ $t('Teacher') }}
                </div>
                <div>
                    {{ event.teacher.name }}
                </div>
            </span>
            <span>
                <div class="text-caption text-white-darken-2">
                    {{ $t('Room nb') }}
                </div>
                <div>
                    {{ event.roomNb }}
                </div>
            </span>
        </div>
        <div class="text-caption text-white-darken-2">
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