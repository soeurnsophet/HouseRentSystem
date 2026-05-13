import axios from "axios";
import router from "../routers";

const api = axios.create({
    baseURL: `${import.meta.env.VITE_API_URL || window.location.origin}/api/v1`,
    headers: {
        Accept: "application/json",
    },
});

api.interceptors.request.use((config) => {
    const token = localStorage.getItem("access_token");

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});

//

api.interceptors.response.use(
    (response) => response,
    async (error) => {
        if (error.response?.status === 403) {
            const { default: userAuthStore } =
                await import("../stores/auth.store");
            const authStore = userAuthStore();
            const message =
                error.response?.data?.message ||
                "User is disabled or forbidden";

            authStore.clearAuth();

            if (router.currentRoute.value.name !== "Login") {
                router.push({
                    name: "Login",
                    query: { message },
                });
            }
        }

        return Promise.reject(error);
    },
);
export default api;
