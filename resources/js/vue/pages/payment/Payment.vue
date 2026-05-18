<script setup>
import { computed, defineAsyncComponent, onMounted, reactive, ref } from "vue";
import { storeToRefs } from "pinia";
import { useConfirm, useDialog, useToast } from "primevue";
import usePaymentStore from "../../stores/payment.store";
import { MessageSuccess } from "../../utils/Message";

const CreatePayment = defineAsyncComponent(() => import("./CreatePayment.vue"));
const UpdatePayment = defineAsyncComponent(() => import("./UpdatePayment.vue"));

const paymentStore = usePaymentStore();
const { payments, meta, loading } = storeToRefs(paymentStore);
const dialog = useDialog();
const confirm = useConfirm();
const toast = useToast();
const menu = ref();
const selectedPayment = ref(null);

const lazyParams = reactive({
    first: 0,
    page: 0,
    rows: 10,
    search: "",
    totalRecords: 0,
});

const summary = computed(() => ({
    total: meta.value.total_payments || lazyParams.totalRecords,
    paid: Number(meta.value.total_paid || 0),
}));

const fetchPayments = async () => {
    await paymentStore.fetchPayments({
        page: lazyParams.page + 1,
        per_page: lazyParams.rows,
        search: lazyParams.search || undefined,
    });

    lazyParams.totalRecords = meta.value.total || 0;
};

let searchTimer;
const onSearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        lazyParams.first = 0;
        lazyParams.page = 0;
        fetchPayments();
    }, 300);
};

const onPage = (event) => {
    lazyParams.first = event.first;
    lazyParams.page = event.page;
    lazyParams.rows = event.rows;
    fetchPayments();
};

const clearFilters = () => {
    lazyParams.first = 0;
    lazyParams.page = 0;
    lazyParams.search = "";
    fetchPayments();
};

const openCreateDialog = () => {
    dialog.open(CreatePayment, {
        props: {
            position: "top",
            header: "Record Payment",
            modal: true,
            style: { width: "42rem" },
            breakpoints: { "960px": "75vw", "640px": "92vw" },
            draggable: false,
        },
        onClose: (options) => {
            if (options?.data?.created) {
                fetchPayments();
            }
        },
    });
};

const openUpdateDialog = () => {
    if (!selectedPayment.value) return;

    dialog.open(UpdatePayment, {
        data: { payment: selectedPayment.value },
        props: {
            position: "top",
            header: "Update Payment",
            modal: true,
            style: { width: "42rem" },
            breakpoints: { "960px": "75vw", "640px": "92vw" },
            draggable: false,
        },
        onClose: (options) => {
            if (options?.data?.updated) {
                fetchPayments();
            }
        },
    });
};

const confirmDelete = () => {
    if (!selectedPayment.value) return;

    confirm.require({
        message: `Are you sure you want to delete this payment from ${selectedPayment.value.bill?.booking?.tenant?.name || "this tenant"}?`,
        header: "Delete Confirmation",
        icon: "fa-solid fa-triangle-exclamation",
        rejectProps: {
            label: "Cancel",
            icon: "fa-solid fa-ban",
            severity: "secondary",
            outlined: true,
        },
        acceptProps: {
            label: "Delete",
            icon: "fa-solid fa-trash",
            severity: "danger",
        },
        accept: async () => {
            const res = await paymentStore.deletePayment(
                selectedPayment.value.id,
            );
            MessageSuccess("", res.message, toast);

            if (payments.value.length === 1 && lazyParams.page > 0) {
                lazyParams.page -= 1;
                lazyParams.first = lazyParams.page * lazyParams.rows;
            }

            await fetchPayments();
        },
    });
};

const actionItems = ref([
    {
        label: "Actions",
        items: [
            {
                label: "Edit",
                icon: "fa-solid fa-pen",
                command: openUpdateDialog,
            },
            {
                label: "Delete",
                icon: "fa-solid fa-trash",
                command: confirmDelete,
            },
        ],
    },
]);

const toggle = (event, payment) => {
    selectedPayment.value = payment;
    menu.value.toggle(event);
};

const formatDate = (value) => {
    if (!value) return "-";

    return new Intl.DateTimeFormat("en-US", {
        year: "numeric",
        month: "short",
        day: "2-digit",
    }).format(new Date(value));
};

const formatCurrency = (value) =>
    new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
    }).format(Number(value || 0));

const methodLabel = (method) =>
    String(method || "-")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (letter) => letter.toUpperCase());

const roomLabel = (payment) => {
    const room = payment.bill?.booking?.room;
    const building = room?.floor?.building?.building_name;

    if (!room) return "-";

    return `${building ? `${building} / ` : ""}Room ${room.room_number}`;
};

onMounted(fetchPayments);
</script>

<template>
    <section class="space-y-5">
        <div
            class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
        >
            <div>
                <h1 class="mt-2 text-3xl font-semibold text-slate-950">
                    Payments
                </h1>
                <p class="mt-2 text-sm text-slate-600">
                    Record rent payments and review payment history.
                </p>
            </div>

            <Button
                label="Record Payment"
                icon="fa-solid fa-money-bill-wave"
                @click="openCreateDialog"
            />
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <div
                class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
            >
                <p class="text-sm text-slate-500">Total Payments</p>
                <p class="mt-1 text-2xl font-semibold text-slate-950">
                    {{ summary.total }}
                </p>
            </div>
            <div
                class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
            >
                <p class="text-sm text-slate-500">Total Paid</p>
                <p class="mt-1 text-2xl font-semibold text-emerald-700">
                    {{ formatCurrency(summary.paid) }}
                </p>
            </div>
        </div>

        <section
            class="overflow-hidden rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
        >
            <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center">
                <IconField class="w-full md:w-80">
                    <InputIcon class="fa-solid fa-magnifying-glass" />
                    <InputText
                        v-model="lazyParams.search"
                        class="w-full"
                        placeholder="Search tenant, room, or method"
                        @input="onSearch"
                        @keydown.enter="onSearch"
                    />
                </IconField>

                <Button
                    icon="fa-solid fa-filter-circle-xmark"
                    label="Reset"
                    severity="secondary"
                    outlined
                    @click="clearFilters"
                />
            </div>

            <DataTable
                :value="payments"
                :loading="loading"
                lazy
                :paginator="payments.length > 0"
                :first="lazyParams.first"
                :rows="lazyParams.rows"
                :total-records="lazyParams.totalRecords"
                :rows-per-page-options="[10, 25, 50]"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} payments"
                data-key="id"
                responsive-layout="scroll"
                class="p-datatable-sm"
                striped-rows
                row-hover
                @page="onPage"
            >
                <template #empty>
                    <div
                        class="flex flex-col items-center justify-center py-16 text-center"
                    >
                        <i
                            class="fa-solid fa-money-check-dollar text-5xl text-slate-300"
                        ></i>
                        <p class="mt-4 text-lg font-semibold text-slate-800">
                            No payments found
                        </p>
                        <p class="mt-1 text-sm text-slate-500">
                            Record a payment after a bill has been created.
                        </p>
                    </div>
                </template>

                <Column header="No" class="w-20">
                    <template #body="{ index }">
                        {{ lazyParams.first + index + 1 }}
                    </template>
                </Column>

                <Column header="Tenant">
                    <template #body="{ data }">
                        <div>
                            <p class="font-semibold text-slate-900">
                                {{ data.bill?.booking?.tenant?.name || "-" }}
                            </p>
                            <p class="text-sm text-slate-500">
                                {{
                                    data.bill?.booking?.tenant?.phone ||
                                    data.bill?.booking?.tenant?.email ||
                                    "-"
                                }}
                            </p>
                        </div>
                    </template>
                </Column>

                <Column header="Room">
                    <template #body="{ data }">
                        {{ roomLabel(data) }}
                    </template>
                </Column>

                <Column header="Payment Date">
                    <template #body="{ data }">
                        {{ formatDate(data.payment_date) }}
                    </template>
                </Column>

                <Column header="Method">
                    <template #body="{ data }">
                        <Tag
                            :value="methodLabel(data.payment_method)"
                            severity="info"
                            rounded
                        />
                    </template>
                </Column>

                <Column header="Amount">
                    <template #body="{ data }">
                        <span class="font-semibold text-emerald-700">
                            {{ formatCurrency(data.amount) }}
                        </span>
                    </template>
                </Column>

                <Column header="Actions" class="w-20">
                    <template #body="{ data }">
                        <div class="flex justify-center">
                            <Button
                                icon="fa-solid fa-ellipsis"
                                severity="secondary"
                                text
                                rounded
                                aria-haspopup="true"
                                aria-controls="payment_menu"
                                @click="toggle($event, data)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <Menu
                ref="menu"
                id="payment_menu"
                :model="actionItems"
                :popup="true"
            />
        </section>
    </section>
</template>
