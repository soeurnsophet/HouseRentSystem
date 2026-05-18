import api from "./api";

export default {
    fetch: (params = {}) => api.get("/bookings", { params }),
    create: (payload) => api.post("/bookings", payload),
    update: (id, data) => api.put(`/bookings/${id}`, data),
    delete: (id) => api.delete(`/bookings/${id}`),
};
