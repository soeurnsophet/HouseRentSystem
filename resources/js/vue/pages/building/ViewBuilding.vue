<script setup>
import { inject } from "vue";

const dialogRef = inject("dialogRef");
const building = dialogRef.value.data.building;
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between border-b pb-3">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">
                    {{ building?.building_name }}
                </h2>
                <p class="text-sm text-slate-500">ID #{{ building?.id }}</p>
            </div>

            <div
                class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600"
            >
                <i class="fa-solid fa-building text-xl"></i>
            </div>
        </div>

        <!-- Details -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="rounded-xl bg-slate-50 p-4">
                <p class="text-xs text-slate-500">Address</p>
                <p class="font-medium text-slate-900">
                    {{ building?.address || "-" }}
                </p>
            </div>

            <div class="rounded-xl bg-slate-50 p-4">
                <p class="text-xs text-slate-500">Phone</p>
                <p class="font-medium text-slate-900">
                    {{ building?.phone || "-" }}
                </p>
            </div>

            <div class="rounded-xl bg-slate-50 p-4">
                <p class="text-xs text-slate-500">Total Floors</p>
                <p class="font-bold text-slate-900">
                    {{ building?.floors_count || 0 }}
                </p>
            </div>

            <div class="rounded-xl bg-slate-50 p-4">
                <p class="text-xs text-slate-500">Total Rooms</p>
                <p class="font-bold text-slate-900">
                    {{ building?.rooms_count || 0 }}
                </p>
            </div>

            <div class="rounded-xl bg-green-50 p-4 md:col-span-2">
                <p class="text-xs text-green-600">Available Rooms</p>
                <p class="text-lg font-bold text-green-700">
                    {{ building?.available_rooms || 0 }}
                </p>
            </div>
        </div>
    </div>
</template>
