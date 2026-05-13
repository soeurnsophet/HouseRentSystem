<script setup>
import { onMounted, reactive, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useToast } from "primevue";
import { MessageError, MessageSuccess } from "../utils/Message";
import userAuthStore from "../stores/auth.store";

const router = useRouter();
const route = useRoute();
const toast = useToast();
const auth = userAuthStore(); // use auth store

const form = reactive({
    phone: "",
    password: "",
    remember: true,
});

const isSubmitting = ref(false);
const errorMessage = ref({
    phone: "",
    password: "",
    general: "",
});

const showRouteMessage = () => {
    if (!route.query.message) return;

    const message = String(route.query.message);

    errorMessage.value.general = message;
    MessageError("Forbidden", message, toast);
};

onMounted(showRouteMessage);

watch(() => route.query.message, showRouteMessage);

const submitLogin = async () => {
    errorMessage.value = {
        phone: "",
        password: "",
        general: "",
    };

    let hasError = false;

    if (!form.phone) {
        errorMessage.value.phone = "Please enter your phone number.";
        hasError = true;
    }

    if (!form.password) {
        errorMessage.value.password = "Please enter your password.";
        hasError = true;
    }

    if (hasError) return;

    isSubmitting.value = true;
    try {
        const { success, message } = await auth.login(form);

        if (success) {
            MessageSuccess(message, message, toast);
            router.push({ name: "dashboard" });
        } else {
            MessageError(message, message, toast);
        }
    } catch (error) {
        MessageError(error.message, error.message, toast);
    }
};

</script>

<template>
    <main
        class="min-h-screen bg-[#f7f4ef] flex items-center justify-center p-6 text-slate-950"
    >
        <div
            class="w-full max-w-md bg-white p-8 rounded-2xl shadow-sm border border-slate-200"
        >
            <div class="mb-8 text-center">
                <div
                    class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 mb-4"
                >
                    <i class="pi pi-building text-2xl"></i>
                </div>
                <p
                    class="text-xs font-bold uppercase tracking-widest text-emerald-700"
                >
                    House Rent System
                </p>
                <h1 class="mt-2 text-2xl font-semibold">Welcome back</h1>
                <p class="mt-2 text-sm text-slate-500">
                    Sign in to manage your rentals and payments
                </p>
            </div>

            <form class="space-y-5" @submit.prevent="submitLogin">
                <!-- Phone Field -->
                <div class="space-y-2">
                    <label
                        for="phone"
                        class="text-sm font-medium text-slate-700"
                        >Phone number</label
                    >
                    <IconField>
                        <InputIcon class="pi pi-phone" />
                        <InputText
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            placeholder="012 345 678"
                            class="w-full"
                            :invalid="!!errorMessage.phone"
                        />
                    </IconField>
                    <Message
                        v-if="errorMessage.phone"
                        severity="error"
                        variant="simple"
                        size="small"
                    >
                        {{ errorMessage.phone }}
                    </Message>
                </div>

                <!-- Password Field -->
                <div class="space-y-2">
                    <label
                        for="password"
                        class="text-sm font-medium text-slate-700"
                        >Password</label
                    >
                    <IconField>
                        <InputIcon class="pi pi-lock" />
                        <Password
                            id="password"
                            v-model="form.password"
                            :feedback="false"
                            toggle-mask
                            placeholder="Enter your password"
                            input-class="w-full"
                            class="w-full"
                            :invalid="!!errorMessage.password"
                        />
                    </IconField>
                    <Message
                        v-if="errorMessage.password"
                        severity="error"
                        variant="simple"
                        size="small"
                    >
                        {{ errorMessage.password }}
                    </Message>
                </div>

                <!-- Utilities -->
                <div class="flex items-center justify-between text-sm">
                    <label
                        class="flex items-center gap-2 text-slate-700 cursor-pointer"
                    >
                        <Checkbox
                            v-model="form.remember"
                            binary
                            inputId="remember"
                        />
                        <span>Remember me</span>
                    </label>
                    <!-- <RouterLink
                        to="/forgot-password"
                        class="font-medium text-emerald-700 hover:underline"
                    >
                        Forgot password?
                    </RouterLink> -->

                    <div class="font-medium text-emerald-700 hover:underline">
                        Forgot password?
                    </div>
                </div>

                <!-- Submit Button -->
                <Button
                    type="submit"
                    label="Sign In"
                    icon="pi pi-sign-in"
                    :loading="auth.isLoading"
                    class="w-full p-3"
                />
            </form>
        </div>
    </main>
</template>
