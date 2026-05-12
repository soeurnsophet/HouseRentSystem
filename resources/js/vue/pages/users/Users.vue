<script setup>
import { onMounted, reactive, ref } from "vue";
import { storeToRefs } from "pinia";
import { useDialog } from "primevue/usedialog";
import useUserStore from "../../stores/users.store";
import CreateUser from "./Create.vue";
import UpdateUser from "./Update.vue";

const dialog = useDialog();
const userStore = useUserStore();
const { users, meta, loading, deleting } = storeToRefs(userStore);
const deleteDialogVisible = ref(false);
const selectedUser = ref(null);

const lazyParams = reactive({
    first: 0,
    page: 0,
    rows: 10,
    totalRecords: 0,
    search: "",
});

const fetchUsers = async () => {
    await userStore.fetchUsers({
        page: lazyParams.page + 1,
        per_page: lazyParams.rows,
        search: lazyParams.search || undefined,
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
                fetchUsers();
            }
        },
    });
};

const confirmDelete = (user) => {
    selectedUser.value = user;
    deleteDialogVisible.value = true;
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
</script>

<template>
    <section class="space-y-5">
        <div
            class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
        >
            <div>
                <p
                    class="text-sm font-medium uppercase tracking-wide text-teal-700"
                >
                    Management
                </p>
                <h1 class="mt-2 text-3xl font-semibold text-slate-950">
                    Users
                </h1>
                <p class="mt-2 text-sm text-slate-600">
                    Load users page by page and manage accounts.
                </p>
            </div>

            <Button
                label="Add User"
                icon="pi pi-plus"
                @click="openCreateDialog"
            />
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div
                class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
            >
                <IconField class="w-full md:max-w-sm">
                    <InputIcon class="pi pi-search" />
                    <InputText
                        v-model="lazyParams.search"
                        class="w-full"
                        placeholder="Search name, phone, email"
                        @input="onSearch"
                    />
                </IconField>

                <span class="text-sm text-slate-500">
                    Showing {{ users.length }} of
                    {{ lazyParams.totalRecords }} users
                </span>
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
                        <Tag :value="data.role" severity="info" />
                    </template>
                </Column>
                <Column header="Actions" class="w-36">
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <Button
                                icon="pi pi-pencil"
                                severity="secondary"
                                text
                                rounded
                                @click="openEditDialog(data)"
                            />
                            <Button
                                icon="pi pi-trash"
                                severity="danger"
                                text
                                rounded
                                @click="confirmDelete(data)"
                            />
                        </div>
                    </template>
                </Column>
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
