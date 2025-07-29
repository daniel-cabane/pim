import { createRouter, createWebHistory } from "vue-router";
import Home from "./components/views/home-view.vue";
import Workshops from "./components/views/workshops-view.vue";
import Calendar from "./components/views/calendar-view.vue";
import CalendarPi from "./components/views/calendar-pi-view.vue";
import Blog from "./components/views/blog-view.vue";
import myPosts from "./components/views/myPosts-view.vue";
import editPost from "./components/views/editPost-view.vue";
import singlePost from "./components/views/singlePost-view.vue";
import myWorkshops from "./components/views/myWorkshops-view.vue";
import editWorkshop from "./components/views/editWorkshop-view.vue";
import singleWorkshop from "./components/views/singleWorkshop-view.vue";
import Admin from "./components/views/admin-view.vue";
import singleSurvey from "./components/views/singleSurvey-view.vue";
import hod from "./components/views/hod-view.vue";
import openDoorsBPR from "./components/views/open-doors-bpr-view.vue";
import teacherOpenDoorsBPR from "./components/views/teacher-open-doors-bpr-view.vue";
import courses from "./components/views/courses-view.vue";
import singleCourse from "./components/views/singleCourse-view.vue";
import editCourse from "./components/views/editCourse-view.vue";
import archivedWorkshops from "./components/views/archivedWorkshops-view.vue";

const routes = [
    {
        path: "/admin",
        name: "Admin",
        component: Admin
    },
    {
        path: "/",
        name: "Home",
        component: Home,
    },
    {
        path: "/calendar",
        name: "Calendar",
        component: Calendar,
    },
    {
        path: "/calendar/pi",
        name: "Calendar-pi",
        component: CalendarPi,
    },
    {
        path: "/myPosts",
        name: "My Posts",
        component: myPosts,
    },
    {
        path: "/posts/:slug/edit",
        name: "Edit Post",
        component: editPost,
    },
    {
        path: "/posts/:slug",
        name: "See Post",
        component: singlePost,
    },
    {
        path: "/workshops",
        name: "Workshops",
        component: Workshops,
    },
    {
        path: "/myWorkshops",
        name: "My Workshops",
        component: myWorkshops,
    },
    {
        path: "/workshops/:id",
        name: "See Workshop",
        component: singleWorkshop,
    },
    {
        path: "/workshops/:id/edit",
        name: "Edit Workshop",
        component: editWorkshop,
    },
    {
        path: "/blog",
        name: "Blog",
        component: Blog,
    },
    {
        path: "/surveys/:id",
        name: "Survey",
        component: singleSurvey,
    },
    {
        path: "/hod",
        name: "HoD",
        component: hod,
    },
    {
        path: "/pobpr",
        name: "Open Doors BPR",
        component: openDoorsBPR,
    },
    {
        path: "/openDoorsBPR",
        name: "Teacher Open Doors BPR",
        component: teacherOpenDoorsBPR,
    },
    {
        path: "/myCourses",
        name: "My courses",
        component: courses,
    },
    {
        path: "/courses/:id",
        name: "Course",
        component: singleCourse,
    },
    {
        path: "/courses/:id/edit",
        name: "Edit Course",
        component: editCourse,
    },
    {
        path: "/archivedWorkshops",
        name: "Archived Workshops",
        component: archivedWorkshops,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;