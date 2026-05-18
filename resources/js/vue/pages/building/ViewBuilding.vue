<script setup>
import { computed, inject } from "vue";

const dialogRef = inject("dialogRef");
const building = dialogRef.value.data.building;

const occupancyPercent = computed(() => {
    const totalRooms = Number(building?.rooms_count || 0);
    const availableRooms = Number(building?.available_rooms || 0);

    if (!totalRooms) return 0;

    return Math.max(
        0,
        Math.min(100, Math.round(((totalRooms - availableRooms) / totalRooms) * 100)),
    );
});
</script>

<template>
    <div class="space-y-5">
        <div
            class="flex items-center justify-between gap-4 rounded-lg border border-slate-200 bg-white p-4"
        >
            <div class="min-w-0">
                <h2 class="truncate text-2xl font-semibold text-slate-950">
                    {{ building?.building_name }}
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Building ID #{{ building?.id }}
                </p>
            </div>

            <div
                class="grid h-12 w-12 shrink-0 place-items-center rounded-lg bg-teal-50 text-teal-700"
            >
                <i class="fa-solid fa-building text-xl"></i>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-medium uppercase text-slate-500">
                    Address
                </p>
                <p class="mt-2 font-medium text-slate-900">
                    {{ building?.address || "-" }}
                </p>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-medium uppercase text-slate-500">
                    Phone
                </p>
                <p class="mt-2 font-medium text-slate-900">
                    {{ building?.phone || "-" }}
                </p>
            </div>

            <div class="rounded-lg border border-teal-100 bg-teal-50 p-4">
                <p class="text-xs font-medium uppercase text-teal-700">
                    Total Floors
                </p>
                <p class="mt-2 text-2xl font-semibold text-teal-800">
                    {{ building?.floors_count || 0 }}
                </p>
            </div>

            <div class="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                <p class="text-xs font-medium uppercase text-indigo-700">
                    Total Rooms
                </p>
                <p class="mt-2 text-2xl font-semibold text-indigo-800">
                    {{ building?.rooms_count || 0 }}
                </p>
            </div>

            <div
                class="rounded-lg border border-emerald-100 bg-emerald-50 p-4 md:col-span-2"
            >
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-medium uppercase text-emerald-700">
                            Available Rooms
                        </p>
                        <p class="mt-2 text-2xl font-semibold text-emerald-800">
                            {{ building?.available_rooms || 0 }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-medium uppercase text-slate-500">
                            Occupied
                        </p>
                        <p class="mt-2 text-2xl font-semibold text-slate-950">
                            {{ occupancyPercent }}%
                        </p>
                    </div>
                </div>
                <div class="mt-4 h-2 overflow-hidden rounded-full bg-white">
                    <div
                        class="h-full rounded-full bg-emerald-600"
                        :style="{ width: `${occupancyPercent}%` }"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</template>
