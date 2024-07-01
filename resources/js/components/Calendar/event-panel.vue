<template>
    <v-expansion-panel :bg-color="event.color">
        <v-expansion-panel-title>
            <div class="d-flex justify-space-between" style="width:100%;">
                <span>
                    {{ event.start }} : {{ event.title }}
                </span>
                <span class="mr-2">
                    {{ event.campus }}
                </span>
            </div>
        </v-expansion-panel-title>
        <v-expansion-panel-text>
            <event-details :event="event"/>
        </v-expansion-panel-text>
    </v-expansion-panel>
</template>
<script setup>
    const props = defineProps({ event: Object });
</script>