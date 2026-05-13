import api from "./api";

const userService = {
    fetchUsers: (params = {}) => api.get("/users", { params }),
    show: (id) => api.get(`/users/${id}`),
    create: (data) => api.post("/users", data),
    update: (id, data) => api.put(`/users/${id}`, data),
    delete: (id) => api.delete(`/users/${id}`),
    verifyChangePassword: (id, data) => api.put(`/users/${id}/password`, data),
    disabledUser: (id) => api.put(`/users/${id}/disabled`),
};

export default userService;
