import { createRouter, createWebHistory } from "vue-router";
import Home from "./components/views/home-view.vue";
import Workshops from "./components/views/workshops-view.vue";
import Calendar from "./components/views/calendar-view.vue";
import myPosts from "./components/views/myPosts-view.vue";
import editPost from "./components/views/editPosts-view.vue";
import singlePost from "./components/views/singlePost-view.vue";

const routes = [
    {
        path: "/",
        name: "Home",
        component: Home,
    },
    {
        path: "/workshops",
        name: "Workshops",
        component: Workshops,
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
        name: "Edit Posts",
        component: editPost,
    },
    {
        path: "/posts/:slug",
        name: "See Post",
        component: singlePost,
    },
];

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes,
});

export default router;