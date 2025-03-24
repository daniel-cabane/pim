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
        },
        async massAttributeTag(data) {
            const res = await patch(`/api/admin/users/massAttributeTag`, { data });
            this.users = this.users.filter(u => !data.find(o => o.id == u.id));
            console.log(res);
        },
        async lostStudents() {
            const res = await get('/api/admin/lostStudents');
            console.log(res);
        }
    },
    getters: {
        isLoading: () => {
            return isLoading.value;
        }
    }
});