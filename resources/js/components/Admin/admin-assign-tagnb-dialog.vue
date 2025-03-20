<template>
    <v-dialog scrollable max-width="850" v-model="mainDialog">
        <template v-slot:activator="{ props: activatorProps }">
            <v-btn color="primary" size="large" icon="mdi-badge-account-outline" v-bind="activatorProps"/>
        </template>
        <template v-slot:default="{ isActive }">
            <v-card :title="$t('Link tag number')">
            <v-card-text>
                <v-btn 
                    color="primary" block 
                    append-icon="mdi-content-paste" 
                    :loading="isLoading" 
                    :text="$t('Paste from clipboard')" 
                    @click="pasteTags" 
                    v-if="users.length == 0"
                />
                <div class="d-flex flex-wrap ga-3">
                    <v-card width="100%" v-for="user in users" :title="`${user.name2} ${user.name1}`" :subtitle="user.tag_number">
                        <v-card-text>
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

    const closeDialog = () => {
        mainDialog.value = false;
        clearUsers();
    }

    const pasteTags = async () => {
        let clipboardText = '';

        clipboardText = await navigator.clipboard.readText();
        const rows = clipboardText.split('\n').map(row => row.split('\t'));

        const rowsArray = rows.map(row => {
            return {
                name1: row[1].split(' ')[0].replace(/,+$/, ''),
                name2: row[2].replace(/\r$/, '').split(' ')[0].replace(/,+$/, ''),
                tag_number: row[0]
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