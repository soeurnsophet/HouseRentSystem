import { createApp } from "vue";
// PrimeVue
import App from "./App.vue";
import PrimeVue from "primevue/config";
import Aura from "@primeuix/themes/aura";
import ToastService from "primevue/toastservice";
import DialogService from "primevue/dialogservice";
import ConfirmationService from "primevue/confirmationservice";
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
app.use(ToastService);
app.use(DialogService);
app.use(ConfirmationService);
app.use(pinia);
app.use(router);
app.mount("#app");
