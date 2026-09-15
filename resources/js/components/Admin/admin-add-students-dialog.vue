<template>
    <v-dialog max-width="850" v-model="mainDialog">
        <template v-slot:activator="{ props: activatorProps }">
            <v-btn color="primary" size="large" icon="mdi-account-multiple-plus" v-bind="activatorProps"/>
        </template>
        <template v-slot:default="{ isActive }">
            <v-card :title="$t('Add students')">
            <v-card-text>
                <div class="d-flex ga-3">
                    <v-select :label="$t('Class level')" variant="outlined" :items="levels" v-model="classLevel" />
                    <v-select :label="$t('Class name')" variant="outlined" :items="['A', 'B', 'C', 'D', 'E', 'F']" v-model="className"/>
                    <v-select :label="$t('Campus')" variant="outlined" :items="['BPR', 'TKO']" v-model="campus" />
                </div>
                <div class="d-flex ga-2 align-center">
                    <v-btn 
                        color="primary"
                        style="flex: 1;"
                        append-icon="mdi-content-paste"
                        :text="$t('Paste from clipboard')" 
                        @click="pasteStudents"
                    />
                    <v-btn icon="mdi-help" color="primary" size="small" variant="tonal" @click="showHelp = !showHelp"/>
                </div>
                <div v-if="showHelp" class="w-100 mt-2">
                    <div class="text-body-2 text-medium-emphasis mb-2">
                        {{ $t('Copy four columns from a spreadsheet, with no header row') }}
                    </div>
                    <v-table density="compact">
                        <thead>
                            <tr>
                                <th></th>
                                <th class="text-center">C<br><span class="text-medium-emphasis font-weight-regular">{{ $t('Last name') }}</span></th>
                                <th class="text-center">B<br><span class="text-medium-emphasis font-weight-regular">{{ $t('First name') }}</span></th>
                                <th class="text-center">D<br><span class="text-medium-emphasis font-weight-regular">{{ $t('Email') }}</span></th>
                                <th class="text-center">A<br><span class="text-medium-emphasis font-weight-regular">{{ $t('Tag Number') }}</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-medium-emphasis text-center">1</td>
                                <td class="text-center">Dupont</td>
                                <td class="text-center">Marie</td>
                                <td class="text-center">marie15389@g.lfis.edu.hk</td>
                                <td class="text-center">12345</td>
                            </tr>
                            <tr>
                                <td class="text-medium-emphasis text-center">2</td>
                                <td class="text-center">Martin</td>
                                <td class="text-center">Jean</td>
                                <td class="text-center">jean28734@g.lfis.edu.hk</td>
                                <td class="text-center">67890</td>
                            </tr>
                        </tbody>
                    </v-table>
                </div>
                <div>
                    <v-data-table :headers="headers" :items="students">
                        <template v-slot:item.actions="{ item }">
                            <v-icon class="me-2" size="small" color="primary" @click="editItem(item)">
                                mdi-pencil
                            </v-icon>
                            <v-icon size="small" color="error" @click="deleteItem(item)">
                                mdi-delete
                            </v-icon>
                        </template>
                    </v-data-table>
                </div>
                <v-dialog width="400" v-model="editDialog">
                    <v-card :title="$t('Edit student')">
                        <v-card-text>
                            <v-text-field v-model="editedItem.name" :label="$t('Name')" />
                            <v-text-field v-model="editedItem.email" :label="$t('Email')" />
                        </v-card-text>
                        <v-card-actions>
                        <v-spacer></v-spacer>
                            <v-btn color="error" variant="text" @click="editDialog=false">
                                Cancel
                            </v-btn>
                            <v-btn color="primary" variant="tonal" @click="saveEditedItem">
                                Save
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
            </v-card-text>
            <v-card-actions>
                <!-- <v-btn icon="mdi-account-school" color="warning" @click="lostStudents"/> -->
                <v-spacer/>
                <v-btn variant="tonal" color="error" :text="$t('Close')" :disabled="isLoading" @click="isActive.value = false"/>
                <v-btn variant="elevated" color="primary" :text="$t('Submit')" :loading="isLoading" @click="proceedAddStudents"/>
            </v-card-actions>
            </v-card>
        </template>
    </v-dialog>
</template>
<script setup>
    import { ref } from 'vue';
    import { useUserStore } from '@/stores/useUserStore';
    import { storeToRefs } from 'pinia';

    const userStore = useUserStore();
    const { addStudents, lostStudents } = userStore;
    const { isLoading } = storeToRefs(userStore);

    const levels = ['6e', '5e', '4e', '3e', '2nde', '1re', 'Term', 'Y7', 'Y8', 'Y9', 'Y10', 'Y11', 'Y12', 'Y13'];
    const headers = [
        { title: 'Name', value: 'name' }, { title: 'Email', value: 'email' }, { title: 'Card Nb', value: 'cardNb' }, { title: 'Actions', key:'actions', sortable: false }
    ]

    const mainDialog = ref(false);
    const showHelp = ref(false);
    const classLevel = ref(null);
    const className = ref(null);
    const campus = ref('BPR');

    const students = ref([]);
    const editDialog = ref(false);
    const editedItem = ref(null);

    const pasteStudents = async () => {
        let clipboardText = '';

        try {
            clipboardText = await navigator.clipboard.readText();
            const rows = clipboardText.split('\n').map(row => row.split('\t'));

            let id = 0;
            const rowsArray = rows.map(row => {
                id++;
                return {
                    id,
                    name: (row[1].split(" "))[0].replace(/,/g, '')+' '+row[0],
                    email: row[2].replace(/\r$/, ''),
                    cardNb: row[3]
                };
            });

            students.value = rowsArray;
        } catch (error) {
            console.error('Error reading from clipboard:', error);
        }
    }

    const editItem = item => {
        editedItem.value = {...item};
        editDialog.value = true;
    }
    const deleteItem = item => {
        students.value = students.value.filter(s => s.id != item.id);
    }
    const saveEditedItem = () => {
        students.value = students.value.map(s => s.id == editedItem.value.id ? editedItem.value : s);
        editDialog.value = false;
        console.log(students.value);
    }    

    const proceedAddStudents = async () => {
        if(className.value && classLevel.value && campus.value && students.value.length){
            await addStudents({ students: students.value, level: classLevel.value, section: className.value, campus: campus.value  });
            students.value = [];
            classLevel.value = null;
            className.value = null;
            campus.value = 'BPR';
            mainDialog.value = false;
        }
    }
</script>