import { defineStore } from "pinia";
import { ref } from "vue";
import userService from "../services/user.service";

const useUserStore = defineStore("users", () => {
    const users = ref([]);
    const meta = ref({
        current_page: 1,
        per_page: 10,
        total: 0,
        last_page: 1,
    });
    const loading = ref(false);
    const saving = ref(false);
    const deleting = ref(false);

    const fetchUsers = async (params = {}) => {
        loading.value = true;

        try {
            const { data } = await userService.fetchUsers(params);

            users.value = data.users;
            meta.value = data.meta;

            return data;
        } finally {
            loading.value = false;
        }
    };

    const createUser = async (payload) => {
        saving.value = true;

        try {
            const { data } = await userService.create(payload);
            return {
                success: true,
                message: data.message,
            };
        } finally {
            saving.value = false;
        }
    };

    const updateUser = async (id, payload) => {
        saving.value = true;

        try {
            const { data } = await userService.update(id, payload);
            return {
                success: true,
                message: data.message,
            };
        } finally {
            saving.value = false;
        }
    };

    const deleteUser = async (id) => {
        deleting.value = true;

        try {
            const { data } = await userService.delete(id);
            return data;
        } finally {
            deleting.value = false;
        }
    };

    const verifyChangePassword = async (id, payload) => {
        try {
            const { data } = await userService.verifyChangePassword(
                id,
                payload,
            );
            return {
                success: true,
                message: data.message,
            };
        } finally {
            saving.value = false;
        }
    };

    // disable user

    const disableUser = async (id) => {
        try {
            const { data } = await userService.disabledUser(id);
            return {
                success: true,
                message: data.message,
                disabled: data.disabled,
            };
        } finally {
            saving.value = false;
        }
    };

    return {
        users,
        meta,
        loading,
        saving,
        deleting,
        fetchUsers,
        createUser,
        updateUser,
        deleteUser,
        verifyChangePassword,
        disableUser,
    };
});

export default useUserStore;
