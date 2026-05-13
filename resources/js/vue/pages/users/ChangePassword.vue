<script setup>
import { inject, reactive, ref } from "vue";
import useUserStore from "../../stores/users.store";
import { MessageSuccess } from "../../utils/Message";
import { useToast } from "primevue";
import { storeToRefs } from "pinia";

const dialogRef = inject("dialogRef");
const user = dialogRef.value.data.user;
const userStore = useUserStore();
const toast = useToast();
const { saving } = storeToRefs(userStore);
const form = reactive({
    password: "",
});

const errors = ref({});

const closeDialog = () => {
    dialogRef.value.close();
};

const updatePassword = async () => {
    if (!form.password) {
        errors.value.password = "Password is required.";
        return;
    }

    const { success, message } = await userStore.verifyChangePassword(
        user.id,
        form,
    );

    if (success) {
        MessageSuccess("", message, toast);
        dialogRef.value.close({ updated: true });
        closeDialog();
    }
};
</script>

<template>
    <form class="space-y-4" @submit.prevent="updatePassword">
        <!-- Password -->
        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700">
                New Password
            </label>

            <Password
                v-model="form.password"
                toggleMask
                fluid
                :feedback="false"
                :invalid="!!errors.password"
            />

            <Message
                v-if="errors.password"
                severity="error"
                size="small"
                variant="simple"
            >
                {{ errors.password }}
            </Message>
        </div>

        <!-- Buttons -->
        <div class="flex flex-col md:flex-row md:justify-end gap-2 pt-4">
            <Button
                label="Cancel"
                icon="pi pi-times"
                severity="secondary"
                outlined
                type="button"
                @click="closeDialog"
            />

            <Button
                label="Update Password"
                icon="pi pi-key"
                type="submit"
                :loading="saving"
            />
        </div>
    </form>
</template>
