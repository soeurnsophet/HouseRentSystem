import { defineStore } from "pinia";
import { ref } from "vue";
import billService from "../services/bill.service";

const useBillStore = defineStore("bill", () => {
    const bills = ref([]);
    const meta = ref({});
    const loading = ref(false);
    const saving = ref(false);
    const deleting = ref(false);

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
    return {
        bills,
        meta,
        loading,
        saving,
        deleting,
        createBill,
    };
});

export default useBillStore;
