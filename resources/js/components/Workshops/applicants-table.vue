<template>
    <div>
        <div class="d-flex justify-end">
            <v-btn variant="outlined" color="primary" @click="emit('confirmMaxApplicants')">
                {{ $t('Confirm max applicants') }}
            </v-btn>
        </div>
        <v-data-table :items-per-page-options="[10, 15, -1]" :headers="headers" :items="applicants" item-value="name">
            <template v-slot:item="{ item }">
                <tr>
                    <td>{{ item.name }}</td>
                    <td class="text-center">{{ item.className }}</td>
                    <td class="text-center">
                        <v-icon icon="mdi-check" color="success" v-if="item.available"/>
                        <v-icon icon="mdi-window-close" color="error" v-else/>
                    </td>
                    <td class="text-center">
                        <v-icon icon="mdi-check" color="success" v-if="item.confirmed" @click="item.confirmed = !item.confirmed"/>
                        <v-icon icon="mdi-window-close" color="error" @click="item.confirmed = !item.confirmed" v-else/>
                    </td>
                    <td>{{ item.comment }}</td>
                </tr>
            </template>
        </v-data-table>
    </div>
</template>
<script setup>
    import { useI18n } from 'vue-i18n';

    const props = defineProps({ applicants: Array });
    const emit = defineEmits(['confirmMaxApplicants'])

    const { t } = useI18n();

    const headers = [
        { title: t('Student'), align: 'start', key: 'name' },
        { title: t('Class'), align: 'center', key: 'className' },
        { title: t('Available'), align: 'center', key: 'available' },
        { title: t('Confirmed'), align: 'center', key: 'confirmed' },
        { title: t('Comment'), align: 'start', key: 'comment' },
    ];
</script>