<template>
    <v-dialog scrollable max-width="850" v-model="mainDialog">
        <template v-slot:activator="{ props: activatorProps }">
            <v-btn color="primary" size="large" icon="mdi-badge-account-outline" v-bind="activatorProps"/>
        </template>
        <template v-slot:default="{ isActive }">
            <v-card :title="$t('Link tag number')">
            <v-card-text>
                <div class="d-flex flex-wrap ga-2 align-center" v-if="users.length == 0">
                    <v-btn 
                        color="primary" 
                        style="flex: 1;"
                        append-icon="mdi-content-paste" 
                        :loading="isLoading" 
                        :text="$t('Paste from clipboard')" 
                        @click="pasteTags" 
                    />
                    <v-btn icon="mdi-help" color="primary" size="small" variant="tonal" @click="showHelp = !showHelp"/>
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
                </div>
                <div class="d-flex flex-wrap ga-3">
                    <v-card width="100%" v-for="user in users" :title="`${user.name2} ${user.name1}`" :subtitle="user.tag_number">
                        <template #append>
                            <v-chip v-if="!user.possibleMatch.length" color="warning" variant="flat">
                                <span class="text-white">
                                    {{ $t('No match found') }}
                                </span>
                            </v-chip>
                        </template>
                        <v-card-text v-if="user.possibleMatch.length">
                            <v-table hover>
                                <tbody>
                                    <tr v-for="match in user.possibleMatch">
                                        <td>
                                            {{ match.name }}
                                        </td>
                                        <td>
                                            {{ match.email }}
                                        </td>
                                        <td>
                                            <v-icon icon="mdi-close" size="large" color="error" @click="removeMatch(user.tag_number, match.id)"/>
                                            <v-icon icon="mdi-check" size="large" class="ml-3" color="success" @click="confirmMatch(user.tag_number, match.id)"/>
                                        </td>
                                    </tr>
                                </tbody>
                            </v-table>
                        </v-card-text>
                    </v-card>     
                </div>
            </v-card-text>
            <v-card-actions>
                <v-btn variant="tonal" color="success" :text="$t('Confirm')" @click="validateAll"/>
                <v-spacer/>
                <v-btn variant="tonal" color="error" :text="$t('Close')" @click="closeDialog"/>
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
    const { findStudentsByTag, clearUsers, attributeTag, massAttributeTag } = userStore;
    const { isLoading, users } = storeToRefs(userStore);

    const mainDialog = ref(false);
    const showHelp = ref(false);

    const closeDialog = () => {
        mainDialog.value = false;
        showHelp.value = false;
        clearUsers();
    }

    const pasteTags = async () => {
        let clipboardText = '';

        clipboardText = await navigator.clipboard.readText();
        const rows = clipboardText.split('\n').map(row => row.split('\t'));

        const rowsArray = rows.map(row => {
            return {
                name1: row[0].split(' ')[0].replace(/,+$/, ''),
                name2: row[1].replace(/\r$/, '').split(' ')[0].replace(/,+$/, ''),
                email: row[2].split('@')[0],
                tag_number: row[3]
            };
        });

        await findStudentsByTag(rowsArray);
    }

    const removeMatch = (userTag, matchId) => {
        const user = users.value.find(u => u.tag_number == userTag);
        user.possibleMatch = user.possibleMatch.filter(m => m.id != matchId);
    }
    const confirmMatch = async (userTag, matchId) => {
        await attributeTag({tagNb: userTag, userId: matchId});
    }

    const validateAll = async () => {
        const matches = [];
         users.value.forEach(u => {
            if(u.possibleMatch.length == 1){
                matches.push({tag_number: u.tag_number, id: u.possibleMatch[0].id});
            }
        });
        await massAttributeTag(matches);
    }
</script>