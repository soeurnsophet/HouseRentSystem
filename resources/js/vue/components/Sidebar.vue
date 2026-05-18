<script setup>
defineProps({
    open: {
        type: Boolean,
        default: false,
    },
});

defineEmits(["close"]);

const navigation = [
    {
        label: "Dashboard",
        icon: "fa-solid fa-gauge-high",
        to: { name: "dashboard" },
    },
    { label: "Users", icon: "fa-solid fa-user-group", to: { name: "users" } },
    {
        label: "Buildings",
        icon: "fa-solid fa-building",
        to: { name: "buildings" },
    },
    {
        label: "Floors",
        icon: "fa-solid fa-layer-group",
        to: { name: "floors" },
    },
    { label: "Rooms", icon: "fa-solid fa-bed", to: { name: "rooms" } },
    {
        label: "Bookings",
        icon: "fa-solid fa-calendar-check",
        to: { name: "bookings" },
    },
    // { label: "Payments", icon: "pi pi-wallet", to: "/payments" },
    // { label: "Reports", icon: "pi pi-file", to: "/reports" },
];
</script>

<template>
    <div>
        <button
            v-if="open"
            class="fixed inset-0 z-30 bg-slate-950/40 lg:hidden"
            type="button"
            aria-label="Close sidebar"
            @click="$emit('close')"
        ></button>

        <aside
            :class="[
                'fixed inset-y-0 left-0 z-40 min-h-screen flex w-72 flex-col border-r border-slate-200 bg-white transition-transform duration-200 lg:static lg:z-auto lg:translate-x-0',
                open ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <div
                class="flex h-16 items-center justify-between border-b border-slate-200 px-5"
            >
                <RouterLink
                    class="flex items-center gap-3"
                    :to="{ name: 'home' }"
                    @click="$emit('close')"
                >
                    <span
                        class="grid h-10 w-10 place-items-center rounded-md bg-teal-700 text-lg font-semibold text-white"
                    >
                        H
                    </span>
                    <span>
                        <span
                            class="block text-base font-semibold text-slate-950"
                            >HouseRent</span
                        >
                        <span
                            class="block text-xs font-medium uppercase tracking-wide text-slate-500"
                            >Management</span
                        >
                    </span>
                </RouterLink>

                <button
                    class="grid h-9 w-9 place-items-center rounded-md border border-slate-200 text-slate-600 lg:hidden"
                    type="button"
                    aria-label="Close sidebar"
                    @click="$emit('close')"
                >
                    <i class="pi pi-times"></i>
                </button>
            </div>

            <nav class="flex-1 space-y-1 px-3 py-5">
                <RouterLink
                    v-for="item in navigation"
                    :key="item.label"
                    :to="item.to"
                    class="flex h-11 items-center gap-3 rounded-md px-3 text-sm font-medium text-slate-600 transition hover:bg-teal-50 hover:text-teal-700"
                    exact-active-class="bg-teal-50 text-teal-700"
                    @click="$emit('close')"
                >
                    <i :class="[item.icon, 'text-base']"></i>
                    <span>{{ item.label }}</span>
                </RouterLink>
            </nav>
        </aside>
    </div>
</template>
