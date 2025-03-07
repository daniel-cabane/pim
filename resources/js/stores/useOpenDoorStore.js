import { defineStore } from 'pinia';
import useAPI from '@/composables/useAPI';

const { get, post, patch, del, isLoading } = useAPI();

export const useOpenDoorStore = defineStore({
    id: 'openDoor',
    state: () => ({
        visitor: {},
        message: ''
    }),
    actions: {
        async visit(tagNb) {
            const res = await post('/api/opendoors/visit', { tagNb });
            this.visitor = res;
            console.log(res);
        },
        async register() {
            console.log(this.visitor);
            const res = await post(`/api/opendoors/${this.visitor.visitId}/register`, this.visitor);
            console.log(res);
        }
    },
    getters: {
        isLoading: () => {
            return isLoading.value;
        },
    }
});