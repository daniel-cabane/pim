<template>
    <v-dialog max-width="500" v-model="dialog">
        <template v-slot:activator="{ props: activatorProps }">
            <v-btn
                v-bind="activatorProps"
                icon="mdi-format-list-bulleted-square"
                color="primary"
            />
        </template>
        <template v-slot:default="{ isActive }">
            <v-card :title="$t('Series')">
            <v-card-text>
                <v-window v-model="window">
                    <v-window-item :key="0">
                        <v-table>
                            <tbody>
                                <tr v-for="item in series">
                                <td>{{ item.title }}</td>
                                <td>
                                    <div 
                                        style="width:20px;height:20px;border-radius:50%;" 
                                        :style="`background:${item.options.color}`"
                                    />
                                    </td>
                                <td>{{ item.posts.length }} posts</td>
                                <td><v-icon icon="mdi-pencil" color="primary" @click="editSerie(item)"/></td>
                                </tr>
                            </tbody>
                        </v-table>
                        <div class="mt-3">
                            <v-btn block density="compact" color="primary" :text="$t('New series')" @click="newSeries"/>
                        </div>
                    </v-window-item>
                    <v-window-item :key="1">
                        <div class="d-flex justify-space-between mb-4">
                            <v-btn density="compact" :text="$t('Back')" variant="tonal" prepend-icon="mdi-chevron-left" @click="window=0"/>
                            <v-btn density="compact" color="primary" :text="$t('Save')" append-icon="mdi-floppy" @click="save"/>
                        </div>
                        <v-text-field 
                            :rules="[rules.required, rules.minLengthTitle]" 
                            max="50" 
                            counter="50"
                            v-model="focusedSerie.title"
                            :label="$t('Title')" 
                            variant="outlined"
                            validate-on="blur"
                        />
                        <div class="text-caption text-captionColor">
                            {{ $t('Color') }}
                        </div>
                        <v-color-picker landscape width="100%" v-model="focusedSerie.color"/>
                    </v-window-item>
                </v-window>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>

                <v-btn
                    :text="$t('Close')"
                    variant="tonal"
                    color="primary"
                    @click="window=0;isActive.value = false"
                />
            </v-card-actions>
            </v-card>
        </template>
    </v-dialog>
</template>
<script setup>
    import { ref } from 'vue';
    import { useThemeStore } from '@/stores/useThemeStore';
    import { storeToRefs } from 'pinia';

    const themeStore = useThemeStore();
    const { createSerie, updateSerie } = themeStore;
    const { isReady } = storeToRefs(themeStore);

    const props = defineProps({ series: Array });

    const dialog = ref(false);
    const window = ref(0);

    const rules = {
        required: value => !!value || 'Required.',
        minLengthTitle: value => value.length >= 5 || 'The title must at least 5 characters long'
    };

    const title = ref('');
    const color = ref(getRandomColor());

    function getRandomColor() {
        return '#' + Math.floor(Math.random() * 16777215).toString(16);
    }

    const focusedSerie = ref({id: null, title: '', color: getRandomColor()});
    const editSerie = serie => {
        focusedSerie.value = { id: serie.id, title: serie.title, color: serie.options.color };
        window.value = 1;
    }

    const newSeries = () => {
        focusedSerie.value = { id: null, title: '', color: getRandomColor() };
        window.value = 1;
    }

    const save = async() => {
        if(focusedSerie.value.id){
            await updateSerie(focusedSerie.value);
        } else {
            await createSerie(focusedSerie.value);
        }
        console.log(props.series);
        window.value = 0;
    }
</script>