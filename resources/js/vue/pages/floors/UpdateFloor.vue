<script setup>
import { inject, onMounted, ref } from "vue";
import { storeToRefs } from "pinia";
import Message from "primevue/message";
import { useToast } from "primevue";
import useBuildingStore from "../../stores/building.store";
import useFloorStore from "../../stores/floor.store";
import { MessageSuccess } from "../../utils/Message";

const dialogRef = inject("dialogRef");
const toast = useToast();

const buildingStore = useBuildingStore();
const { buildings } = storeToRefs(buildingStore);

const floorStore = useFloorStore();
const { saving } = storeToRefs(floorStore);

const floor = dialogRef.value.data.floor;
const form = ref({
    name: floor.name || "",
    building_id: floor.building_id || null,
    base_price: floor.base_price ?? 0,
    description: floor.description || "",
});

const errors = ref({
    name: "",
    building_id: "",
    base_price: "",
});

onMounted(buildingStore.fetchBuildings);

const validate = () => {
    errors.value = {
        name: "",
        building_id: "",
        base_price: "",
    };

    let valid = true;

    if (!form.value.name) {
        errors.value.name = "Name is required";
        valid = false;
    }

    if (!form.value.building_id) {
        errors.value.building_id = "Building is required";
        valid = false;
    }

    if (form.value.base_price === null || form.value.base_price === undefined) {
        errors.value.base_price = "Base price is required";
        valid = false;
    } else if (form.value.base_price < 0) {
        errors.value.base_price = "Base price must be >= 0";
        valid = false;
    }

    return valid;
};

const submit = async () => {
    if (!validate()) return;

    try {
        const res = await floorStore.updateFloor(floor.id, form.value);
        MessageSuccess("", res.message, toast);
        dialogRef.value.close({ updated: true });
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        }
    }
};

const closeDialog = () => {
    dialogRef.value.close({ updated: false });
};
</script>

<template>
    <div class="space-y-6">
        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <InputText v-model="form.name" class="w-full" />

                <Message
                    v-if="errors.name"
                    severity="error"
                    class="mt-2"
                    variant="simple"
                >
                    {{ errors.name }}
                </Message>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Building</label>
                <Select
                    v-model="form.building_id"
                    class="w-full"
                    :options="buildings"
                    option-label="building_name"
                    option-value="id"
                    placeholder="Select building"
                />

                <Message
                    v-if="errors.building_id"
                    severity="error"
                    class="mt-2"
                    variant="simple"
                >
                    {{ errors.building_id }}
                </Message>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Base Price</label>
                <InputNumber
                    v-model="form.base_price"
                    mode="currency"
                    currency="USD"
                    locale="en-US"
                    class="w-full"
                />

                <Message
                    v-if="errors.base_price"
                    severity="error"
                    class="mt-2"
                    variant="simple"
                >
                    {{ errors.base_price }}
                </Message>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Description
                </label>
                <Textarea
                    v-model="form.description"
                    rows="3"
                    class="w-full"
                    placeholder="Optional..."
                />
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <Button
                    label="Cancel"
                    severity="secondary"
                    outlined
                    type="button"
                    @click="closeDialog"
                />

                <Button
                    label="Update"
                    icon="fa-solid fa-check"
                    type="submit"
                    :loading="saving"
                />
            </div>
        </form>
    </div>
</template>
