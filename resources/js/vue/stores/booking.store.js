import { defineStore } from "pinia";
import { ref } from "vue";
import bookingService from "../services/booking.service";

const useBookingStore = defineStore("booking", () => {
    const bookings = ref([]);
    const meta = ref({});
    const loading = ref(false);
    const saving = ref(false);
    const deleting = ref(false);

    const fetchBookings = async (params = {}) => {
        loading.value = true;

        try {
            const { data } = await bookingService.fetch(params);
            bookings.value = data.bookings;
            meta.value = data.meta;
            return data;
        } finally {
            loading.value = false;
        }
    };

    const createBooking = async (payload) => {
        saving.value = true;

        try {
            const { data } = await bookingService.create(payload);
            return data;
        } finally {
            saving.value = false;
        }
    };

    const updateBooking = async (id, payload) => {
        saving.value = true;

        try {
            const { data } = await bookingService.update(id, payload);
            return data;
        } finally {
            saving.value = false;
        }
    };

    const deleteBooking = async (id) => {
        deleting.value = true;

        try {
            const { data } = await bookingService.delete(id);
            return data;
        } finally {
            deleting.value = false;
        }
    };

    return {
        bookings,
        meta,
        loading,
        saving,
        deleting,
        fetchBookings,
        createBooking,
        updateBooking,
        deleteBooking,
    };
});

export default useBookingStore;
