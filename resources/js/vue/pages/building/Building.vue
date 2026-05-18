<script setup>
import { computed, defineAsyncComponent, onMounted, ref } from "vue";
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
const { buildings, loading } = storeToRefs(buildingStore);
const search = ref("");

const filteredBuildings = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (!term) return buildings.value;

    return buildings.value.filter((building) => {
        return [building.building_name, building.address, building.phone]
            .filter(Boolean)
            .some((value) => value.toLowerCase().includes(term));
    });
});

const totals = computed(() => {
    return buildings.value.reduce(
        (summary, building) => {
            summary.floors += Number(building.floors_count || 0);
            summary.rooms += Number(building.rooms_count || 0);
            summary.available += Number(building.available_rooms || 0);
            return summary;
        },
        {
            floors: 0,
            rooms: 0,
            available: 0,
        },
    );
});

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
    <section class="space-y-5">
        <div
            class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
        >
            <div>
                <h1 class="mt-2 text-3xl font-semibold text-slate-950">
                    Buildings
                </h1>
                <p class="mt-2 text-sm text-slate-600">
                    Manage building profiles, locations, floors, and room
                    availability.
                </p>
            </div>

            <Button
                label="Add Building"
                icon="fa-solid fa-plus"
                @click="openCreateBuildingDialog"
            />
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Buildings</p>
                <p class="mt-2 text-2xl font-semibold text-slate-950">
                    {{ buildings.length }}
                </p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Floors</p>
                <p class="mt-2 text-2xl font-semibold text-teal-700">
                    {{ totals.floors }}
                </p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Rooms</p>
                <p class="mt-2 text-2xl font-semibold text-indigo-700">
                    {{ totals.rooms }}
                </p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Available</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-700">
                    {{ totals.available }}
                </p>
            </div>
        </div>

        <div
            class="flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:flex-row md:items-center md:justify-between"
        >
            <IconField class="w-full md:max-w-sm">
                <InputIcon class="fa-solid fa-magnifying-glass" />
                <InputText
                    v-model="search"
                    class="w-full"
                    placeholder="Search buildings"
                />
            </IconField>

            <span class="text-sm text-slate-500">
                Showing {{ filteredBuildings.length }} of
                {{ buildings.length }} buildings
            </span>
        </div>

        <div
            v-if="loading"
            class="flex flex-col items-center justify-center rounded-lg border border-slate-200 bg-white py-14 text-slate-500 shadow-sm"
        >
            <ProgressSpinner
                style="width: 44px; height: 44px"
                strokeWidth="5"
                fill="transparent"
                animationDuration=".5s"
                aria-label="Loading buildings"
            />
            <span class="mt-3 text-sm">Loading buildings...</span>
        </div>

        <div
            v-else-if="!filteredBuildings.length"
            class="flex flex-col items-center justify-center rounded-lg border border-slate-200 bg-white py-14 text-center shadow-sm"
        >
            <i class="fa-solid fa-building-circle-xmark text-5xl text-slate-300"></i>
            <p class="mt-4 text-lg font-semibold text-slate-800">
                No buildings found
            </p>
            <p class="mt-1 max-w-md text-sm text-slate-500">
                Add your first building or adjust your search.
            </p>
        </div>

        <div v-else class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            <article
                v-for="building in filteredBuildings"
                :key="building.id"
                class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition hover:border-teal-200 hover:shadow-md"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h2
                            class="truncate text-xl font-semibold text-slate-950"
                            :title="building.building_name"
                        >
                            {{ building.building_name }}
                        </h2>
                        <p class="mt-1 flex gap-2 text-sm text-slate-500">
                            <i class="fa-solid fa-location-dot mt-1 text-slate-400"></i>
                            <span class="line-clamp-2">
                                {{ building.address || "-" }}
                            </span>
                        </p>
                    </div>

                    <div
                        class="grid h-12 w-12 shrink-0 place-items-center rounded-lg bg-teal-50 text-xl text-teal-700"
                    >
                        <i class="fa-solid fa-building"></i>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-3 gap-3">
                    <div
                        class="rounded-lg border border-teal-100 bg-teal-50 p-3 text-center"
                    >
                        <p class="text-xs font-medium text-teal-700">Floors</p>

                        <h3 class="mt-1 text-lg font-semibold text-teal-800">
                            {{ building.floors_count || 0 }}
                        </h3>
                    </div>

                    <div
                        class="rounded-lg border border-indigo-100 bg-indigo-50 p-3 text-center"
                    >
                        <p class="text-xs font-medium text-indigo-700">Rooms</p>

                        <h3 class="mt-1 text-lg font-semibold text-indigo-800">
                            {{ building.rooms_count || 0 }}
                        </h3>
                    </div>

                    <div
                        class="rounded-lg border border-emerald-100 bg-emerald-50 p-3 text-center"
                    >
                        <p class="text-xs font-medium text-emerald-700">
                            Available
                        </p>

                        <h3 class="mt-1 text-lg font-semibold text-emerald-800">
                            {{ building.available_rooms || 0 }}
                        </h3>
                    </div>
                </div>

                <div
                    class="mt-4 flex items-center justify-between gap-3 border-t border-slate-100 pt-4"
                >
                    <span class="truncate text-sm font-medium text-slate-500">
                        <i class="fa-solid fa-phone mr-1 text-slate-400"></i>
                        {{ building.phone || "No phone" }}
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
            </article>
        </div>
    </section>
</template>
