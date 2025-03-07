import { defineStore } from 'pinia';
import useAPI from '@/composables/useAPI';

const { get, post, patch, del, isLoading } = useAPI();

export const useUserStore = defineStore({
    id: 'user',
    state: () => ({
        users: [],
        user: {}
    }),
    actions: {
        async addStudents(data){
            const res = await post(`/api/admin/addStudents`, data);
        },
        async findStudentsByTag(data){
            const res = await post(`/api/admin/findStudentsByTag`, { users: data });
            this.users = res.users;
        },
        clearUsers(){
            this.users = [];
        },
        async attributeTag(data){
            const res = await patch(`/api/admin/users/${data.userId}/tag`, {tagNb: data.tagNb});
            this.users = this.users.filter(u => u.tag_number != res.tagNb);
        }
    },
    getters: {
        isLoading: () => {
            return isLoading.value;
        }
    }
});