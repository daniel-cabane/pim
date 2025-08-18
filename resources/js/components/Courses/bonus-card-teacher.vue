<template>
    <v-card color="surface" flat class="pa-4">
        <div class="d-flex justify-end mb-4">
            <v-dialog width="450" v-model="addBonusDialog">
                <template v-slot:activator="{ props: activatorProps }">
                    <v-btn :text="$t('Add bonus')" color="primary" rounded="pill" v-bind="activatorProps" @click="initData"/>
                </template>
                <template v-slot:default="{ isActive }">
                    <v-card :title="$t('Add bonus')">
                        <v-card-text>
                            <v-autocomplete 
                                variant="outlined" 
                                :label="$t('Student')" 
                                v-model="studentId"
                                :items="course.students"
                                item-title="name"
                                item-value="id"
                                auto-select-first
                            />
                            <v-text-field variant="outlined" :label="$t('Description')" v-model="description"/>
                            <v-text-field variant="outlined" label="Score" v-model="score" type="number" min="0" max="100"/>
                        </v-card-text>

                        <v-card-actions>
                            <v-spacer/>
                            <v-btn variant="tonal" color="error" :text="$t('Cancel')" :disabled="isLoading" @click="isActive.value = false"/>
                            <v-btn 
                                variant="flat"
                                color="primary"
                                :text="$t('Submit')"
                                @click="handleAddBonus"
                                :loading="isLoading"
                            />
                        </v-card-actions>
                    </v-card>
                </template>
            </v-dialog>
        </div>
        <div class="d-flex ga-2 flex-wrap">
            <bonus-chip v-for="bonus in course.bonuses" :bonus="bonus" @edit="showEditBonusDialog"/>
            <v-dialog width="450" v-model="editBonusDialog">
                <v-card :title="`${focusedBonus.student.name} (${focusedBonus.student.className})`">
                    <v-window v-model="editDeleteWindow">
                        <v-window-item value="edit">
                            <v-card-text>
                                <v-text-field variant="outlined" :label="$t('Description')" v-model="description"/>
                                <v-text-field variant="outlined" label="Score" v-model="score" type="number" min="0" max="100"/>
                            </v-card-text>
                            <v-card-actions>
                                <v-btn color="error" icon="mdi-delete" @click="editDeleteWindow='delete'"/>
                                <v-spacer/>
                                <v-btn variant="tonal" color="error" :text="$t('Cancel')" :disabled="isLoading" @click="editBonusDialog=false"/>
                                    <v-btn 
                                        variant="flat"
                                        color="primary"
                                        :text="$t('Submit')"
                                        @click="handleEditBonus"
                                        :loading="isLoading"
                                    />
                            </v-card-actions>
                        </v-window-item>
                        <v-window-item value="delete">
                            <v-card-text>
                                <div class="text-h6 px-2">
                                    {{ $t('Are you sure ?') }}
                                </div>
                                <v-checkbox color="error" v-model="checkDelete" :label="$t('Yes I want to delete this bonus')"/>
                            </v-card-text>
                            <v-card-actions>
                                <v-btn variant="tonal" color="primary" :disabled="isLoading" prepend-icon="mdi-chevron-left" :text="$t('Cancel')" @click="editDeleteWindow='edit'"/>
                                <v-spacer/>
                                <v-btn variant="flat" color="error" :disbaled="!checkDelete" :disabled="!checkDelete" :loading="isLoading" :text="$t('Confirm')" @click="handleDeleteBonus"/>
                            </v-card-actions>
                        </v-window-item>
                    </v-window>
                </v-card>
            </v-dialog>
        </div>
    </v-card>
</template>
<script setup>
    import { ref } from 'vue';
    import { useCourseStore } from '@/stores/useCourseStore';
    import { storeToRefs } from 'pinia';

    const courseStore = useCourseStore();
    const { addBonus, editBonus, deleteBonus } = courseStore;
    const { isLoading } = storeToRefs(courseStore);

    const addBonusDialog = ref(false);
    const studentId = ref(null);
    const description = ref('');
    const score = ref(null);
    const initData = () => {
        description.value = '';
        score.value = null;
        studentId.value = null;
    }

    const props = defineProps({ course: Object });
    
    const handleAddBonus = async () => {
        const res = await addBonus({studentId: studentId.value, description: description.value, score: score.value});
        if(res){
            addBonusDialog.value = false;
            studentId.value = null;
            description.value = '';
            score.value = null;
        }
    }

    const editBonusDialog = ref(false);
    const editDeleteWindow = ref('edit');
    const checkDelete = ref(false);
    const focusedBonus = ref(null);
    const showEditBonusDialog = bonus => {
        checkDelete.value = false;
        focusedBonus.value = bonus;
        description.value = bonus.description;
        score.value = bonus.score;
        editDeleteWindow.value = 'edit';
        editBonusDialog.value = true;
    }
    const handleEditBonus = async () => {
        const res = await editBonus({id: focusedBonus.value.id, description: description.value, score: score.value});
        if(res){
            editBonusDialog.value = false;
            description.value = '';
            score.value = null;
        }
    }
    const handleDeleteBonus = async () => {
        const res = await deleteBonus(focusedBonus.value.id);
        if(res){
            editBonusDialog.value = false;
            description.value = '';
            score.value = null;
        }
    }
</script>