<script setup>
import { Toast } from "primevue";
</script>
<template>
    <ConfirmDialog></ConfirmDialog>
    <Toast />
    <DynamicDialog />
    <router-view />
</template>
<style>
body {
    --sb-track-color: #232e33;
    --sb-thumb-color: #6baf8d;
    --sb-size: 7px;
}

body::-webkit-scrollbar {
    width: var(--sb-size);
}

body::-webkit-scrollbar-track {
    background: var(--sb-track-color);
    border-radius: 3px;
}

body::-webkit-scrollbar-thumb {
    background: var(--sb-thumb-color);
    border-radius: 3px;
}

@supports not selector(::-webkit-scrollbar) {
    body {
        scrollbar-color: var(--sb-thumb-color) var(--sb-track-color);
    }
}
::-webkit-scrollbar-button {
    display: none;
    height: 0;
    width: 0;
}
</style>
