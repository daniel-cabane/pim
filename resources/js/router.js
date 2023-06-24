import { createRouter, createWebHistory } from "vue-router";
import Home from "./components/views/home-view.vue";
import Workshops from "./components/views/workshops-view.vue";
import Calendar from "./components/views/calendar-view.vue";

const routes = [
    {
        path: "/",
        name: "home",
        component: Home,
    },
    {
        path: "/workshops",
        name: "workshops",
        component: Workshops,
    },
    {
        path: "/calendar",
        name: "calendar",
        component: Calendar,
    },
];

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes,
});

export default router;