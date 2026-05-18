<script setup>
import { computed, defineAsyncComponent, inject, onMounted, ref } from "vue";
import { storeToRefs } from "pinia";
import Message from "primevue/message";
import { useDialog, useToast } from "primevue";
import useFloorStore from "../../stores/floor.store";
import useRoomStore from "../../stores/room.store";
import useRoomTypeStore from "../../stores/room-type.store";
import { MessageSuccess } from "../../utils/Message";
const CreateFloor = defineAsyncComponent(
    () => import("../floors/CreateFloor.vue"),
);
const CreateRoomType = defineAsyncComponent(
    () => import("./CreateRoomType.vue"),
);
const dialog = useDialog();
const dialogRef = inject("dialogRef");
const toast = useToast();
const floorStore = useFloorStore();
const roomStore = useRoomStore();
const roomTypeStore = useRoomTypeStore();
const { floors } = storeToRefs(floorStore);
const { roomTypes } = storeToRefs(roomTypeStore);
const { saving } = storeToRefs(roomStore);

const statusOptions = [
    { label: "Available", value: "available" },
    { label: "Occupied", value: "occupied" },
    { label: "Maintenance", value: "maintenance" },
    { label: "Reserved", value: "reserved" },
];

const form = ref({
    floor_id: null,
    room_type_id: null,
    room_number: "",
    status: "available",
});

const errors = ref({});

const floorOptions = computed(() =>
    floors.value.map((floor) => ({
        ...floor,
        label: `${floor.building?.building_name || "Building"} - ${floor.name}`,
    })),
);

const roomTypeOptions = computed(() => [
    { id: null, type_name: "No room type" },
    ...roomTypes.value,
]);

const errorFor = (field) => {
    const error = errors.value[field];
    return Array.isArray(error) ? error[0] : error;
};

onMounted(async () => {
    await Promise.all([
        floorStore.fetchFloors({ per_page: 100 }),
        roomTypeStore.fetchRoomTypes({ per_page: 100 }),
    ]);
});

const validate = () => {
    errors.value = {};

    if (!form.value.floor_id) {
        errors.value.floor_id = "Floor is required";
    }

    if (!form.value.room_number) {
        errors.value.room_number = "Room number is required";
    }

    if (!form.value.status) {
        errors.value.status = "Status is required";
    }

    return !Object.keys(errors.value).length;
};

const submit = async () => {
    if (!validate()) return;

    try {
        const res = await roomStore.createRoom(form.value);
        MessageSuccess("", res.message, toast);
        dialogRef.value.close({ created: true });
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        }
    }
};

const closeDialog = () => {
    dialogRef.value.close({ created: false });
};

const openCreateFloorDialog = () => {
    dialog.open(CreateFloor, {
        props: {
            position: "top",
            header: "Add Floor",
            modal: true,
            style: { width: "38rem" },
            breakpoints: { "960px": "75vw", "640px": "92vw" },
            draggable: false,
        },
        onClose: (options) => {
            if (options?.data?.created) {
                floorStore.fetchFloors();
            }
        },
    });
};

const openCreateRoomTypeDialog = () => {
    dialog.open(CreateRoomType, {
        props: {
            position: "top",
            header: "Add Floor",
            modal: true,
            style: { width: "38rem" },
            breakpoints: { "960px": "75vw", "640px": "92vw" },
            draggable: false,
        },
        onClose: (options) => {
            if (options?.data?.created) {
                roomTypeStore.fetchRoomTypes();
            }
        },
    });
};
</script>

<template>
    <form class="space-y-5" @submit.prevent="submit">
        <div>
            <label class="mb-1 block text-sm font-medium">Room Number</label>
            <InputText v-model="form.room_number" class="w-full" />
            <Message
                v-if="errorFor('room_number')"
                severity="error"
                class="mt-2"
                variant="simple"
            >
                {{ errorFor("room_number") }}
            </Message>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium">Floor</label>
            <InputGroup>
                <Select
                    v-model="form.floor_id"
                    class="w-full"
                    :options="floorOptions"
                    option-label="label"
                    option-value="id"
                    placeholder="Select floor"
                />
                <Message
                    v-if="errorFor('floor_id')"
                    severity="error"
                    class="mt-2"
                    variant="simple"
                >
                    {{ errorFor("floor_id") }}
                </Message>
                <InputGroupAddon>
                    <Button
                        icon="fa-solid fa-plus"
                        @click="openCreateFloorDialog"
                    />
                </InputGroupAddon>
            </InputGroup>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium">Room Type</label>
            <div class="flex items-center">
                <InputGroup>
                    <Select
                        v-model="form.room_type_id"
                        class="w-full"
                        :options="roomTypeOptions"
                        option-label="type_name"
                        option-value="id"
                        placeholder="Select room type"
                    />
                    <InputGroupAddon>
                        <Button
                            icon="fa-solid fa-plus"
                            @click="openCreateRoomTypeDialog"
                        />
                    </InputGroupAddon>
                </InputGroup>
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium">Status</label>
            <Select
                v-model="form.status"
                class="w-full"
                :options="statusOptions"
                option-label="label"
                option-value="value"
                placeholder="Select status"
            />
            <Message
                v-if="errorFor('status')"
                severity="error"
                class="mt-2"
                variant="simple"
            >
                {{ errorFor("status") }}
            </Message>
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
                label="Save"
                icon="fa-solid fa-save"
                type="submit"
                :loading="saving"
            />
        </div>
    </form>
</template>
