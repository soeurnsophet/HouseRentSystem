<script setup>
import { Message } from "primevue";
import { inject, ref } from "vue";
import useBuildingStore from "../../stores/building.store";
import { storeToRefs } from "pinia";
import { MessageSuccess } from "../../utils/Message";
import { useToast } from "primevue";
// store
const buildingStore = useBuildingStore();
const { loading } = storeToRefs(buildingStore);
const dialogRef = inject("dialogRef");
const toast = useToast();
const buildingData = dialogRef.value.data.building;
const form = ref({
    building_name: buildingData.building_name || "",
    address: buildingData.address || "",
    phone: buildingData.phone || "",
});
const errors = ref({});

// validate form
const validated = () => {
    errors.value = {};

    if (!form.value.building_name)
        errors.value.building_name = "Building name is required.";

    if (!form.value.address) errors.value.address = "Address is required.";

    if (!form.value.phone) errors.value.phone = "Phone is required.";

    return Object.keys(errors.value).length === 0;
};

const submit = async () => {
    if (!validated()) return;
    try {
        const res = await buildingStore.updateBuilding(
            buildingData.id,
            form.value,
        );
        MessageSuccess("", res.message, toast);
        dialogRef.value.close({ updated: true });
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        }
    }
};

//
const handleOnCloseDialog = () => {
    dialogRef.value.close({ updated: false });
    form.value = {};
};
</script>

<template>
    <div class="space-y-6">
        <!-- Form -->
        <form class="space-y-6" @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="text-sm font-medium">Building Name</label>
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
                    <label class="text-sm font-medium">Phone</label>
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
                    <label class="text-sm font-medium">Address</label>
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

            <!-- Actions -->
            <div class="mt-6 flex justify-end gap-3">
                <Button
                    label="Cancel"
                    severity="secondary"
                    outlined
                    @click="handleOnCloseDialog"
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
