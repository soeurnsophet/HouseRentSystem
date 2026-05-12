<script setup>
import { inject, reactive, ref } from "vue";
import { storeToRefs } from "pinia";
import useUserStore from "../../stores/users.store";
import { MessageSuccess } from "../../utils/Message";
import { useToast } from "primevue";

const dialogRef = inject("dialogRef");
const userStore = useUserStore();
const { saving } = storeToRefs(userStore);
const errors = ref({});
const toast = useToast();
const form = reactive({
    name: "",
    username: "",
    email: "",
    phone: "",
    password: "",
    role: "user",
    gender_id: null,
});

const roles = [
    { label: "Admin", value: "admin" },
    { label: "Manager", value: "manager" },
    { label: "User", value: "user" },
];

const genders = [
    { label: "Male", value: 1 },
    { label: "Female", value: 2 },
];

const fieldError = (name) => errors.value[name]?.[0] || "";

const closeDialog = () => {
    dialogRef.value.close();
};

const createUser = async () => {
    errors.value = {};

    try {
        const res = await userStore.createUser(form);
        console.log(res);

        if (res.success) MessageSuccess("", res.message, toast);
        dialogRef.value.close({ created: true });
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        }
    }
};
</script>

<template>
    <form class="grid gap-4 md:grid-cols-2" @submit.prevent="createUser">
        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700" for="create-name"
                >Name</label
            >
            <InputText
                id="create-name"
                v-model="form.name"
                class="w-full"
                :invalid="!!fieldError('name')"
            />
            <Message
                v-if="fieldError('name')"
                severity="error"
                size="small"
                variant="simple"
            >
                {{ fieldError("name") }}
            </Message>
        </div>

        <div class="space-y-2">
            <label
                class="text-sm font-medium text-slate-700"
                for="create-username"
                >Username</label
            >
            <InputText
                id="create-username"
                v-model="form.username"
                class="w-full"
                :invalid="!!fieldError('username')"
            />
            <Message
                v-if="fieldError('username')"
                severity="error"
                size="small"
                variant="simple"
            >
                {{ fieldError("username") }}
            </Message>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700" for="create-phone"
                >Phone</label
            >
            <InputText
                id="create-phone"
                v-model="form.phone"
                class="w-full"
                :invalid="!!fieldError('phone')"
            />
            <Message
                v-if="fieldError('phone')"
                severity="error"
                size="small"
                variant="simple"
            >
                {{ fieldError("phone") }}
            </Message>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700" for="create-email"
                >Email</label
            >
            <InputText
                id="create-email"
                v-model="form.email"
                class="w-full"
                :invalid="!!fieldError('email')"
            />
            <Message
                v-if="fieldError('email')"
                severity="error"
                size="small"
                variant="simple"
            >
                {{ fieldError("email") }}
            </Message>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700" for="create-role"
                >Role</label
            >
            <Select
                id="create-role"
                v-model="form.role"
                :options="roles"
                option-label="label"
                option-value="value"
                class="w-full"
            />
        </div>

        <div class="space-y-2">
            <label
                class="text-sm font-medium text-slate-700"
                for="create-gender"
                >Gender</label
            >
            <Select
                id="create-gender"
                v-model="form.gender_id"
                :options="genders"
                option-label="label"
                option-value="value"
                class="w-full"
                show-clear
            />
        </div>

        <div class="space-y-2 md:col-span-2">
            <label
                class="text-sm font-medium text-slate-700"
                for="create-password"
                >Password</label
            >
            <Password
                id="create-password"
                v-model="form.password"
                class="w-full"
                input-class="w-full"
                :feedback="false"
                toggle-mask
                :invalid="!!fieldError('password')"
            />
            <Message
                v-if="fieldError('password')"
                severity="error"
                size="small"
                variant="simple"
            >
                {{ fieldError("password") }}
            </Message>
        </div>

        <div class="flex justify-end gap-3 md:col-span-2">
            <Button
                label="Cancel"
                severity="secondary"
                outlined
                type="button"
                @click="closeDialog"
            />
            <Button
                label="Create"
                icon="pi pi-save"
                :loading="saving"
                type="submit"
            />
        </div>
    </form>
</template>
