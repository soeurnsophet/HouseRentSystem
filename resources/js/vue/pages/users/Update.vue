<script setup>
import { inject, reactive, ref } from "vue";
import { storeToRefs } from "pinia";
import useUserStore from "../../stores/users.store";
import { useToast } from "primevue";
import { MessageSuccess } from "../../utils/Message";

const dialogRef = inject("dialogRef");
const userStore = useUserStore();
const { saving } = storeToRefs(userStore);
const errors = ref({});
const user = dialogRef.value.data.user;
const toast = useToast();
const form = reactive({
    id: user.id,
    name: user.name,
    username: user.username,
    email: user.email,
    phone: user.phone,
    role: user.role,
    gender_id: user.gender_id,
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

const updateUser = async () => {
    errors.value = {};

    const payload = {
        name: form.name,
        username: form.username,
        email: form.email,
        phone: form.phone,
        role: form.role,
        gender_id: form.gender_id,
    };

    try {
        const res = await userStore.updateUser(form.id, payload);
        if (res.success) MessageSuccess("", res.message, toast);
        dialogRef.value.close({ updated: true });
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        }
    }
};
</script>

<template>
    <form class="grid gap-4 md:grid-cols-2" @submit.prevent="updateUser">
        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700" for="update-name"
                >Name</label
            >
            <InputText
                id="update-name"
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
                for="update-username"
                >Username</label
            >
            <InputText
                id="update-username"
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
            <label class="text-sm font-medium text-slate-700" for="update-phone"
                >Phone</label
            >
            <InputText
                id="update-phone"
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
            <label class="text-sm font-medium text-slate-700" for="update-email"
                >Email</label
            >
            <InputText
                id="update-email"
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
            <label class="text-sm font-medium text-slate-700" for="update-role"
                >Role</label
            >
            <Select
                id="update-role"
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
                for="update-gender"
                >Gender</label
            >
            <Select
                id="update-gender"
                v-model="form.gender_id"
                :options="genders"
                option-label="label"
                option-value="value"
                class="w-full"
            />
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
                label="Update"
                icon="pi pi-save"
                :loading="saving"
                type="submit"
            />
        </div>
    </form>
</template>
