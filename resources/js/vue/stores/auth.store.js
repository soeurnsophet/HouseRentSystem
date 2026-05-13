import { defineStore } from "pinia";
import { ref } from "vue";
import api from "../services/api";

const userAuthStore = defineStore("auth", () => {
    // States
    const user = ref(JSON.parse(localStorage.getItem("user")) || null);

    const token = ref(localStorage.getItem("access_token") || null);

    const isAuthenticated = ref(!!token.value);

    const isLoading = ref(false);

    // Login
    const login = async (payload) => {
        try {
            isLoading.value = true;

            const res = await api.post("/login", payload);

            localStorage.setItem("access_token", res.data.access_token);

            localStorage.setItem("user", JSON.stringify(res.data.user));

            token.value = res.data.access_token;

            user.value = res.data.user;

            isAuthenticated.value = true;

            return {
                success: true,
                message: "Login successful",
            };
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || "Login failed",
            };
        } finally {
            isLoading.value = false;
        }
    };

    const clearAuth = () => {
        localStorage.removeItem("access_token");

        localStorage.removeItem("user");

        token.value = null;

        user.value = null;

        isAuthenticated.value = false;
    };

    // Logout
    const logout = async () => {
        try {
            const accessToken = token.value || localStorage.getItem("access_token");

            if (accessToken) {
                await api.post(
                    "/logout",
                    {},
                    {
                        headers: {
                            Authorization: `Bearer ${accessToken}`,
                        },
                    },
                );
            }
        } finally {
            clearAuth();
        }
    };


    return {
        user,
        token,
        isAuthenticated,
        isLoading,
        login,
        logout,
        clearAuth,
    };
});

export default userAuthStore;
