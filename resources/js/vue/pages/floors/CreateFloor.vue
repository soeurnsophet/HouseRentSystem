<script setup>
import { defineAsyncComponent, inject, onMounted, ref } from "vue";
import useBuildingStore from "../../stores/building.store";
import { storeToRefs } from "pinia";

import Message from "primevue/message";
import useFloorStore from "../../stores/floor.store";
import { MessageSuccess } from "../../utils/Message";
import { useDialog, useToast } from "primevue";

const CreateBuilding = defineAsyncComponent(
    () => import("../building/CreateBuilding.vue"),
);

const toast = useToast();
const dialog = useDialog();
const buildingStore = useBuildingStore();
const { buildings } = storeToRefs(buildingStore);
const floorStore = useFloorStore();
const { saving } = storeToRefs(floorStore);

const dialogRef = inject("dialogRef");

const form = ref({
    name: "",
    building_id: null,
    base_price: 0,
    description: "",
});

const errors = ref({
    name: "",
    building_id: "",
    base_price: "",
});

onMounted(buildingStore.fetchBuildings);

// validation
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

// submit
const submit = async () => {
    if (!validate()) return;

    const res = await floorStore.createFloor(form.value);
    MessageSuccess("", res.message, toast);

    dialogRef.value.close({ created: true });
};

const openCreateBuildingDialog = () => {
    dialog.open(CreateBuilding, {
        props: {
            position: "top",
            header: "Add Building",
            modal: true,
            style: { width: "42rem" },
            breakpoints: { "960px": "75vw", "640px": "92vw" },
            draggable: false,
        },
        onClose: async (options) => {
            if (options?.data?.created) {
                await buildingStore.fetchBuildings();

                if (options.data.building?.id) {
                    form.value.building_id = options.data.building.id;
                }
            }
        },
    });
};
</script>

<template>
    <div class="space-y-6">
        <form @submit.prevent="submit" class="space-y-5">
            <!-- Name -->
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

            <!-- Building -->
            <div>
                <label class="block text-sm font-medium mb-1">Building</label>
                <InputGroup>
                    <Select
                        v-model="form.building_id"
                        class="w-full"
                        :options="buildings"
                        option-label="building_name"
                        option-value="id"
                        placeholder="Select building"
                    />
                    <InputGroupAddon>
                        <Button
                            icon="fa-solid fa-building-circle-plus"
                            type="button"
                            @click="openCreateBuildingDialog"
                        />
                    </InputGroupAddon>
                </InputGroup>

                <Message
                    v-if="errors.building_id"
                    severity="error"
                    class="mt-2"
                    variant="simple"
                >
                    {{ errors.building_id }}
                </Message>
            </div>

            <!-- Base Price -->
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

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium mb-1"
                    >Description</label
                >
                <Textarea
                    v-model="form.description"
                    rows="3"
                    class="w-full"
                    placeholder="Optional..."
                />
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-2">
                <Button
                    label="Cancel"
                    severity="secondary"
                    outlined
                    type="button"
                    @click="
                        form = {
                            name: '',
                            building_id: null,
                            base_price: 0,
                            description: '',
                        }
                    "
                />

                <Button
                    label="Save"
                    icon="fa-solid fa-save"
                    type="submit"
                    :loading="saving"
                />
            </div>
        </form>
    </div>
</template>
