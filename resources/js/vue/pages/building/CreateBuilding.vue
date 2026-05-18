<script setup>
import { Message } from "primevue";
import { inject, ref } from "vue";
import useBuildingStore from "../../stores/building.store";
import { storeToRefs } from "pinia";
import { MessageSuccess } from "../../utils/Message";
import { useToast } from "primevue";

const buildingStore = useBuildingStore();
const { loading } = storeToRefs(buildingStore);
const dialogRef = inject("dialogRef");
const toast = useToast();

const emptyForm = () => ({
    building_name: "",
    address: "",
    phone: "",
});

const form = ref(emptyForm());
const errors = ref({});

const validated = () => {
    errors.value = {};

    if (!form.value.building_name.trim()) {
        errors.value.building_name = "Building name is required.";
    }

    if (!form.value.address.trim()) {
        errors.value.address = "Address is required.";
    }

    if (!form.value.phone.trim()) {
        errors.value.phone = "Phone is required.";
    }

    return Object.keys(errors.value).length === 0;
};

const resetForm = () => {
    form.value = emptyForm();
    errors.value = {};
};

const submit = async () => {
    if (!validated()) return;

    try {
        const res = await buildingStore.createBuilding(form.value);
        MessageSuccess("", res.message, toast);
        dialogRef.value.close({ created: true });
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        }
    }
};
</script>

<template>
    <div class="space-y-5">
        <div class="rounded-lg border border-teal-100 bg-teal-50 p-4">
            <div class="flex items-center gap-3">
                <span
                    class="grid h-10 w-10 place-items-center rounded-md bg-white text-teal-700"
                >
                    <i class="fa-solid fa-building"></i>
                </span>
                <div>
                    <p class="font-semibold text-slate-950">
                        Building Profile
                    </p>
                    <p class="text-sm text-slate-600">
                        Save the main contact and location details.
                    </p>
                </div>
            </div>
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="text-sm font-medium text-slate-700">
                        Building Name
                    </label>
                    <InputText
                        v-model="form.building_name"
                        class="w-full mt-1"
                        placeholder="Sky Apartment"
                    />

                    <Message
                        v-if="errors.building_name"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ errors.building_name }}
                    </Message>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">
                        Phone
                    </label>
                    <InputText
                        v-model="form.phone"
                        class="w-full mt-1"
                        placeholder="+855..."
                    />
                    <Message
                        v-if="errors.phone"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ errors.phone }}
                    </Message>
                </div>

                <div class="md:col-span-2">
                    <label class="text-sm font-medium text-slate-700">
                        Address
                    </label>
                    <Textarea
                        v-model="form.address"
                        class="w-full mt-1"
                        placeholder="Phnom Penh"
                        rows="3"
                    />
                    <Message
                        v-if="errors.address"
                        severity="error"
                        size="small"
                        variant="simple"
                    >
                        {{ errors.address }}
                    </Message>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <Button
                    label="Reset"
                    icon="fa-solid fa-rotate-left"
                    severity="secondary"
                    outlined
                    type="button"
                    @click="resetForm"
                />

                <Button
                    label="Save Building"
                    icon="fa-solid fa-check"
                    :loading="loading"
                    type="submit"
                />
            </div>
        </form>
    </div>
</template>
