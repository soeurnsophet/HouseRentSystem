import { defineStore } from "pinia";
import { ref } from "vue";
import roomTypeService from "../services/room-type.service";

const useRoomTypeStore = defineStore("roomType", () => {
    const roomTypes = ref([]);
    const meta = ref({});
    const loading = ref(false);
    const saving = ref(false);
    const deleting = ref(false);

    const fetchRoomTypes = async (params = {}) => {
        loading.value = true;

        try {
            const { data } = await roomTypeService.fetch(params);
            roomTypes.value = data.room_types;
            meta.value = data.meta;
            return data;
        } finally {
            loading.value = false;
        }
    };

    const createRoomType = async (payload) => {
        saving.value = true;

        try {
            const { data } = await roomTypeService.create(payload);
            return data;
        } finally {
            saving.value = false;
        }
    };

    const updateRoomType = async (id, payload) => {
        saving.value = true;

        try {
            const { data } = await roomTypeService.update(id, payload);
            return data;
        } finally {
            saving.value = false;
        }
    };

    const deleteRoomType = async (id) => {
        deleting.value = true;

        try {
            const { data } = await roomTypeService.delete(id);
            return data;
        } finally {
            deleting.value = false;
        }
    };

    return {
        roomTypes,
        meta,
        loading,
        saving,
        deleting,
        fetchRoomTypes,
        createRoomType,
        updateRoomType,
        deleteRoomType,
    };
});

export default useRoomTypeStore;
