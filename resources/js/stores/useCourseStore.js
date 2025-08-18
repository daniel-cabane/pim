import { defineStore } from 'pinia';
import useAPI from '@/composables/useAPI';

const { get, post, patch, del, isLoading } = useAPI();

export const useCourseStore = defineStore({
    id: 'course',
    state: () => ({
        isReady: false,
        course: {},
        courses: [],
        students: []
    }),
    actions: {
        addSection() {
            this.course.sections.push({
                title: { fr: 'Nouvelle section', en: 'New section' },
                description: { fr: 'Nouvelle section de ce parcours', en: 'New section of this course' },
                objectives: []
            });
        },
        async myCourses() {
            this.isReady = false;
            const res = await get('/api/myCourses');
            this.courses = res.courses;
            this.isReady = true;
        },
        async getCourse(id) {
            this.isReady = false;
            const res = await get(`/api/courses/${id}`);
            // console.log(res.course);
            this.course = res.course;
            this.isReady = true;
        },
        async createCourse(title_fr, title_en) {
            const res = await post('/api/courses', { title_fr, title_en });
            this.courses.push(res.course);
        },
        async updateCourse() {
            console.log(this.course);
            const res = await patch(`/api/courses/${this.course.id}`, this.course);
            this.course = res.course;
            this.courses = this.courses.map(c => c.id === res.course.id ? res.course : c);
        },
        async deleteCourse(id) {
            const res = await del(`/api/courses/${id}`);
            this.courses = this.courses.filter(c => c.id !== res.id);
        },
        async searchStudent(name) {
            const res = await get(`/api/courses/${this.course.id}/searchStudent?query=Laravel&name=${name}`);
            this.students = res.students;
        },
        async addStudent(student){
            const res = await post(`/api/courses/${this.course.id}/students`, { id: student.id });
            this.students = this.students.filter(s => s.id != student.id);
            this.course = res.course;
        },
        async removeStudent(list) {
            const res = await patch(`/api/courses/${this.course.id}/students`, { list });
            this.course = res.course;
        },
        async updateScores(data) {
            const res = await patch(`/api/courses/${this.course.id}/scores`, data);
            this.course = res.course;
        },
        async join(code) {
            const res = await post(`/api/courses/join`, { code });
            if(res.course){
                this.courses.push(res.course);
                return true;
            }
            return false;
        },
        async leave() {
            const res = await post(`/api/courses/${this.course.id}/leave`);
            return res;
        },
        async addBonus(data) {
            const res = await post(`/api/courses/${this.course.id}/students/${data.studentId}/bonus`, data);
            if (res.course) {
                this.course = res.course;
                return true;
            }
            return false;
        },
        async editBonus(data){
            const res = await patch(`/api/courses/${this.course.id}/bonus/${data.id}`, data);
            if (res.course) {
                this.course = res.course;
                return true;
            }
            return false;
        },
        async deleteBonus(id) {
            console.log(id);
            const res = await del(`/api/courses/${this.course.id}/bonus/${id}`);
            if (res.course) {
                this.course = res.course;
                return true;
            }
            return false;
        }
    },
    getters: {
        isLoading: () => {
            return isLoading.value;
        },
    }
});