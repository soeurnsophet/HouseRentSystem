<script setup>
defineEmits(["toggle-sidebar"]);
</script>

<template>
    <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <div class="flex min-w-0 items-center gap-3">
                <button
                    class="inline-grid h-10 w-10 place-items-center rounded-md border border-slate-200 text-slate-700 transition hover:border-teal-300 hover:text-teal-700 lg:hidden"
                    type="button" aria-label="Open sidebar" @click="$emit('toggle-sidebar')">
                    <i class="pi pi-bars"></i>
                </button>

                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-slate-500">Welcome back</p>
                    <h2 class="truncate text-lg font-semibold text-slate-950">House Rent Dashboard</h2>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <label
                    class="hidden h-10 min-w-72 items-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-3 text-sm text-slate-500 md:flex">
                    <i class="pi pi-search"></i>
                    <input class="w-full bg-transparent text-slate-700 outline-none placeholder:text-slate-400"
                        placeholder="Search rooms, tenants, invoices" type="search" />
                </label>

                <button
                    class="grid h-10 w-10 place-items-center rounded-md border border-slate-200 text-slate-700 transition hover:border-teal-300 hover:text-teal-700"
                    type="button" aria-label="Notifications">
                    <i class="pi pi-bell"></i>
                </button>

                <div
                    class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-700 text-sm font-semibold text-white">
                    HR
                </div>
            </div>
        </div>
    </header>
</template>
