import { defineStore } from "pinia";
import floorService from "../services/floor.service";
import { ref } from "vue";

const useFloorStore = defineStore("floor", () => {
    const floors = ref([]);
    const meta = ref({});
    const loading = ref(false);
    const saving = ref(false);
    const deleting = ref(false);

    const fetchFloors = async (params = {}) => {
        loading.value = true;

        try {
            const { data } = await floorService.fetch(params);

            floors.value = data.floors;
            meta.value = data.meta;

            return data;
        } finally {
            loading.value = false;
        }
    };

    const createFloor = async (payload) => {
        saving.value = true;
        try {
            const { data } = await floorService.create(payload);
            return data;
        } catch (error) {
            console.log(error);
            throw error;
        } finally {
            saving.value = false;
        }
    };

    const updateFloor = async (id, payload) => {
        saving.value = true;
        try {
            const { data } = await floorService.update(id, payload);
            return data;
        } catch (error) {
            console.log(error);
            throw error;
        } finally {
            saving.value = false;
        }
    };

    const deleteFloor = async (id) => {
        deleting.value = true;
        try {
            const { data } = await floorService.delete(id);
            return data;
        } catch (error) {
            console.log(error);
            throw error;
        } finally {
            deleting.value = false;
        }
    };

    return {
        floors,
        meta,
        loading,
        saving,
        deleting,
        fetchFloors,
        createFloor,
        updateFloor,
        deleteFloor,
    };
});

export default useFloorStore;
