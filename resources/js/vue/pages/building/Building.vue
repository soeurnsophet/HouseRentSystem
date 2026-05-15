<script setup>
import { computed, defineAsyncComponent, onMounted } from "vue";
import useBuildingStore from "../../stores/building.store";
import { storeToRefs } from "pinia";
import { useConfirm, useDialog, useToast } from "primevue";
import { MessageSuccess } from "../../utils/Message";

const CreateBuilding = defineAsyncComponent(
    () => import("./CreateBuilding.vue"),
);
const UpdateBuilding = defineAsyncComponent(
    () => import("./UpdateBuilding.vue"),
);
const ViewBuilding = defineAsyncComponent(() => import("./ViewBuilding.vue"));
// dialog
const dialog = useDialog();
const confirm = useConfirm();
const toast = useToast();
// store
const buildingStore = useBuildingStore();
const buildings = computed(() => buildingStore.buildings);

// mounted
onMounted(async () => {
    await buildingStore.fetchBuildings();
});

// create dialog
const openCreateBuildingDialog = () => {
    dialog.open(CreateBuilding, {
        props: {
            position: "top",
            header: "Create New Building",
            modal: true,
            style: {
                width: "42rem",
            },
            draggable: false,
            breakpoints: {
                "960px": "75vw",
                "640px": "92vw",
            },
        },
        onClose: async (options) => {
            if (options?.data?.created) {
                await buildingStore.fetchBuildings();
            }
        },
    });
};

// update dialog
const openUpdateBuildingDialog = (building) => {
    dialog.open(UpdateBuilding, {
        data: {
            building,
        },
        props: {
            position: "top",
            header: "Update Building",
            modal: true,
            style: {
                width: "42rem",
            },
            breakpoints: {
                "960px": "75vw",
                "640px": "92vw",
            },
        },
        onClose: async (options) => {
            if (options?.data?.updated) {
                await buildingStore.fetchBuildings();
            }
        },
    });
};

const openViewBuildingDialog = (building) => {
    dialog.open(ViewBuilding, {
        data: {
            building,
        },
        props: {
            position: "top",
            header: "Building Details",
            modal: true,
            style: {
                width: "42rem",
            },
            breakpoints: {
                "960px": "75vw",
                "640px": "92vw",
            },
        },
    });
};

// delete
const confirmDelete = async (id) => {
    confirm.require({
        message: "Are you sure you want to delete this building?",
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
            try {
                const res = await buildingStore.deleteBuilding(id);

                MessageSuccess("", res.message, toast);

                await buildingStore.fetchBuildings();
            } catch (error) {
                console.error(error);
            }
        },
    });
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Buildings</h1>

                <p class="mt-1 text-sm text-slate-500">
                    Manage all building information.
                </p>
            </div>

            <Button
                label="Add Building"
                icon="fa-solid fa-plus"
                @click="openCreateBuildingDialog"
            />
        </div>
        <!-- loading -->
        <div
            class="card flex flex-col items-center justify-center"
            v-if="!buildings.length"
        >
            <ProgressSpinner
                style="width: 50px; height: 50px"
                strokeWidth="5"
                fill="transparent"
                animationDuration=".5s"
                aria-label="Custom ProgressSpinner"
            />
            <span class="mt-2">Loading...</span>
        </div>
        <!-- Card List -->
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            <div
                v-for="building in buildings"
                :key="building.id"
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <!-- Top -->
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">
                            {{ building.building_name }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            <i
                                class="fa-solid fa-location-dot mr-1 text-slate-400"
                            ></i>
                            {{ building.address }}
                        </p>
                    </div>

                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-xl text-blue-600"
                    >
                        <i class="fa-solid fa-building"></i>
                    </div>
                </div>

                <!-- Info -->
                <div class="mt-5 grid grid-cols-3 gap-3">
                    <!-- Floors -->
                    <div
                        class="rounded-xl bg-blue-50 p-3 text-center border border-blue-100"
                    >
                        <p class="text-xs text-blue-600">Floors</p>

                        <h3 class="mt-1 text-lg font-bold text-blue-700">
                            {{ building.floors_count || 0 }}
                        </h3>
                    </div>

                    <!-- Rooms -->
                    <div
                        class="rounded-xl bg-purple-50 p-3 text-center border border-purple-100"
                    >
                        <p class="text-xs text-purple-600">Rooms</p>

                        <h3 class="mt-1 text-lg font-bold text-purple-700">
                            {{ building.rooms_count || 0 }}
                        </h3>
                    </div>

                    <!-- Available Rooms -->
                    <div
                        class="rounded-xl bg-emerald-50 p-3 text-center border border-emerald-100"
                    >
                        <p class="text-xs text-emerald-600">Available Rooms</p>

                        <h3 class="mt-1 text-lg font-bold text-emerald-700">
                            {{ building.available_rooms || 0 }}
                        </h3>
                    </div>
                </div>

                <!-- Footer -->
                <div
                    class="mt-3 flex items-center justify-between border-t border-slate-100 pt-4"
                >
                    <span class="text-sm font-medium text-slate-500">
                        Building ID #{{ building.id }}
                    </span>

                    <div class="flex gap-2">
                        <Button
                            icon="fa-solid fa-pen"
                            severity="secondary"
                            rounded
                            text
                            @click="openUpdateBuildingDialog(building)"
                        />

                        <Button
                            icon="fa-solid fa-eye"
                            rounded
                            text
                            @click="openViewBuildingDialog(building)"
                        />
                        <Button
                            icon="fa-solid fa-trash"
                            severity="danger"
                            rounded
                            text
                            @click="confirmDelete(building.id)"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
