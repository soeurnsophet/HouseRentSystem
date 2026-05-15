import { defineStore } from "pinia";
import { ref } from "vue";
import buildingService from "../services/building.service";

const useBuildingStore = defineStore("building", () => {
    const buildings = ref([]);
    const loading = ref(false);

    const fetchBuildings = async () => {
        loading.value = true;
        try {
            const { data } = await buildingService.fetchBuildings();
            buildings.value = data.buildings;
        } catch (error) {
            console.log(error);
        } finally {
            loading.value = false;
        }
    };
    const createBuilding = async (payload) => {
        loading.value = true;
        try {
            const { data } = await buildingService.create(payload);
            return data;
        } catch (error) {
            console.log(error);
            throw error;
        } finally {
            loading.value = false;
        }
    };

    const updateBuilding = async (id, payload) => {
        loading.value = true;
        try {
            const { data } = await buildingService.update(id, payload);
            return data;
        } catch (error) {
            console.log(error);
            throw error;
        } finally {
            loading.value = false;
        }
    };

    const deleteBuilding = async (id) => {
        loading.value = true;
        try {
            const { data } = await buildingService.delete(id);
            return data;
        } catch (error) {
            console.log(error);
            throw error;
        } finally {
            loading.value = false;
        }
    };

    return {
        buildings,
        loading,
        fetchBuildings,
        createBuilding,
        updateBuilding,
        deleteBuilding,
    };
});

export default useBuildingStore;
