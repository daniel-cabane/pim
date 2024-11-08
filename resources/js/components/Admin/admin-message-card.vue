<template>
    <v-card class="pa-3">
        <div class="mb-4 d-flex justify-space-between align-center">
        <div>
            <div class="text-caption text-captionColor">
            From
            </div>
            <div>
            {{ message.sender.name }}
            </div>
            <div class="text-caption text-captionColor">
            {{ message.sender.email }}
            </div>
        </div>
        <div>
            <div class="text-caption text-captionColor">
            Date
            </div>
            <div>
                {{ $d(message.sentOn) }}
            </div>
            <div style="height:20px;"></div>
        </div>
        <v-menu>
            <template v-slot:activator="{ props }">
              <v-btn :icon="currentOption.icon" :color="currentOption.color" variant="text" v-bind="props"></v-btn>
            </template>

            <v-list>
              <v-list-item rounded="circle" v-for="option in statusOptions" @click="updateMessageStatus(message.id, option.status)">
                  <v-icon :icon="option.icon" :color="option.color"/>
              </v-list-item>
              <v-list-item rounded="circle" @click="deleteMessage(message.id)">
                <v-icon icon="mdi-delete" color="error"/>
              </v-list-item>
            </v-list>
          </v-menu>
        </div>
        <div>
        <div class="text-caption text-captionColor">
            Message
        </div>
        <div>
            {{ message.body }}
        </div>
        </div>
    </v-card>
</template>
<script setup>
    import { computed } from "vue";
    import { useAuthStore } from '@/stores/useAuthStore';
    import { storeToRefs } from 'pinia';

    const authStore = useAuthStore();
    const { updateMessageStatus, deleteMessage } = authStore;
    const { loading } = storeToRefs(authStore);

    const props = defineProps({ message: Object });

    const statusOptions = [
        { status: 'read', icon: 'mdi-email-check-outline', color: 'secondary' },
        { status: 'unread', icon: 'mdi-email-alert-outline', color: 'primary' },
        { status: 'important', icon: 'mdi-email-alert-outline', color: 'error' }
    ];

    const currentOption = computed(() => {
        return statusOptions.find(o => o.status == props.message.status);
    })
</script>