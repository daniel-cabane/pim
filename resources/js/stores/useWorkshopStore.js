import { defineStore } from 'pinia';
import useAPI from '@/composables/useAPI';

const { get, post, patch, del, isLoading } = useAPI();

export const useWorkshopStore = defineStore({
    id: 'workshop',
    state: () => ({
        workshops: [],
        workshopsByTerm: [[], [], []],
        currentTerm: 1,
        myWorkshops: [],
        workshop: {},
        students: [],
        isReady: false,
        // isLoading: false,
        imageLoading: false
    }),
    actions: {
        // async getThemes() {
        //     this.themes = await get('/api/workshops/themes');
        // },
        async createWorkshop(newWorkshop) {
            this.isReady = false;
            const res = await post('/api/workshops', newWorkshop);
            this.workshop = res.workshop;
            this.isReady = true;
            return res.workshop;
        },
        async getWorkshop(id){
            this.isReady = false;
            const res = await get(`/api/workshops/${id}`);
            // console.log(res.workshop);
            this.workshop = res.workshop;
            this.isReady = true;
            return res.workshop;
        },
        async getWorkshops() {
            this.isReady = false;
            this.workshopsByTerm = [[], [], []];
            const res = await get(`/api/workshops/currentTerm`, true);
            this.currentTerm = res.termNb;
            for(let i=0 ; i<=2 ; i++){
                if(i+1 == res.termNb){
                    this.workshopsByTerm[i] = res.workshops;
                }
            }
            this.isReady = true;
            const res2 = await get(`/api/workshops/complete/${res.termNb}`, true);
            res2.workshopsByTerm.forEach(w => {
                for (let i = 0; i <= 2; i++) {
                    if (i + 1 == w.termNb) {
                        this.workshopsByTerm[i] = w.workshops;
                    }
                }
            });
            // console.log(this.workshopsByTerm);
        },
        async updateWorkshop() {
            const res = await patch(`/api/workshops/${this.workshop.id}`, this.workshop);
            this.workshop = res.workshop;
            // UPDATE WORKSHOP IN WORKSHOPS ARRAY + LOAD ??
            return res.workshop;
        },
        async getMyWorkshops() {
            this.isReady = false;
            const res = await get(`/api/myWorkshops`);
            this.myWorkshops = res.workshops;
            this.isReady = true;
        },
        async updateImage(data) {
            this.imageLoading = true;
            let formData = new FormData()
            formData.append('poster', data.file);
            const res = await post(`/api/workshops/${this.workshop.id}/poster/${data.language}`, formData);
            this.workshop = res.workshop;
            this.imageLoading = false;
            return res.workshop;
        },
        async deleteImage(language){
            this.imageLoading = true;
            const res = await del(`/api/workshops/${this.workshop.id}/poster/${language}`, {});
            this.workshop = res.workshop;
            this.imageLoading = false;
            return res.workshop;
        },
        async archiveWorkshop() {
            const res = await del(`/api/workshops/${this.workshop.id}/archive`, {});
            return res;
        },
        async deleteWorkshop(){
            const res = await del(`/api/workshops/${this.workshop.id}`, {});
            return res;
        },
        async adminGetAllWorkshops(){
            this.isReady = false;
            const res = await get(`/api/admin/workshops`);
            this.workshops = res.workshops;
            this.themes = res.themes;
            this.isReady = true;
        },
        async applyWorkshop(data){
            const res = await post(`/api/workshops/${this.workshop.id}/apply`, data);
            this.workshop = res.workshop;
        },
        async withdrawWorkshop(){
            const res = await post(`/api/workshops/${this.workshop.id}/withdraw`);
            this.workshop = res.workshop;
        },
        async editApplicant(data){
            //this.isLoading = true;
            const res = await patch(`/api/workshops/${this.workshop.id}/applicants/${data.id}`, data);
            this.workshop.applicants = this.workshop.applicants.map(a => a.id == res.applicant.id ? res.applicant : a);
            //this.isLoading = false;
        },
        async removeApplicant(data) {
            //this.isLoading = true;
            const res = await del(`/api/workshops/${this.workshop.id}/applicants/${data.id}`);
            this.workshop.applicants = this.workshop.applicants.filter(a => a.id != res.id);
            //this.isLoading = false;
        },
        async prepareLaunch(){
            //this.isLoading = true;
            const res = await get(`/api/admin/workshops/${this.workshop.id}/prepare`);
            //this.isLoading = false;
            return res.info;
        },
        async launch(data) {
            //this.isLoading = true;
            const res = await post(`/api/admin/workshops/${this.workshop.id}/launch`, data);
            this.workshop = res.workshop;
            //this.isLoading = false;
            return res.workshop;
        },
        async createSession() {
            //this.isLoading = true;
            const res = await post(`/api/workshops/${this.workshop.id}/session`);
            this.workshop = res.workshop;
            //this.isLoading = false;
        },
        async updateSession(session) {
            //this.isLoading = true;
            const res = await patch(`/api/workshops/${this.workshop.id}/sessions/${session.id}`, session);
            this.workshop = res.workshop;
            //this.isLoading = false;
        },
        async deleteSession(id) {
            //this.isLoading = true;
            const res = await del(`/api/workshops/${this.workshop.id}/sessions/${id}`);
            this.workshop = res.workshop;
            //this.isLoading = false;
        },
        async orderSessions() {
            //this.isLoading = true;
            const res = await patch(`/api/workshops/${this.workshop.id}/orderSessions`);
            this.workshop = res.workshop;
            //this.isLoading = false;
        },
        async searchStudent(name){
            //this.isLoading = true;
            const res = await get(`/api/workshops/${this.workshop.id}/searchStudent?query=Laravel&name=${name}`);
            this.students = res.students;
            //this.isLoading = false;
        },
        async addStudent(student) {
            const res = await post(`/api/workshops/${this.workshop.id}/addStudent`, student);
            this.students = this.students.filter(s => s.id != student.id);
            if(res.workshop){
                this.workshop = res.workshop;
            }
        },
        // async updateTheme(theme){
        //     //this.isLoading = true;
        //     const res = await patch(`/api/admin/themes/${theme.id}`, theme);
        //     //this.isLoading = false;
        // },
        // async createTheme(title_en, title_fr){
        //     //this.isLoading = true;
        //     const res = await post(`/api/admin/themes`, {title_en, title_fr});
        //     this.themes.push(res.theme);
        //     //this.isLoading = false;
        // }
    },
    getters: {
        isLoading: () => {
            return isLoading.value;
        },
    }
});