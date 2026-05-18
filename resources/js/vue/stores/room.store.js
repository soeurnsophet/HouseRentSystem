import { defineStore } from "pinia";
import { ref } from "vue";
import roomService from "../services/room.service";

const useRoomStore = defineStore("room", () => {
    const rooms = ref([]);
    const meta = ref({});
    const loading = ref(false);
    const saving = ref(false);
    const deleting = ref(false);

    const fetchRooms = async (params = {}) => {
        loading.value = true;

        try {
            const { data } = await roomService.fetch(params);
            rooms.value = data.rooms;
            meta.value = data.meta;
            return data;
        } finally {
            loading.value = false;
        }
    };

    const createRoom = async (payload) => {
        saving.value = true;

        try {
            const { data } = await roomService.create(payload);
            return data;
        } finally {
            saving.value = false;
        }
    };

    const updateRoom = async (id, payload) => {
        saving.value = true;

        try {
            const { data } = await roomService.update(id, payload);
            return data;
        } finally {
            saving.value = false;
        }
    };

    const deleteRoom = async (id) => {
        deleting.value = true;

        try {
            const { data } = await roomService.delete(id);
            return data;
        } finally {
            deleting.value = false;
        }
    };

    return {
        rooms,
        meta,
        loading,
        saving,
        deleting,
        fetchRooms,
        createRoom,
        updateRoom,
        deleteRoom,
    };
});

export default useRoomStore;
