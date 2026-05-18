import { defineStore } from "pinia";
import { ref } from "vue";
import billService from "../services/bill.service";

const useBillStore = defineStore("bill", () => {
    const bills = ref([]);
    const meta = ref({});
    const loading = ref(false);
    const saving = ref(false);
    const deleting = ref(false);

    const fetchBills = async (params = {}) => {
        loading.value = true;

        try {
            const { data } = await billService.fetch(params);
            bills.value = data.bills;
            meta.value = data.meta || {};
            return data;
        } finally {
            loading.value = false;
        }
    };

    const createBill = async (payload) => {
        saving.value = true;
        try {
            const { data } = await billService.create(payload);
            return data;
        } catch (error) {
            console.log(error);
            throw error;
        } finally {
            saving.value = false;
        }
    };

    const updateBill = async (id, payload) => {
        saving.value = true;

        try {
            const { data } = await billService.update(id, payload);
            return data;
        } finally {
            saving.value = false;
        }
    };

    const deleteBill = async (id) => {
        deleting.value = true;

        try {
            const { data } = await billService.delete(id);
            return data;
        } finally {
            deleting.value = false;
        }
    };

    return {
        bills,
        meta,
        loading,
        saving,
        deleting,
        fetchBills,
        createBill,
        updateBill,
        deleteBill,
    };
});

export default useBillStore;
