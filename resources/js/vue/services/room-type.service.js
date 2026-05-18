import api from "./api";

export default {
    fetch: (params = {}) => api.get("/room-types", { params }),
    create: (payload) => api.post("/room-types", payload),
    update: (id, data) => api.put(`/room-types/${id}`, data),
    delete: (id) => api.delete(`/room-types/${id}`),
};
