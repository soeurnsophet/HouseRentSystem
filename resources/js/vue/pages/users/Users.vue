<script setup>
import { onMounted, reactive, ref } from "vue";
import { storeToRefs } from "pinia";
import { useDialog } from "primevue/usedialog";
import useUserStore from "../../stores/users.store";
import CreateUser from "./Create.vue";
import UpdateUser from "./Update.vue";
import ChangePassword from "./ChangePassword.vue";
import { useToast } from "primevue";
import { MessageSuccess } from "../../utils/Message";
import { value } from "@primeuix/themes/aura/knob";

const dialog = useDialog();
const toast = useToast();
const userStore = useUserStore();
const { users, meta, loading, deleting } = storeToRefs(userStore);
const deleteDialogVisible = ref(false);
const selectedUser = ref(null);
// Drawer
const visibleRight = ref(false);
const selectedRow = ref(null);

// toggle
const checked = ref(false);

const lazyParams = reactive({
    first: 0,
    page: 0,
    rows: 10,
    totalRecords: 0,
    search: "",
    role: null,
});

const fetchUsers = async () => {
    await userStore.fetchUsers({
        page: lazyParams.page + 1,
        per_page: lazyParams.rows,
        search: lazyParams.search || undefined,
        role: lazyParams.role || undefined,
    });

    lazyParams.totalRecords = meta.value.total;
};

const onPage = (event) => {
    lazyParams.first = event.first;
    lazyParams.page = event.page;
    lazyParams.rows = event.rows;

    fetchUsers();
};

let searchTimer;
const onSearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        lazyParams.first = 0;
        lazyParams.page = 0;
        fetchUsers();
    }, 300);
};
const onRoleChange = () => {
    lazyParams.first = 0;
    lazyParams.page = 0;
    fetchUsers();
};
// Dialog Create User
const openCreateDialog = () => {
    dialog.open(CreateUser, {
        props: {
            header: "Add User",
            modal: true,
            style: {
                width: "42rem",
            },
            breakpoints: {
                "960px": "75vw",
                "640px": "92vw",
            },
        },

        onClose: (options) => {
            if (options?.data?.created) {
                lazyParams.first = 0;
                lazyParams.page = 0;
                fetchUsers();
            }
        },
    });
};

const openEditDialog = (user) => {
    dialog.open(UpdateUser, {
        data: {
            user,
        },
        props: {
            header: "Edit User",
            modal: true,
            draggable: false,
            style: {
                width: "42rem",
            },
            breakpoints: {
                "960px": "75vw",
                "640px": "92vw",
            },
        },
        onClose: (options) => {
            if (options?.data?.updated) {
                visibleRight.value = false;
                fetchUsers();
            }
        },
    });
};

const confirmDelete = (user) => {
    selectedUser.value = user;
    deleteDialogVisible.value = true;
    visibleRight.value = false;
};

const deleteUser = async () => {
    if (!selectedUser.value) return;

    await userStore.deleteUser(selectedUser.value.id);
    deleteDialogVisible.value = false;
    selectedUser.value = null;

    if (users.value.length === 1 && lazyParams.page > 0) {
        lazyParams.page -= 1;
        lazyParams.first = lazyParams.page * lazyParams.rows;
    }

    fetchUsers();
};

onMounted(fetchUsers);

const openDrawer = (data) => {
    selectedRow.value = data;
    visibleRight.value = true;
};

// change password
const openChangePasswordDialog = (user) => {
    dialog.open(ChangePassword, {
        data: {
            user,
        },
        props: {
            position: "top",
            header: "Change Password",
            modal: true,
            draggable: false,
            style: {
                width: "42vw",
            },
            breakpoints: {
                "960px": "45vw",
                "640px": "42vw",
            },
        },
        onClose: (options) => {
            if (options?.data?.updated) {
                visibleRight.value = false;
                fetchUsers();
            }
        },
    });
};
// disabled user
const toggleUserStatus = async (user) => {
    try {
        const { success, message, disabled } = await userStore.disableUser(
            user.id,
            {
                disabled: user.disabled,
            },
        );

        if (success) {
            console.log(disabled);

            const msg = disabled == 1 ? message : "User undisabled";
            MessageSuccess("", msg, toast);
            fetchUsers();
        }
    } catch (error) {
        console.log(error);
    }
};
const getSeverity = (user) => {
    switch (user.role) {
        case "admin":
            return "danger";
        case "manager":
            return "warning";
        default:
            return "info";
    }
};

const roles = ref([
    { label: "Admin", value: "admin" },
    { label: "Manager", value: "manager" },
    { label: "User", value: "user" },
]);
const onClearFilter = () => {
    lazyParams.first = 0;
    lazyParams.page = 0;
    lazyParams.search = null;
    lazyParams.role = null;
    fetchUsers();
};
</script>

<template>
    <section class="space-y-5">
        <div
            class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
        >
            <div>
                <h1 class="mt-2 text-3xl font-semibold text-slate-950">
                    Users
                </h1>
                <p class="mt-2 text-sm text-slate-600">
                    Load users page by page and manage accounts.
                </p>
            </div>

            <Button
                label="Add User"
                icon="fa-solid fa-user-plus"
                @click="openCreateDialog"
            />
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="mb-6">
                <div
                    class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between"
                >
                    <div
                        class="flex flex-col gap-3 md:flex-row md:items-center"
                    >
                        <IconField class="w-full md:w-80">
                            <InputIcon class="pi pi-search text-slate-400" />

                            <InputText
                                v-model="lazyParams.search"
                                placeholder="Search users..."
                                class="w-full"
                                @input="onSearch"
                            />
                        </IconField>

                        <Select
                            v-model="lazyParams.role"
                            :options="roles"
                            option-label="label"
                            option-value="value"
                            placeholder="Role"
                            class="w-full md:w-48"
                            @change="onSearch"
                        />

                        <Button
                            icon="pi pi-filter-slash"
                            label="Reset"
                            outlined
                            severity="secondary"
                            class="rounded-2xl"
                            @click="onClearFilter"
                        />
                    </div>
                </div>
            </div>

            <DataTable
                :value="users"
                :loading="loading"
                lazy
                paginator
                :first="lazyParams.first"
                :rows="lazyParams.rows"
                :total-records="lazyParams.totalRecords"
                :rows-per-page-options="[10, 25, 50]"
                data-key="id"
                striped-rows
                responsive-layout="scroll"
                class="p-datatable-sm"
                @page="onPage"
            >
                <Column header="No" class="w-20">
                    <template #body="{ index }">
                        {{ lazyParams.first + index + 1 }}
                    </template>
                </Column>
                <Column field="name" header="Name" />
                <Column field="username" header="Username" />
                <Column field="phone" header="Phone" />
                <Column field="email" header="Email" />
                <Column header="Gender">
                    <template #body="{ data }">
                        {{ data.gender?.gender_en_full || "-" }}
                    </template>
                </Column>
                <Column field="role" header="Role">
                    <template #body="{ data }">
                        <Tag :value="data.role" :severity="getSeverity(data)" />
                    </template>
                </Column>

                <Column header="Disable" class="w-5">
                    <template #body="{ data }">
                        <div class="flex justify-center">
                            <ToggleSwitch
                                :modelValue="data.disabled !== 1"
                                @update:modelValue="
                                    (val) => toggleUserStatus(data, val)
                                "
                            />
                        </div>
                    </template>
                </Column>
                <Column header="Actions" class="w-8">
                    <template #body="{ data }">
                        <div class="flex justify-center">
                            <Button
                                icon="fa-solid fa-gears"
                                severity="info"
                                text
                                rounded
                                @click="openDrawer(data)"
                            />
                        </div>
                    </template>
                </Column>
                <!-- drawer for action udpate and delete -->
                <Drawer
                    v-model:visible="visibleRight"
                    header="Actions"
                    position="right"
                    class="w-80"
                >
                    <div class="flex flex-col justify-between h-full">
                        <div class="flex flex-col gap-3">
                            <Button
                                label="Update User Info"
                                icon="fa-solid fa-pen-to-square"
                                severity="secondary"
                                outlined
                                @click="openEditDialog(selectedRow)"
                            />

                            <Button
                                label="Change Password"
                                icon="fa-solid fa-lock"
                                severity="secondary"
                                outlined
                                @click="openChangePasswordDialog(selectedRow)"
                            />
                        </div>

                        <Button
                            label="Delete"
                            icon="fa-solid fa-trash"
                            severity="danger"
                            @click="confirmDelete(selectedRow)"
                        />
                    </div>
                </Drawer>
            </DataTable>
        </div>

        <Dialog
            v-model:visible="deleteDialogVisible"
            modal
            header="Delete User"
            class="w-[min(92vw,28rem)]"
        >
            <p class="text-slate-600">
                Are you sure you want to delete
                <span class="font-semibold text-slate-950">{{
                    selectedUser?.name
                }}</span
                >?
            </p>

            <div class="mt-6 flex justify-end gap-3">
                <Button
                    label="Cancel"
                    severity="secondary"
                    outlined
                    @click="deleteDialogVisible = false"
                />
                <Button
                    label="Delete"
                    severity="danger"
                    icon="pi pi-trash"
                    :loading="deleting"
                    @click="deleteUser"
                />
            </div>
        </Dialog>
    </section>
</template>
