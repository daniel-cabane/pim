import { createRouter, createWebHistory } from "vue-router";
import Home from "./components/views/home-view.vue";
import Workshops from "./components/views/workshops-view.vue";
import Calendar from "./components/views/calendar-view.vue";
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
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;