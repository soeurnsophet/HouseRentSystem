import axios from "axios";

const api = axios.create({
    baseURL: import.meta.env.VITE_APP_API_URL,
    headers: {
        // "Content-Type": "application/json",
        Accept: "application/json",
    },
});
// api.interceptors.request.use((config) => {
//     const auth = useAuthStore();
//     if (auth.access_token) {
//         config.headers.Authorization = `Bearer ${auth.access_token}`;
//     }
//     return config;
// });
export default api;
