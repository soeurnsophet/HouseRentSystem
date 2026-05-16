<script setup>
import { onMounted, reactive, ref } from "vue";
import useFloorStore from "../../stores/floor.store";
import { storeToRefs } from "pinia";
import { useConfirm, useDialog, useToast } from "primevue";
import CreateFloor from "./CreateFloor.vue";
import UpdateFloor from "./UpdateFloor.vue";
import { MessageSuccess } from "../../utils/Message";

const floorStore = useFloorStore();
const { floors, meta, loading } = storeToRefs(floorStore);
const menu = ref();
const selectedFloor = ref(null);

const dialog = useDialog();
const confirm = useConfirm();
const toast = useToast();

const lazyParams = reactive({
    first: 0,
    page: 0,
    rows: 10,
    totalRecords: 0,
});

const fetchFloors = async () => {
    await floorStore.fetchFloors({
        page: lazyParams.page + 1,
        per_page: lazyParams.rows,
    });

    lazyParams.totalRecords = meta.value.total || 0;
};

const onPage = (event) => {
    lazyParams.first = event.first;
    lazyParams.page = event.page;
    lazyParams.rows = event.rows;

    fetchFloors();
};

onMounted(fetchFloors);

const openCreateDialog = () => {
    dialog.open(CreateFloor, {
        props: {
            position: "top",
            header: "Add Floor",
            modal: true,
            style: {
                width: "42rem",
            },
            breakpoints: {
                "960px": "75vw",
                "640px": "92vw",
            },
            draggable: false,
        },
        onClose: (options) => {
            if (options?.data?.created) {
                fetchFloors();
            }
        },
    });
};

const openUpdateDialog = () => {
    if (!selectedFloor.value) return;

    dialog.open(UpdateFloor, {
        data: {
            floor: selectedFloor.value,
        },
        props: {
            position: "top",
            header: "Update Floor",
            modal: true,
            style: {
                width: "42rem",
            },
            breakpoints: {
                "960px": "75vw",
                "640px": "92vw",
            },
            draggable: false,
        },
        onClose: (options) => {
            if (options?.data?.updated) {
                fetchFloors();
            }
        },
    });
};

const confirmDelete = () => {
    if (!selectedFloor.value) return;

    confirm.require({
        message: `Are you sure you want to delete ${selectedFloor.value.name}?`,
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
            const res = await floorStore.deleteFloor(selectedFloor.value.id);
            MessageSuccess("", res.message, toast);

            if (floors.value.length === 1 && lazyParams.page > 0) {
                lazyParams.page -= 1;
                lazyParams.first = lazyParams.page * lazyParams.rows;
            }

            await fetchFloors();
        },
    });
};

// Menu
const actionsItems = ref([
    {
        label: "Actions",
        items: [
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

const toggle = (event, floor) => {
    selectedFloor.value = floor;
    menu.value.toggle(event);
};
</script>
<template>
    <section class="space-y-5">
        <div
            class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
        >
            <div>
                <h1 class="mt-2 text-3xl font-semibold text-slate-950">
                    Floors
                </h1>
                <p class="mt-2 text-sm text-slate-600">
                    Load floors page by page and manage accounts.
                </p>
            </div>

            <Button
                label="Add Floor"
                icon="fa-solid fa-plus"
                @click="openCreateDialog"
            />
        </div>
        <!-- Table -->
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="mb-4 flex justify-end">
                <span class="text-sm text-slate-500">
                    Showing {{ floors.length }} of
                    {{ lazyParams.totalRecords }} floors
                </span>
            </div>

            <DataTable
                :value="floors"
                :loading="loading"
                lazy
                :paginator="floors.length > 0"
                :first="lazyParams.first"
                :rows="lazyParams.rows"
                :total-records="lazyParams.totalRecords"
                :rows-per-page-options="[10, 25, 50]"
                data-key="id"
                striped-rows
                responsive-layout="scroll"
                class="p-datatable-sm"
                @page="onPage"
            >
                <template #empty>
                    <div
                        class="flex flex-col items-center justify-center py-10 text-slate-500"
                    >
                        <i
                            class="fa-solid fa-box-open text-5xl mb-3 text-slate-300"
                        ></i>

                        <p class="text-lg font-semibold text-slate-700">
                            No floors found
                        </p>
                    </div>
                </template>
                <Column header="No" class="w-20">
                    <template #body="{ index }">
                        {{ lazyParams.first + index + 1 }}
                    </template>
                </Column>
                <Column field="name" header="Name" />
                <Column header="Building">
                    <template #body="{ data }">
                        {{ data.building?.building_name || "-" }}
                    </template>
                </Column>
                <Column field="base_price" header="Base Price" />
                <Column field="rooms_count" header="Rooms" />
                <Column field="description" header="Description">
                    <template #body="{ data }">
                        {{ data.description || "-" }}
                    </template>
                </Column>

                <Column header="Actions" class="w-8">
                    <template #body="{ data }">
                        <div class="flex justify-center">
                            <Button
                                icon="fa-solid fa-ellipsis"
                                severity="info"
                                text
                                rounded
                                @click="toggle($event, data)"
                                aria-haspopup="true"
                                aria-controls="overlay_menu"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
            <Menu
                ref="menu"
                id="overlay_menu"
                :model="actionsItems"
                :popup="true"
            />
        </div>
    </section>
</template>
