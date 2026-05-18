<script setup>
import { computed, inject, ref } from "vue";
import { storeToRefs } from "pinia";
import { useToast } from "primevue";
import useBillStore from "../../stores/bill.store";
import { MessageSuccess } from "../../utils/Message";

const toast = useToast();
const dialogRef = inject("dialogRef");
const bill = dialogRef.value.data.bill;
const billStore = useBillStore();
const { saving } = storeToRefs(billStore);

const makeDetail = (typeName, fallback = {}) => {
    const detail = bill.bill_type?.find((item) => item.type_name === typeName);

    return {
        type_name: typeName,
        previous_reading: Number(
            detail?.previous_reading ?? fallback.previous_reading ?? 0,
        ),
        current_reading: Number(
            detail?.current_reading ?? fallback.current_reading ?? 0,
        ),
        rate: Number(detail?.rate ?? fallback.rate ?? 0),
        description: detail?.description || "",
        amount: Number(detail?.amount ?? fallback.amount ?? 0),
    };
};

const details = bill.bill_type || [];
const fixedTypes = ["Rent", "Water", "Electricity"];
const detailTotal = (detail) => {
    const usage = detail.current_reading - detail.previous_reading;

    return usage > 0 ? usage * detail.rate : 0;
};

const form = ref({
    booking_id: bill.booking_id,
    bill_date: bill.bill_date ? new Date(bill.bill_date) : new Date(),
    amount: Number(bill.amount || 0),
    bill_details: [
        makeDetail("Rent", { amount: Number(bill.amount || 0) }),
        makeDetail("Water", { rate: 1.5 }),
        makeDetail("Electricity", { rate: 0.4 }),
        ...details
            .filter((item) => !fixedTypes.includes(item.type_name))
            .map((item) => ({
                type_name: item.type_name,
                previous_reading: Number(item.previous_reading || 0),
                current_reading: Number(item.current_reading || 0),
                rate: Number(item.rate || 0),
                description: item.description || "",
                amount: Number(item.amount || 0),
            })),
    ],
});

const rent = form.value.bill_details[0];
const hasSavedRentAmount = bill.bill_type?.some(
    (item) => item.type_name === "Rent" && item.amount != null,
);

if (!hasSavedRentAmount) {
    const savedTotal = Number(bill.amount || 0);
    const utilitiesTotal =
        detailTotal(form.value.bill_details[1]) +
        detailTotal(form.value.bill_details[2]);
    const feesTotal = form.value.bill_details
        .slice(3)
        .reduce((total, item) => total + Number(item.amount || 0), 0);

    rent.amount = Math.max(savedTotal - utilitiesTotal - feesTotal, 0);
}

const errors = ref({});

const electricity = computed(() =>
    form.value.bill_details.find((item) => item.type_name === "Electricity"),
);

const water = computed(() =>
    form.value.bill_details.find((item) => item.type_name === "Water"),
);

const electricityTotal = computed(() => {
    return detailTotal(electricity.value);
});

const waterTotal = computed(() => {
    return detailTotal(water.value);
});

const additionalFees = computed(() =>
    form.value.bill_details.filter(
        (item) => !fixedTypes.includes(item.type_name),
    ),
);

const additionalFeesTotal = computed(() =>
    additionalFees.value.reduce(
        (total, fee) => total + Number(fee.amount || 0),
        0,
    ),
);

const grandTotal = computed(
    () =>
        Number(form.value.bill_details[0].amount || 0) +
        electricityTotal.value +
        waterTotal.value +
        additionalFeesTotal.value,
);

const errorFor = (field) => {
    const error = errors.value[field];
    return Array.isArray(error) ? error[0] : error;
};

const addFee = () => {
    form.value.bill_details.push({
        type_name: "",
        previous_reading: 0,
        current_reading: 0,
        rate: 0,
        description: "",
        amount: 0,
    });
};

const removeFee = (index) => {
    form.value.bill_details.splice(index, 1);
};

const submit = async () => {
    electricity.value.amount = electricityTotal.value;
    water.value.amount = waterTotal.value;
    form.value.amount = grandTotal.value;

    try {
        const res = await billStore.updateBill(bill.id, {
            ...form.value,
            bill_date: form.value.bill_date.toISOString(),
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
    <form class="mx-auto max-w-3xl" @submit.prevent="submit">
        <div class="mb-6 flex flex-col gap-1">
            <h2 class="text-2xl font-bold text-surface-900">Update Bill</h2>
            <p class="text-sm text-surface-500">
                Edit bill date, utility readings, rent, and extra fees.
            </p>
        </div>

        <div class="flex flex-col gap-5">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold">Bill Date</label>
                    <DatePicker v-model="form.bill_date" showIcon fluid />
                    <Message
                        v-if="errorFor('bill_date')"
                        severity="error"
                        variant="simple"
                    >
                        {{ errorFor("bill_date") }}
                    </Message>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold">Tenant</label>
                    <InputText
                        :model-value="bill.booking?.tenant?.name || '-'"
                        readonly
                    />
                </div>
            </div>

            <Card>
                <template #content>
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center gap-2">
                            <i class="pi pi-home text-emerald-500"></i>
                            <span class="font-semibold">Room Rent</span>
                        </div>
                        <InputNumber
                            v-model="form.bill_details[0].amount"
                            mode="currency"
                            currency="USD"
                            locale="en-US"
                            fluid
                        />
                    </div>
                </template>
            </Card>

            <Card>
                <template #content>
                    <div class="flex flex-col gap-5">
                        <div class="flex items-center gap-2">
                            <i class="pi pi-bolt text-yellow-500"></i>
                            <span class="font-semibold">Electricity</span>
                        </div>

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                            <div class="flex flex-col gap-2">
                                <label class="text-sm">Previous</label>
                                <InputNumber
                                    v-model="electricity.previous_reading"
                                    fluid
                                />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-sm">Current</label>
                                <InputNumber
                                    v-model="electricity.current_reading"
                                    fluid
                                />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-sm">Rate</label>
                                <InputNumber v-model="electricity.rate" fluid />
                            </div>
                        </div>

                        <div
                            class="flex justify-end font-bold text-emerald-600"
                        >
                            Total: ${{ electricityTotal.toFixed(2) }}
                        </div>
                    </div>
                </template>
            </Card>

            <Card>
                <template #content>
                    <div class="flex flex-col gap-5">
                        <div class="flex items-center gap-2">
                            <i class="pi pi-refresh text-cyan-500"></i>
                            <span class="font-semibold">Water</span>
                        </div>

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                            <div class="flex flex-col gap-2">
                                <label class="text-sm">Previous</label>
                                <InputNumber
                                    v-model="water.previous_reading"
                                    fluid
                                />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-sm">Current</label>
                                <InputNumber
                                    v-model="water.current_reading"
                                    fluid
                                />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-sm">Rate</label>
                                <InputNumber v-model="water.rate" fluid />
                            </div>
                        </div>

                        <div
                            class="flex justify-end font-bold text-emerald-600"
                        >
                            Total: ${{ waterTotal.toFixed(2) }}
                        </div>
                    </div>
                </template>
            </Card>

            <Card>
                <template #content>
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold">Additional Fees</h3>
                            <Button
                                label="Add Fee"
                                icon="pi pi-plus"
                                size="small"
                                outlined
                                type="button"
                                @click="addFee"
                            />
                        </div>

                        <div
                            v-for="(fee, index) in additionalFees"
                            :key="index"
                            class="grid grid-cols-12 items-end gap-3"
                        >
                            <div
                                class="col-span-12 flex flex-col gap-2 md:col-span-5"
                            >
                                <label class="text-sm">Fee Name</label>
                                <InputText
                                    v-model="fee.type_name"
                                    placeholder="Internet"
                                />
                            </div>

                            <div
                                class="col-span-10 flex flex-col gap-2 md:col-span-5"
                            >
                                <label class="text-sm">Amount</label>
                                <InputNumber
                                    v-model="fee.amount"
                                    mode="currency"
                                    currency="USD"
                                    locale="en-US"
                                    fluid
                                />
                            </div>

                            <div class="col-span-2">
                                <Button
                                    icon="pi pi-trash"
                                    severity="danger"
                                    text
                                    rounded
                                    type="button"
                                    @click="
                                        removeFee(
                                            form.bill_details.indexOf(fee),
                                        )
                                    "
                                />
                            </div>
                        </div>
                    </div>
                </template>
            </Card>

            <div
                class="flex items-center justify-between rounded-xl bg-surface-100 p-4"
            >
                <span class="text-lg font-bold">Grand Total</span>
                <span class="text-2xl font-bold text-emerald-600">
                    ${{ grandTotal.toFixed(2) }}
                </span>
            </div>

            <div class="flex justify-end gap-3">
                <Button
                    label="Cancel"
                    severity="secondary"
                    outlined
                    type="button"
                    @click="closeDialog"
                />
                <Button
                    label="Save Bill"
                    icon="pi pi-check"
                    type="submit"
                    :loading="saving"
                />
            </div>
        </div>
    </form>
</template>
