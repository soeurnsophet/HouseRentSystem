import { createApp } from "vue";
// PrimeVue
import App from "./App.vue";
import PrimeVue from "primevue/config";
import Aura from "@primeuix/themes/aura";

// Pinia
import { createPinia } from "pinia";
// Routers
import router from "./routers";

const pinia = createPinia();
const app = createApp(App);
app.use(PrimeVue, {
    theme: {
        preset: Aura,
        options: {
            prefix: "p",
            darkModeSelector: "system",
            cssLayer: false,
        },
    },
});
app.use(pinia);
app.use(router);
app.mount("#app");
