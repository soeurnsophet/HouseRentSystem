import api from "./api";
export default {
    fetchBuildings: () => api.get("/buildings"),
    create: (payload) => api.post("/buildings", payload),
    update: (id, data) => api.put(`/buildings/${id}`, data),
    delete: (id) => api.delete(`/buildings/${id}`),
};
