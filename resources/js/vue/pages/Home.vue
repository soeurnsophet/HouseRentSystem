<script setup>
const stats = [
    {
        label: "Total Rooms",
        value: "128",
        change: "+12 this month",
        icon: "pi pi-building",
        tone: "bg-teal-50 text-teal-700",
    },
    {
        label: "Occupied",
        value: "94",
        change: "73% occupancy",
        icon: "pi pi-key",
        tone: "bg-sky-50 text-sky-700",
    },
    {
        label: "Available",
        value: "34",
        change: "Ready to rent",
        icon: "pi pi-home",
        tone: "bg-emerald-50 text-emerald-700",
    },
    {
        label: "Monthly Revenue",
        value: "$18,420",
        change: "+8.4% vs last month",
        icon: "pi pi-wallet",
        tone: "bg-amber-50 text-amber-700",
    },
];

const activities = [
    {
        title: "Room B-204 booking confirmed",
        detail: "Sokha Chan paid deposit",
        time: "10 min ago",
    },
    {
        title: "Payment received",
        detail: "Invoice #HR-1029 marked paid",
        time: "1 hour ago",
    },
    {
        title: "Maintenance request opened",
        detail: "Air conditioner check for A-112",
        time: "Today",
    },
    {
        title: "New room added",
        detail: "C-301 is now available",
        time: "Yesterday",
    },
];

const quickActions = [
    { label: "Add Tenant", icon: "pi pi-user-plus" },
    { label: "New Booking", icon: "pi pi-calendar-plus" },
    { label: "Record Payment", icon: "pi pi-credit-card" },
];
</script>

<template>
    <section class="flex flex-col gap-6">
        <div
            class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
        >
            <div>
                <p
                    class="text-sm font-medium uppercase tracking-wide text-teal-700"
                >
                    Overview
                </p>
                <h1 class="mt-2 text-3xl font-semibold text-slate-950">
                    Dashboard
                </h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-600">
                    Track rooms, tenants, bookings, and payments from one clean
                    overview.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <button
                    v-for="action in quickActions"
                    :key="action.label"
                    class="inline-flex h-10 items-center gap-2 rounded-md border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm transition hover:border-teal-300 hover:text-teal-700"
                    type="button"
                >
                    <i :class="action.icon"></i>
                    <span>{{ action.label }}</span>
                </button>
            </div>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article
                v-for="stat in stats"
                :key="stat.label"
                class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            {{ stat.label }}
                        </p>
                        <p class="mt-3 text-3xl font-semibold text-slate-950">
                            {{ stat.value }}
                        </p>
                    </div>
                    <span
                        :class="[
                            'grid h-11 w-11 place-items-center rounded-md text-lg',
                            stat.tone,
                        ]"
                    >
                        <i :class="stat.icon"></i>
                    </span>
                </div>
                <p class="mt-4 text-sm text-slate-500">{{ stat.change }}</p>
            </article>
        </section>

        <section class="grid gap-6 lg:grid-cols-[1.25fr_0.75fr]">
            <article
                class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950">
                            Occupancy Overview
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Current rental status across all rooms.
                        </p>
                    </div>
                    <span
                        class="rounded-md bg-teal-50 px-3 py-1 text-sm font-medium text-teal-700"
                        >73%</span
                    >
                </div>

                <div class="mt-6">
                    <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                        <div
                            class="h-full w-[73%] rounded-full bg-teal-600"
                        ></div>
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-md bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Occupied</p>
                            <p
                                class="mt-1 text-xl font-semibold text-slate-950"
                            >
                                94 rooms
                            </p>
                        </div>
                        <div class="rounded-md bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Available</p>
                            <p
                                class="mt-1 text-xl font-semibold text-slate-950"
                            >
                                34 rooms
                            </p>
                        </div>
                        <div class="rounded-md bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">
                                Pending Move-in
                            </p>
                            <p
                                class="mt-1 text-xl font-semibold text-slate-950"
                            >
                                8 rooms
                            </p>
                        </div>
                    </div>
                </div>
            </article>

            <article
                class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
            >
                <h2 class="text-lg font-semibold text-slate-950">
                    Recent Activity
                </h2>
                <div class="mt-5 space-y-4">
                    <div
                        v-for="item in activities"
                        :key="item.title"
                        class="flex gap-3"
                    >
                        <span
                            class="mt-1 h-2.5 w-2.5 rounded-full bg-teal-600"
                        ></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-900">
                                {{ item.title }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ item.detail }}
                            </p>
                        </div>
                        <span class="shrink-0 text-xs text-slate-400">{{
                            item.time
                        }}</span>
                    </div>
                </div>
            </article>
        </section>
    </section>
</template>
