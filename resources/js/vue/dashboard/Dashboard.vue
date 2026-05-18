<script setup>
import { onMounted, ref } from "vue";
import Header from "../components/Header.vue";
import Sidebar from "../components/Sidebar.vue";
import userAuthStore from "../stores/auth.store";

const sidebarOpen = ref(false);

const auth = userAuthStore();

onMounted(() => {
    // auth.logout();
});
</script>

<template>
    <div class="flex h-dvh overflow-hidden bg-slate-50 text-slate-950">
        <Sidebar :open="sidebarOpen" @close="sidebarOpen = false" />

        <div class="flex h-full min-h-0 min-w-0 flex-1 flex-col">
            <Header @toggle-sidebar="sidebarOpen = true" />

            <main class="dashboard-scroll min-h-0 flex-1 overflow-y-auto">
                <div class="mx-auto w-full max-w-8xl px-4 py-6 sm:px-6 lg:px-8">
                    <RouterView />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.dashboard-scroll {
    scrollbar-color: #14b8a6 #e2e8f0;
    scrollbar-width: thin;
}

.dashboard-scroll::-webkit-scrollbar {
    width: 10px;
}

.dashboard-scroll::-webkit-scrollbar-track {
    background: #e2e8f0;
}

.dashboard-scroll::-webkit-scrollbar-thumb {
    background-color: #14b8a6;
    border: 2px solid #e2e8f0;
    border-radius: 999px;
}

.dashboard-scroll::-webkit-scrollbar-thumb:hover {
    background-color: #0f766e;
}
</style>
