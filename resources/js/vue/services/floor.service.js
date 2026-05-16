import api from "./api";
export default {
    fetch: (params = {}) => api.get("/floors", { params }),
    create: (payload) => api.post("/floors", payload),
    update: (id, data) => api.put(`/floors/${id}`, data),
    delete: (id) => api.delete(`/floors/${id}`),
};
