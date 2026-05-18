<script setup>
import { computed, defineAsyncComponent, onMounted, reactive, ref } from "vue";
import { storeToRefs } from "pinia";
import { useConfirm, useDialog, useToast } from "primevue";
import useRoomStore from "../../stores/room.store";
import useRoomTypeStore from "../../stores/room-type.store";
import { MessageSuccess } from "../../utils/Message";
import useBuildingStore from "../../stores/building.store";

const CreateRoom = defineAsyncComponent(() => import("./CreateRoom.vue"));
const UpdateRoom = defineAsyncComponent(() => import("./UpdateRoom.vue"));
const CreateRoomType = defineAsyncComponent(
    () => import("./CreateRoomType.vue"),
);
const UpdateRoomType = defineAsyncComponent(
    () => import("./UpdateRoomType.vue"),
);

const roomStore = useRoomStore();
const roomTypeStore = useRoomTypeStore();
const builingStore = useBuildingStore();
const { buildings } = storeToRefs(builingStore);
const { rooms, meta: roomMeta, loading: roomsLoading } = storeToRefs(roomStore);
const {
    roomTypes,
    meta: roomTypeMeta,
    loading: roomTypesLoading,
} = storeToRefs(roomTypeStore);

const dialog = useDialog();
const confirm = useConfirm();
const toast = useToast();
const activeTab = ref("rooms");
const roomMenu = ref();
const roomTypeMenu = ref();
const selectedRoom = ref(null);
const selectedRoomType = ref(null);

const roomLazyParams = reactive({
    first: 0,
    page: 0,
    rows: 10,
    status: null,
    search: "",
    building_id: null,
    totalRecords: 0,
});

const roomTypeLazyParams = reactive({
    first: 0,
    page: 0,
    rows: 10,
    search: "",
    totalRecords: 0,
});

const statusSeverity = {
    available: "success",
    occupied: "danger",
    maintenance: "warn",
    reserved: "info",
};

const roomSummary = computed(() => ({
    total: roomLazyParams.totalRecords,
    available: rooms.value.filter((room) => room.status === "available").length,
    occupied: rooms.value.filter((room) => room.status === "occupied").length,
}));

const fetchRooms = async () => {
    await roomStore.fetchRooms({
        page: roomLazyParams.page + 1,
        per_page: roomLazyParams.rows,
        status: roomLazyParams.status,
        building_id: roomLazyParams.building_id,
        search: roomLazyParams.search || undefined,
    });

    roomLazyParams.totalRecords = roomMeta.value.total_rooms || 0;
};

let searchTimer;
const onSearchRoom = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        roomLazyParams.first = 0;
        roomLazyParams.page = 0;
        fetchRooms();
    }, 300);
};

const fetchRoomTypes = async () => {
    await roomTypeStore.fetchRoomTypes({
        page: roomTypeLazyParams.page + 1,
        per_page: roomTypeLazyParams.rows,
        search: roomTypeLazyParams.search || undefined,
    });

    roomTypeLazyParams.totalRecords = roomTypeMeta.value.total_room_types || 0;
};
const onSearchRoomType = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        roomTypeLazyParams.first = 0;
        roomTypeLazyParams.page = 0;
        fetchRoomTypes();
    }, 300);
};

const refreshAll = async () => {
    await Promise.all([
        fetchRooms(),
        fetchRoomTypes(),
        builingStore.fetchBuildings(),
    ]);
};

onMounted(refreshAll);
console.log(buildings);

const onRoomPage = (event) => {
    roomLazyParams.first = event.first;
    roomLazyParams.page = event.page;
    roomLazyParams.rows = event.rows;
    fetchRooms();
};

const onRoomTypePage = (event) => {
    roomTypeLazyParams.first = event.first;
    roomTypeLazyParams.page = event.page;
    roomTypeLazyParams.rows = event.rows;
    fetchRoomTypes();
};

const openCreateRoomDialog = () => {
    dialog.open(CreateRoom, {
        props: {
            position: "top",
            header: "Add Room",
            modal: true,
            style: { width: "42rem" },
            breakpoints: { "960px": "75vw", "640px": "92vw" },
            draggable: false,
        },
        onClose: (options) => {
            if (options?.data?.created) {
                fetchRooms();
            }
        },
    });
};

const openUpdateRoomDialog = () => {
    if (!selectedRoom.value) return;

    dialog.open(UpdateRoom, {
        data: { room: selectedRoom.value },
        props: {
            position: "top",
            header: "Update Room",
            modal: true,
            style: { width: "42rem" },
            breakpoints: { "960px": "75vw", "640px": "92vw" },
            draggable: false,
        },
        onClose: (options) => {
            if (options?.data?.updated) {
                fetchRooms();
            }
        },
    });
};

const openCreateRoomTypeDialog = () => {
    dialog.open(CreateRoomType, {
        props: {
            position: "top",
            header: "Add Room Type",
            modal: true,
            style: { width: "42rem" },
            breakpoints: { "960px": "75vw", "640px": "92vw" },
            draggable: false,
        },
        onClose: (options) => {
            if (options?.data?.created) {
                fetchRoomTypes();
            }
        },
    });
};

const openUpdateRoomTypeDialog = () => {
    if (!selectedRoomType.value) return;

    dialog.open(UpdateRoomType, {
        data: { roomType: selectedRoomType.value },
        props: {
            position: "top",
            header: "Update Room Type",
            modal: true,
            style: { width: "42rem" },
            breakpoints: { "960px": "75vw", "640px": "92vw" },
            draggable: false,
        },
        onClose: (options) => {
            if (options?.data?.updated) {
                fetchRoomTypes();
                fetchRooms();
            }
        },
    });
};

const confirmDeleteRoom = () => {
    if (!selectedRoom.value) return;

    confirm.require({
        message: `Are you sure you want to delete room ${selectedRoom.value.room_number}?`,
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
            const res = await roomStore.deleteRoom(selectedRoom.value.id);
            MessageSuccess("", res.message, toast);

            if (rooms.value.length === 1 && roomLazyParams.page > 0) {
                roomLazyParams.page -= 1;
                roomLazyParams.first =
                    roomLazyParams.page * roomLazyParams.rows;
            }

            await fetchRooms();
        },
    });
};

const confirmDeleteRoomType = () => {
    if (!selectedRoomType.value) return;

    confirm.require({
        message: `Are you sure you want to delete ${selectedRoomType.value.type_name}?`,
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
            const res = await roomTypeStore.deleteRoomType(
                selectedRoomType.value.id,
            );
            MessageSuccess("", res.message, toast);

            if (roomTypes.value.length === 1 && roomTypeLazyParams.page > 0) {
                roomTypeLazyParams.page -= 1;
                roomTypeLazyParams.first =
                    roomTypeLazyParams.page * roomTypeLazyParams.rows;
            }

            await refreshAll();
        },
    });
};

const roomActionItems = ref([
    {
        label: "Actions",
        items: [
            {
                label: "Edit",
                icon: "fa-solid fa-pen",
                command: openUpdateRoomDialog,
            },
            {
                label: "Delete",
                icon: "fa-solid fa-trash",
                command: confirmDeleteRoom,
            },
        ],
    },
]);

const roomTypeActionItems = ref([
    {
        label: "Actions",
        items: [
            {
                label: "Edit",
                icon: "fa-solid fa-pen",
                command: openUpdateRoomTypeDialog,
            },
            {
                label: "Delete",
                icon: "fa-solid fa-trash",
                command: confirmDeleteRoomType,
            },
        ],
    },
]);

const toggleRoomMenu = (event, room) => {
    selectedRoom.value = room;
    roomMenu.value.toggle(event);
};

const toggleRoomTypeMenu = (event, roomType) => {
    selectedRoomType.value = roomType;
    roomTypeMenu.value.toggle(event);
};
const statusOptions = [
    { label: "Available", value: "available" },
    { label: "Occupied", value: "occupied" },
    { label: "Maintenance", value: "maintenance" },
    { label: "Reserved", value: "reserved" },
];
const clearFiltersRoom = () => {
    roomLazyParams.status = null;
    roomLazyParams.search = "";
    roomLazyParams.building_id = null;
    refreshAll();
};
const clearFiltersRoomType = () => {
    roomTypeLazyParams.search = "";
    refreshAll();
};
</script>

<template>
    <section class="space-y-5">
        <div
            class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
        >
            <div>
                <h1 class="mt-2 text-3xl font-semibold text-slate-950">
                    Rooms
                </h1>
                <p class="mt-2 text-sm text-slate-600">
                    Manage rooms, room availability, and room type definitions.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <Button
                    v-if="activeTab === 'rooms'"
                    label="Add Room"
                    icon="fa-solid fa-plus"
                    @click="openCreateRoomDialog"
                />
                <Button
                    v-else
                    label="Add Room Type"
                    icon="fa-solid fa-plus"
                    @click="openCreateRoomTypeDialog"
                />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <div
                class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
            >
                <p class="text-sm text-slate-500">Total Rooms</p>
                <p class="mt-1 text-2xl font-semibold text-slate-950">
                    {{ roomSummary.total }}
                </p>
            </div>
            <div
                class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
            >
                <p class="text-sm text-slate-500">Available This Page</p>
                <p class="mt-1 text-2xl font-semibold text-emerald-700">
                    {{ roomSummary.available }}
                </p>
            </div>
            <div
                class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
            >
                <p class="text-sm text-slate-500">Room Types</p>
                <p class="mt-1 text-2xl font-semibold text-teal-700">
                    {{ roomTypeLazyParams.totalRecords }}
                </p>
            </div>
        </div>
        <!-- Tabs -->
        <div
            class="flex w-fit rounded-lg border gap-1 border-slate-200 bg-white p-1 shadow-sm"
        >
            <Button
                label="Rooms"
                icon="fa-solid fa-bed"
                :severity="activeTab === 'rooms' ? 'primary' : 'secondary'"
                :outlined="activeTab !== 'rooms'"
                size="small"
                @click="activeTab = 'rooms'"
            />
            <Button
                label="Room Types"
                icon="fa-solid fa-tags"
                :severity="activeTab === 'types' ? 'primary' : 'secondary'"
                :outlined="activeTab !== 'types'"
                size="small"
                @click="activeTab = 'types'"
            />
        </div>
        <!-- Rooms Section -->
        <section
            v-if="activeTab === 'rooms'"
            class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm p-3"
        >
            <!-- Header -->
            <div class="mb-3">
                <div
                    class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                >
                    <!-- <div>
                        <h2 class="text-xl font-bold text-slate-800">
                            Room Management
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Manage rooms, availability, and room information.
                        </p>
                    </div> -->

                    <!-- Filters -->
                    <div class="flex flex-col gap-3 lg:flex-row">
                        <!-- Search -->
                        <IconField class="w-full lg:w-80">
                            <InputIcon class="pi pi-search" />

                            <InputText
                                v-model="roomLazyParams.search"
                                class="w-full rounded-xl"
                                placeholder="Search room number..."
                                @input="onSearchRoom"
                                @keydown.enter="onSearchRoom"
                            />
                        </IconField>

                        <!-- Building -->
                        <Select
                            v-model="roomLazyParams.building_id"
                            :options="buildings"
                            option-label="building_name"
                            option-value="id"
                            placeholder="Select Building"
                            class="w-full lg:w-52"
                            @change="onSearchRoom"
                            show-clear
                        >
                            <template #dropdownicon>
                                <i
                                    class="fa-solid fa-building text-slate-400"
                                ></i>
                            </template>
                        </Select>

                        <!-- Status -->
                        <Select
                            v-model="roomLazyParams.status"
                            :options="statusOptions"
                            option-label="label"
                            option-value="value"
                            placeholder="Room Status"
                            class="w-full lg:w-48"
                            @change="onSearchRoom"
                            show-clear
                        >
                            <template #dropdownicon>
                                <i
                                    class="fa-solid fa-circle-check text-slate-400"
                                ></i>
                            </template>
                        </Select>

                        <!-- Clear -->
                        <Button
                            icon="fa-solid fa-filter-circle-xmark"
                            severity="secondary"
                            outlined
                            rounded
                            @click="clearFiltersRoom"
                        />
                    </div>
                </div>
            </div>

            <!-- Table -->
            <DataTable
                :value="rooms"
                :loading="roomsLoading"
                lazy
                :paginator="rooms.length > 0"
                :first="roomLazyParams.first"
                :rows="roomLazyParams.rows"
                :total-records="roomLazyParams.totalRecords"
                :rows-per-page-options="[10, 25, 50]"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} rooms"
                data-key="id"
                responsive-layout="scroll"
                class="p-datatable-sm"
                @page="onRoomPage"
                row-hover
                striped-rows
                show-gridlines
            >
                <!-- Empty -->
                <template #empty>
                    <div
                        class="flex flex-col items-center justify-center py-20"
                    >
                        <div
                            class="mb-5 flex h-24 w-24 items-center justify-center rounded-full bg-slate-100"
                        >
                            <i
                                class="fa-solid fa-bed text-5xl text-slate-300"
                            ></i>
                        </div>

                        <h3 class="text-lg font-semibold text-slate-700">
                            No Rooms Found
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Try changing your search or filters.
                        </p>
                    </div>
                </template>

                <Column header="" class="w-16">
                    <template #body="{ index }">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-700"
                        >
                            {{ roomLazyParams.first + index + 1 }}
                        </div>
                    </template>
                </Column>

                <Column header="Room">
                    <template #body="{ data }">
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100"
                            >
                                <i class="fa-solid fa-bed text-blue-600"></i>
                            </div>

                            <div>
                                <p class="font-semibold text-slate-800">
                                    {{ data.room_number }}
                                </p>

                                <div
                                    class="mt-1 flex items-center gap-3 text-sm text-slate-500"
                                >
                                    <span class="flex items-center gap-1">
                                        <i
                                            class="fa-solid fa-building text-slate-400"
                                        ></i>
                                        {{
                                            data.floor?.building
                                                ?.building_name || "-"
                                        }}
                                    </span>

                                    <span class="flex items-center gap-1">
                                        <i
                                            class="fa-solid fa-layer-group text-slate-400"
                                        ></i>

                                        {{ data.floor?.name || "-" }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>
                </Column>

                <Column header="Room Type">
                    <template #body="{ data }">
                        {{ data.room_type?.type_name || "-" }}
                    </template>
                </Column>

                <Column header="Status">
                    <template #body="{ data }">
                        <Tag
                            :value="data.status"
                            :severity="
                                statusSeverity[data.status] || 'secondary'
                            "
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
                                aria-controls="room_menu"
                                class="hover:bg-slate-100"
                                @click="toggleRoomMenu($event, data)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <Menu
                ref="roomMenu"
                id="room_menu"
                :model="roomActionItems"
                :popup="true"
            />
        </section>
        <!-- Room Types Section -->
        <section
            v-else
            class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm p-3"
        >
            <!-- Header -->
            <div class="mb-3">
                <div
                    class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                >
                    <!-- Left
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">
                            Room Types
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Manage and organize room categories for your
                            property system.
                        </p>
                    </div> -->

                    <!-- Right -->
                    <div class="flex items-center gap-3">
                        <!-- Search -->
                        <IconField class="w-full lg:w-80">
                            <InputIcon class="pi pi-search" />

                            <InputText
                                v-model="roomTypeLazyParams.search"
                                class="w-full rounded-xl"
                                placeholder="Search room types..."
                                @input="onSearchRoomType"
                                @keydown.enter="onSearchRoomType"
                            />
                        </IconField>

                        <!-- Clear -->
                        <Button
                            v-if="roomTypeLazyParams.search"
                            icon="fa-solid fa-filter-circle-xmark"
                            severity="secondary"
                            outlined
                            rounded
                            @click="clearFiltersRoomType"
                        />
                    </div>
                </div>
            </div>

            <!-- Table -->
            <DataTable
                :value="roomTypes"
                :loading="roomTypesLoading"
                lazy
                :paginator="roomTypes.length > 0"
                :first="roomTypeLazyParams.first"
                :rows="roomTypeLazyParams.rows"
                :total-records="roomTypeLazyParams.totalRecords"
                :rows-per-page-options="[10, 25, 50]"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                data-key="id"
                responsive-layout="scroll"
                class="p-datatable-sm"
                @page="onRoomTypePage"
                row-hover
                striped-rows
                show-gridlines
            >
                <!-- Empty -->
                <template #empty>
                    <div
                        class="flex flex-col items-center justify-center py-20"
                    >
                        <div
                            class="mb-5 flex h-24 w-24 items-center justify-center rounded-full bg-slate-100"
                        >
                            <i
                                class="fa-solid fa-tags text-5xl text-slate-300"
                            ></i>
                        </div>

                        <h3 class="text-lg font-semibold text-slate-700">
                            No Room Types Found
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Try changing your search keyword.
                        </p>
                    </div>
                </template>

                <!-- No -->
                <Column header="" class="w-16">
                    <template #body="{ index }">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-700"
                        >
                            {{ roomTypeLazyParams.first + index + 1 }}
                        </div>
                    </template>
                </Column>

                <Column header="Room Type">
                    <template #body="{ data }">
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100"
                            >
                                <i class="fa-solid fa-tags text-violet-600"></i>
                            </div>

                            <div>
                                <p class="font-semibold text-slate-800">
                                    {{ data.type_name }}
                                </p>

                                <p
                                    class="mt-1 text-sm text-slate-500 line-clamp-1"
                                >
                                    {{
                                        data.description ||
                                        "No description provided"
                                    }}
                                </p>
                            </div>
                        </div>
                    </template>
                </Column>

                <Column header="Rooms" class="w-36">
                    <template #body="{ data }">
                        <div class="flex justify-center">
                            <div
                                class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1.5"
                            >
                                <i
                                    class="fa-solid fa-bed text-xs text-blue-500"
                                ></i>

                                <span
                                    class="text-sm font-semibold text-blue-700"
                                >
                                    {{ data.rooms_count ?? 0 }}
                                </span>
                            </div>
                        </div>
                    </template>
                </Column>

                <!-- Actions -->
                <Column header="" class="w-20">
                    <template #body="{ data }">
                        <div class="flex justify-center">
                            <Button
                                icon="fa-solid fa-ellipsis"
                                severity="secondary"
                                text
                                rounded
                                aria-haspopup="true"
                                aria-controls="room_type_menu"
                                class="hover:bg-slate-100"
                                @click="toggleRoomTypeMenu($event, data)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <!-- Menu -->
            <Menu
                ref="roomTypeMenu"
                id="room_type_menu"
                :model="roomTypeActionItems"
                :popup="true"
            />
        </section>
    </section>
</template>
