<script setup>
import { computed, defineAsyncComponent, onMounted, reactive, ref } from "vue";
import { storeToRefs } from "pinia";
import { useConfirm, useDialog, useToast } from "primevue";
import useBookingStore from "../../stores/booking.store";
import useRoomStore from "../../stores/room.store";
import useUserStore from "../../stores/users.store";
import { MessageSuccess } from "../../utils/Message";
import CreateBill from "../bill/CreateBill.vue";

const CreateBooking = defineAsyncComponent(() => import("./CreateBooking.vue"));
const UpdateBooking = defineAsyncComponent(() => import("./UpdateBooking.vue"));

const bookingStore = useBookingStore();
const roomStore = useRoomStore();
const userStore = useUserStore();
const { bookings, meta, loading } = storeToRefs(bookingStore);
const { rooms } = storeToRefs(roomStore);
const { users } = storeToRefs(userStore);

const dialog = useDialog();
const confirm = useConfirm();
const toast = useToast();
const menu = ref();
const selectedBooking = ref(null);

const lazyParams = reactive({
    first: 0,
    page: 0,
    rows: 10,
    search: "",
    status: null,
    room_id: null,
    tenant_id: null,
    totalRecords: 0,
});

const statusOptions = [
    { label: "Pending", value: "pending" },
    { label: "Active", value: "active" },
    { label: "Completed", value: "completed" },
];

const statusSeverity = {
    pending: "warn",
    active: "info",
    completed: "success",
};

const roomOptions = computed(() =>
    rooms.value.map((room) => ({
        ...room,
        label: `${room.room_number} - ${room.floor?.building?.building_name || "Building"}`,
    })),
);

const tenantOptions = computed(() =>
    users.value.map((user) => ({
        ...user,
        label: `${user.name} (${user.phone || user.username})`,
    })),
);

const summary = computed(() => ({
    total: meta.value.total_bookings || lazyParams.totalRecords,
    pending: meta.value.pending_bookings || 0,
    active: meta.value.active_bookings || 0,
    completed: meta.value.completed_bookings || 0,
}));

const fetchBookings = async () => {
    await bookingStore.fetchBookings({
        page: lazyParams.page + 1,
        per_page: lazyParams.rows,
        search: lazyParams.search || undefined,
        status: lazyParams.status || undefined,
        room_id: lazyParams.room_id || undefined,
        tenant_id: lazyParams.tenant_id || undefined,
    });

    lazyParams.totalRecords = meta.value.total || 0;
};

let searchTimer;
const onSearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        lazyParams.first = 0;
        lazyParams.page = 0;
        fetchBookings();
    }, 300);
};

const refreshOptions = async () => {
    await Promise.all([
        roomStore.fetchRooms({ per_page: 100 }),
        userStore.fetchUsers({ per_page: 100, role: "user" }),
    ]);
};

const refreshAll = async () => {
    await Promise.all([fetchBookings(), refreshOptions()]);
};

onMounted(refreshAll);

const onPage = (event) => {
    lazyParams.first = event.first;
    lazyParams.page = event.page;
    lazyParams.rows = event.rows;
    fetchBookings();
};

const clearFilters = () => {
    lazyParams.first = 0;
    lazyParams.page = 0;
    lazyParams.search = "";
    lazyParams.status = null;
    lazyParams.room_id = null;
    lazyParams.tenant_id = null;
    fetchBookings();
};

const openCreateDialog = () => {
    dialog.open(CreateBooking, {
        props: {
            position: "top",
            header: "Add Booking",
            modal: true,
            style: { width: "42rem" },
            breakpoints: { "960px": "75vw", "640px": "92vw" },
            draggable: false,
        },
        onClose: (options) => {
            if (options?.data?.created) {
                fetchBookings();
            }
        },
    });
};

const openUpdateDialog = () => {
    if (!selectedBooking.value) return;

    dialog.open(UpdateBooking, {
        data: { booking: selectedBooking.value },
        props: {
            position: "top",
            header: "Update Booking",
            modal: true,
            style: { width: "42rem" },
            breakpoints: { "960px": "75vw", "640px": "92vw" },
            draggable: false,
        },
        onClose: (options) => {
            if (options?.data?.updated) {
                fetchBookings();
            }
        },
    });
};

const confirmDelete = () => {
    if (!selectedBooking.value) return;

    confirm.require({
        message: `Are you sure you want to delete this booking for ${selectedBooking.value.tenant?.name || "this tenant"}?`,
        header: "Delete Confirmation",
        icon: "fa-solid fa-triangle-exclamation",
        rejectProps: {
            label: "Cancel",
            icon: "fa-solid fa-ban",
            severity: "secondary",
            outlined: true,
        },
        acceptProps: {
            label: "Delete",
            icon: "fa-solid fa-trash",
            severity: "danger",
        },
        accept: async () => {
            const res = await bookingStore.deleteBooking(
                selectedBooking.value.id,
            );
            MessageSuccess("", res.message, toast);

            if (bookings.value.length === 1 && lazyParams.page > 0) {
                lazyParams.page -= 1;
                lazyParams.first = lazyParams.page * lazyParams.rows;
            }

            await fetchBookings();
        },
    });
};

// Bill
const openCreateBillDialog = () => {
    if (!selectedBooking.value) return;

    dialog.open(CreateBill, {
        data: { booking: selectedBooking.value },
        props: {
            position: "top",
            header: "Create Bill",
            modal: true,
            style: { width: "42rem" },
            breakpoints: { "960px": "75vw", "640px": "92vw" },
            draggable: false,
        },
        onClose: (options) => {
            if (options?.data?.created) {
                fetchBookings();
            }
        },
    });
};

const actionItems = ref([
    {
        label: "Actions",
        items: [
            {
                label: "Create Bill",
                icon: "fa-solid fa-file-invoice",
                command: openCreateBillDialog,
            },
            {
                label: "Edit",
                icon: "fa-solid fa-pen",
                command: openUpdateDialog,
            },
            {
                label: "Delete",
                icon: "fa-solid fa-trash",
                command: confirmDelete,
            },
        ],
    },
]);

const toggle = (event, booking) => {
    selectedBooking.value = null;
    selectedBooking.value = booking;
    menu.value.toggle(event);
};

const formatDate = (value) => {
    if (!value) return "-";

    return new Intl.DateTimeFormat("en-US", {
        year: "numeric",
        month: "short",
        day: "2-digit",
    }).format(new Date(value));
};
</script>

<template>
    <section class="space-y-5">
        <div
            class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
        >
            <div>
                <h1 class="mt-2 text-3xl font-semibold text-slate-950">
                    Bookings
                </h1>
                <p class="mt-2 text-sm text-slate-600">
                    Track tenant room bookings from pending request to completed
                    stay.
                </p>
            </div>

            <Button
                label="Add Booking"
                icon="fa-solid fa-calendar-plus"
                @click="openCreateDialog"
            />
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
            <div
                class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
            >
                <p class="text-sm text-slate-500">Total Bookings</p>
                <p class="mt-1 text-2xl font-semibold text-slate-950">
                    {{ summary.total }}
                </p>
            </div>
            <div
                class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
            >
                <p class="text-sm text-slate-500">Pending</p>
                <p class="mt-1 text-2xl font-semibold text-amber-700">
                    {{ summary.pending }}
                </p>
            </div>
            <div
                class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
            >
                <p class="text-sm text-slate-500">Active</p>
                <p class="mt-1 text-2xl font-semibold text-emerald-700">
                    {{ summary.active }}
                </p>
            </div>
            <div
                class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
            >
                <p class="text-sm text-slate-500">Completed</p>
                <p class="mt-1 text-2xl font-semibold text-slate-700">
                    {{ summary.completed }}
                </p>
            </div>
        </div>

        <section
            class="overflow-hidden rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
        >
            <div class="mb-4 flex flex-col gap-3 xl:flex-row xl:items-center">
                <IconField class="w-full xl:w-80">
                    <InputIcon class="fa-solid fa-magnifying-glass" />
                    <InputText
                        v-model="lazyParams.search"
                        class="w-full"
                        placeholder="Search tenant or room"
                        @input="onSearch"
                        @keydown.enter="onSearch"
                    />
                </IconField>

                <Select
                    v-model="lazyParams.status"
                    :options="statusOptions"
                    option-label="label"
                    option-value="value"
                    placeholder="Status"
                    class="w-full xl:w-44"
                    show-clear
                    @change="onSearch"
                />

                <Select
                    v-model="lazyParams.room_id"
                    :options="roomOptions"
                    option-label="label"
                    option-value="id"
                    placeholder="Room"
                    class="w-full xl:w-64"
                    filter
                    show-clear
                    @change="onSearch"
                />

                <Select
                    v-model="lazyParams.tenant_id"
                    :options="tenantOptions"
                    option-label="label"
                    option-value="id"
                    placeholder="Tenant"
                    class="w-full xl:w-64"
                    filter
                    show-clear
                    @change="onSearch"
                />

                <Button
                    icon="fa-solid fa-filter-circle-xmark"
                    severity="secondary"
                    outlined
                    rounded
                    @click="clearFilters"
                />
            </div>

            <DataTable
                :value="bookings"
                :loading="loading"
                lazy
                :paginator="bookings.length > 0"
                :first="lazyParams.first"
                :rows="lazyParams.rows"
                :total-records="lazyParams.totalRecords"
                :rows-per-page-options="[10, 25, 50]"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} bookings"
                data-key="id"
                responsive-layout="scroll"
                class="p-datatable-sm"
                striped-rows
                row-hover
                @page="onPage"
            >
                <template #empty>
                    <div
                        class="flex flex-col items-center justify-center py-16 text-center"
                    >
                        <i
                            class="fa-solid fa-calendar-xmark text-5xl text-slate-300"
                        ></i>
                        <p class="mt-4 text-lg font-semibold text-slate-800">
                            No bookings found
                        </p>
                        <p class="mt-1 text-sm text-slate-500">
                            Add a booking or adjust your filters.
                        </p>
                    </div>
                </template>

                <Column header="No" class="w-20">
                    <template #body="{ index }">
                        {{ lazyParams.first + index + 1 }}
                    </template>
                </Column>

                <Column header="Tenant">
                    <template #body="{ data }">
                        <div>
                            <p class="font-semibold text-slate-900">
                                {{ data.tenant?.name || "-" }}
                            </p>
                            <p class="text-sm text-slate-500">
                                {{
                                    data.tenant?.phone ||
                                    data.tenant?.email ||
                                    "-"
                                }}
                            </p>
                        </div>
                    </template>
                </Column>

                <Column header="Room">
                    <template #body="{ data }">
                        <div>
                            <p class="font-semibold text-slate-900">
                                Room {{ data.room?.room_number || "-" }}
                            </p>
                            <p class="text-sm text-slate-500">
                                {{
                                    data.room?.floor?.building?.building_name ||
                                    "-"
                                }}
                                <span v-if="data.room?.floor?.name">
                                    / {{ data.room.floor.name }}
                                </span>
                            </p>
                        </div>
                    </template>
                </Column>

                <Column header="Stay Dates">
                    <template #body="{ data }">
                        <p class="font-medium text-slate-800">
                            {{ formatDate(data.start_date) }}
                        </p>
                        <p class="text-sm text-slate-500">
                            to {{ formatDate(data.end_date) }}
                        </p>
                    </template>
                </Column>

                <Column header="Status">
                    <template #body="{ data }">
                        <Tag
                            :value="data.status"
                            :severity="statusSeverity[data.status] || 'info'"
                            rounded
                        />
                    </template>
                </Column>

                <Column header="Actions" class="w-20">
                    <template #body="{ data }">
                        <div class="flex justify-center">
                            <Button
                                icon="fa-solid fa-ellipsis"
                                severity="secondary"
                                text
                                rounded
                                aria-haspopup="true"
                                aria-controls="booking_menu"
                                @click="toggle($event, data)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <Menu
                ref="menu"
                id="booking_menu"
                :model="actionItems"
                :popup="true"
            />
        </section>
    </section>
</template>
