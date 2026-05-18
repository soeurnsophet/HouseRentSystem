<script setup>
import { computed, defineAsyncComponent, inject, onMounted, ref } from "vue";
import { storeToRefs } from "pinia";
import Message from "primevue/message";
import { useDialog, useToast } from "primevue";
import useBookingStore from "../../stores/booking.store";
import useRoomStore from "../../stores/room.store";
import useUserStore from "../../stores/users.store";
import { MessageSuccess } from "../../utils/Message";

const CreateUser = defineAsyncComponent(() => import("../users/Create.vue"));

const dialogRef = inject("dialogRef");
const dialog = useDialog();
const toast = useToast();
const bookingStore = useBookingStore();
const roomStore = useRoomStore();
const userStore = useUserStore();
const { saving } = storeToRefs(bookingStore);
const { rooms } = storeToRefs(roomStore);
const { users } = storeToRefs(userStore);

const form = ref({
    room_id: null,
    tenant_id: null,
    start_date: "",
    end_date: "",
    status: "pending",
});

const errors = ref({});

const statusOptions = [
    { label: "Pending", value: "pending" },
    { label: "Active", value: "active" },
    { label: "Completed", value: "completed" },
];

const roomOptions = computed(() =>
    rooms.value
        .filter((room) => room.status === "available")
        .map((room) => ({
            ...room,
            label: `${room.floor?.building?.building_name || "Building"} - ${room.floor?.name} - ${room.room_number}`,
        })),
);

const tenantOptions = computed(() =>
    users.value.map((user) => ({
        ...user,
        label: `${user.name} (${user.phone || user.username})`,
    })),
);

const errorFor = (field) => {
    const error = errors.value[field];
    return Array.isArray(error) ? error[0] : error;
};

onMounted(async () => {
    await Promise.all([
        roomStore.fetchRooms({ per_page: 100, status: "available" }),
        userStore.fetchUsers({ per_page: 100, role: "user" }),
    ]);
});

const validate = () => {
    errors.value = {};

    if (!form.value.room_id) errors.value.room_id = "Room is required.";
    if (!form.value.tenant_id) errors.value.tenant_id = "Tenant is required.";
    if (!form.value.start_date) {
        errors.value.start_date = "Start date is required.";
    }
    if (!form.value.status) errors.value.status = "Status is required.";

    return !Object.keys(errors.value).length;
};

const payload = () => ({
    ...form.value,
    end_date: form.value.end_date || null,
});

const submit = async () => {
    if (!validate()) return;

    try {
        const res = await bookingStore.createBooking(payload());
        MessageSuccess("", res.message, toast);
        dialogRef.value.close({ created: true });
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        }
    }
};

const closeDialog = () => {
    dialogRef.value.close({ created: false });
};

const openCreateTenantDialog = () => {
    dialog.open(CreateUser, {
        data: {
            role: "user",
        },
        props: {
            header: "Add Tenant",
            modal: true,
            style: { width: "42rem" },
            breakpoints: { "960px": "75vw", "640px": "92vw" },
            draggable: false,
        },
        onClose: async (options) => {
            if (options?.data?.created) {
                await userStore.fetchUsers({ per_page: 100, role: "user" });

                if (options.data.user?.id) {
                    form.value.tenant_id = options.data.user.id;
                }
            }
        },
    });
};
</script>

<template>
    <form class="space-y-5" @submit.prevent="submit">
        <div class="rounded-lg border border-teal-100 bg-teal-50 p-4">
            <div class="flex items-center gap-3">
                <span
                    class="grid h-10 w-10 place-items-center rounded-md bg-white text-teal-700"
                >
                    <i class="fa-solid fa-calendar-plus"></i>
                </span>
                <div>
                    <p class="font-semibold text-slate-950">Booking Details</p>
                    <p class="text-sm text-slate-600">
                        Choose the tenant, room, dates, and booking status.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    Tenant
                </label>
                <InputGroup>
                    <Select
                        v-model="form.tenant_id"
                        :options="tenantOptions"
                        option-label="label"
                        option-value="id"
                        placeholder="Select tenant"
                        class="w-full"
                        filter
                    />
                    <InputGroupAddon>
                        <Button
                            icon="fa-solid fa-user-plus"
                            type="button"
                            @click="openCreateTenantDialog"
                        />
                    </InputGroupAddon>
                </InputGroup>
                <Message
                    v-if="errorFor('tenant_id')"
                    severity="error"
                    class="mt-2"
                    variant="simple"
                >
                    {{ errorFor("tenant_id") }}
                </Message>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    Room
                </label>
                <Select
                    v-model="form.room_id"
                    :options="roomOptions"
                    option-label="label"
                    option-value="id"
                    placeholder="Select room"
                    class="w-full"
                    filter
                />
                <Message
                    v-if="errorFor('room_id')"
                    severity="error"
                    class="mt-2"
                    variant="simple"
                >
                    {{ errorFor("room_id") }}
                </Message>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    Start Date
                </label>
                <InputText
                    v-model="form.start_date"
                    type="date"
                    class="w-full"
                />
                <Message
                    v-if="errorFor('start_date')"
                    severity="error"
                    class="mt-2"
                    variant="simple"
                >
                    {{ errorFor("start_date") }}
                </Message>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    End Date
                </label>
                <InputText v-model="form.end_date" type="date" class="w-full" />
                <Message
                    v-if="errorFor('end_date')"
                    severity="error"
                    class="mt-2"
                    variant="simple"
                >
                    {{ errorFor("end_date") }}
                </Message>
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    Status
                </label>
                <Select
                    v-model="form.status"
                    :options="statusOptions"
                    option-label="label"
                    option-value="value"
                    placeholder="Select status"
                    class="w-full"
                />
                <Message
                    v-if="errorFor('status')"
                    severity="error"
                    class="mt-2"
                    variant="simple"
                >
                    {{ errorFor("status") }}
                </Message>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <Button
                label="Cancel"
                icon="fa-solid fa-ban"
                severity="secondary"
                outlined
                type="button"
                @click="closeDialog"
            />
            <Button
                label="Save Booking"
                icon="fa-solid fa-save"
                type="submit"
                :loading="saving"
            />
        </div>
    </form>
</template>
