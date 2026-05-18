import api from "./api";

export default {
    fetch: (params = {}) => api.get("/rooms", { params }),
    create: (payload) => api.post("/rooms", payload),
    update: (id, data) => api.put(`/rooms/${id}`, data),
    delete: (id) => api.delete(`/rooms/${id}`),
};
