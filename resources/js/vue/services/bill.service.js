import api from "./api";
export default {
    create: (payload) => api.post("/bills", payload),
};
