import api from "./api";
export default {
    fetch: (params = {}) => api.get("/bills", { params }),
    create: (payload) => api.post("/bills", payload),
    update: (id, data) => api.put(`/bills/${id}`, data),
    delete: (id) => api.delete(`/bills/${id}`),
};
