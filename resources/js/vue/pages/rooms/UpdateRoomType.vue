<script setup>
import { inject, ref } from "vue";
import { storeToRefs } from "pinia";
import Message from "primevue/message";
import { useToast } from "primevue";
import useRoomTypeStore from "../../stores/room-type.store";
import { MessageSuccess } from "../../utils/Message";

const dialogRef = inject("dialogRef");
const toast = useToast();
const roomTypeStore = useRoomTypeStore();
const { saving } = storeToRefs(roomTypeStore);
const roomType = dialogRef.value.data.roomType;

const form = ref({
    type_name: roomType.type_name || "",
    description: roomType.description || "",
});

const errors = ref({});

const errorFor = (field) => {
    const error = errors.value[field];
    return Array.isArray(error) ? error[0] : error;
};

const validate = () => {
    errors.value = {};

    if (!form.value.type_name) {
        errors.value.type_name = "Type name is required";
    }

    return !Object.keys(errors.value).length;
};

const submit = async () => {
    if (!validate()) return;

    try {
        const res = await roomTypeStore.updateRoomType(roomType.id, form.value);
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
    <form class="space-y-5" @submit.prevent="submit">
        <div>
            <label class="mb-1 block text-sm font-medium">Type Name</label>
            <InputText v-model="form.type_name" class="w-full" />
            <Message v-if="errorFor('type_name')" severity="error" class="mt-2" variant="simple">
                {{ errorFor("type_name") }}
            </Message>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium">Description</label>
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
</template>
