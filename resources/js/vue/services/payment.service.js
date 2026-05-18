import api from "./api";

export default {
    fetch: (params = {}) => api.get("/payments", { params }),
    create: (payload) => api.post("/payments", payload),
    update: (id, payload) => api.put(`/payments/${id}`, payload),
    delete: (id) => api.delete(`/payments/${id}`),
};
