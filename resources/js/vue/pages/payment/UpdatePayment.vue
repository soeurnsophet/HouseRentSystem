<script setup>
import { computed, inject, onMounted, ref } from "vue";
import { storeToRefs } from "pinia";
import { useToast } from "primevue";
import useBillStore from "../../stores/bill.store";
import usePaymentStore from "../../stores/payment.store";
import { MessageSuccess } from "../../utils/Message";

const dialogRef = inject("dialogRef");
const payment = dialogRef.value.data.payment;
const toast = useToast();
const billStore = useBillStore();
const paymentStore = usePaymentStore();
const { bills } = storeToRefs(billStore);
const { saving } = storeToRefs(paymentStore);

const form = ref({
    bill_id: payment.bill_id,
    payment_date: payment.payment_date ? new Date(payment.payment_date) : new Date(),
    payment_method: payment.payment_method || "cash",
    amount: Number(payment.amount || 0),
});

const errors = ref({});

const methodOptions = [
    { label: "Cash", value: "cash" },
    { label: "Bank Transfer", value: "bank_transfer" },
    { label: "Card", value: "card" },
    { label: "Other", value: "other" },
];

const paidWithoutCurrent = (bill) => {
    const paid = Number(bill.paid_amount || 0);

    return bill.id === payment.bill_id ? paid - Number(payment.amount || 0) : paid;
};

const remainingAmount = (bill) =>
    Math.max(Number(bill.amount || 0) - paidWithoutCurrent(bill), 0);

const billOptions = computed(() =>
    bills.value
        .filter(
            (bill) =>
                bill.id === payment.bill_id ||
                (bill.status !== "paid" && remainingAmount(bill) > 0),
        )
        .map((bill) => ({
            ...bill,
            label: `${bill.booking?.tenant?.name || "Tenant"} - ${bill.booking?.room?.room_number || "Room"} - ${formatCurrency(remainingAmount(bill))} available`,
        })),
);

const selectedBill = computed(() =>
    bills.value.find((bill) => bill.id === form.value.bill_id),
);

const selectedRemaining = computed(() =>
    selectedBill.value ? remainingAmount(selectedBill.value) : 0,
);

const errorFor = (field) => {
    const error = errors.value[field];
    return Array.isArray(error) ? error[0] : error;
};

const formatCurrency = (value) =>
    new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
    }).format(Number(value || 0));

onMounted(() => billStore.fetchBills({ per_page: 100 }));

const submit = async () => {
    errors.value = {};

    try {
        const res = await paymentStore.updatePayment(payment.id, {
            ...form.value,
            payment_date: form.value.payment_date.toISOString(),
        });
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
        <div class="rounded-lg border border-sky-100 bg-sky-50 p-4">
            <div class="flex items-center gap-3">
                <span
                    class="grid h-10 w-10 place-items-center rounded-md bg-white text-sky-700"
                >
                    <i class="fa-solid fa-pen-to-square"></i>
                </span>
                <div>
                    <p class="font-semibold text-slate-950">Update Payment</p>
                    <p class="text-sm text-slate-600">
                        Adjust the payment date, method, or amount.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    Bill
                </label>
                <Select
                    v-model="form.bill_id"
                    :options="billOptions"
                    option-label="label"
                    option-value="id"
                    placeholder="Select bill"
                    class="w-full"
                    filter
                />
                <Message
                    v-if="errorFor('bill_id')"
                    severity="error"
                    class="mt-2"
                    variant="simple"
                >
                    {{ errorFor("bill_id") }}
                </Message>
                <p v-if="selectedBill" class="mt-2 text-sm text-slate-500">
                    Available: {{ formatCurrency(selectedRemaining) }}
                </p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    Payment Date
                </label>
                <DatePicker v-model="form.payment_date" showIcon fluid />
                <Message
                    v-if="errorFor('payment_date')"
                    severity="error"
                    class="mt-2"
                    variant="simple"
                >
                    {{ errorFor("payment_date") }}
                </Message>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    Method
                </label>
                <Select
                    v-model="form.payment_method"
                    :options="methodOptions"
                    option-label="label"
                    option-value="value"
                    placeholder="Payment method"
                    class="w-full"
                />
                <Message
                    v-if="errorFor('payment_method')"
                    severity="error"
                    class="mt-2"
                    variant="simple"
                >
                    {{ errorFor("payment_method") }}
                </Message>
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    Amount
                </label>
                <InputNumber
                    v-model="form.amount"
                    mode="currency"
                    currency="USD"
                    locale="en-US"
                    class="w-full"
                    fluid
                />
                <Message
                    v-if="errorFor('amount')"
                    severity="error"
                    class="mt-2"
                    variant="simple"
                >
                    {{ errorFor("amount") }}
                </Message>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <Button
                label="Cancel"
                icon="fa-solid fa-ban"
                severity="secondary"
                outlined
                type="button"
                @click="closeDialog"
            />
            <Button
                label="Save Payment"
                icon="fa-solid fa-save"
                type="submit"
                :loading="saving"
            />
        </div>
    </form>
</template>
