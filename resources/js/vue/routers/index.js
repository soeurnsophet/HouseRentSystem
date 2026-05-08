import { createRouter, createWebHistory } from "vue-router";
import About from "../pages/About.vue";
import Home from "../pages/Home.vue";
import Login from "../auth/Login.vue";

const routes = [
    { path: "/", component: Home },
    { path: "/login", component: Login },
    { path: "/about", component: About },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
