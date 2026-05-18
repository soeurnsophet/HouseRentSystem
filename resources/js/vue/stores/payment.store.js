import { defineStore } from "pinia";
import { ref } from "vue";
import paymentService from "../services/payment.service";

const usePaymentStore = defineStore("payment", () => {
    const payments = ref([]);
    const meta = ref({});
    const loading = ref(false);
    const saving = ref(false);
    const deleting = ref(false);

    const fetchPayments = async (params = {}) => {
        loading.value = true;

        try {
            const { data } = await paymentService.fetch(params);
            payments.value = data.payments;
            meta.value = data.meta || {};
            return data;
        } finally {
            loading.value = false;
        }
    };

    const createPayment = async (payload) => {
        saving.value = true;

        try {
            const { data } = await paymentService.create(payload);
            return data;
        } finally {
            saving.value = false;
        }
    };

    const updatePayment = async (id, payload) => {
        saving.value = true;

        try {
            const { data } = await paymentService.update(id, payload);
            return data;
        } finally {
            saving.value = false;
        }
    };

    const deletePayment = async (id) => {
        deleting.value = true;

        try {
            const { data } = await paymentService.delete(id);
            return data;
        } finally {
            deleting.value = false;
        }
    };

    return {
        payments,
        meta,
        loading,
        saving,
        deleting,
        fetchPayments,
        createPayment,
        updatePayment,
        deletePayment,
    };
});

export default usePaymentStore;
