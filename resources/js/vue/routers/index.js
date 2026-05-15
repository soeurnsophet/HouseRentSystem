import { createRouter, createWebHistory } from "vue-router";
import Home from "../pages/Home.vue";
import Login from "../auth/Login.vue";
import Dashboard from "../dashboard/Dashboard.vue";
import Users from "../pages/users/Users.vue";
import userAuthStore from "../stores/auth.store";
import Building from "../pages/building/Building.vue";

const routes = [
    // { path: "/", component: Home },
    { path: "/login", component: Login, name: "Login" },
    {
        path: "/dashboard",
        component: Dashboard,
        name: "dashboard",
        meta: { requiresAuth: true },
        redirect: { name: "home" },
        children: [
            { path: "home", name: "home", component: Home },
            { path: "users", name: "users", component: Users },
            { path: "buildings", name: "buildings", component: Building },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from) => {
    const auth = userAuthStore();

    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return { name: "Login" };
    }

    if (to.name === "Login" && auth.isAuthenticated) {
        return { name: "home" };
    }

    // if (to.meta.role && auth.user?.role !== to.meta.role) {
    //     if (to.meta.role === "cashier") {
    //         return { name: "sales.create" };
    //     }
    //     return { name: "home" };
    // }
});

export default router;
