<script setup>
import { ref, computed, inject } from "vue";
import useBillStore from "../../stores/bill.store";
import { storeToRefs } from "pinia";
import { MessageSuccess } from "../../utils/Message";
import { useToast } from "primevue";
const toast = useToast();
const dialogRef = inject("dialogRef");
const booking = dialogRef.value.data.booking;
const bookingPrice = computed(() => {
    return booking?.room?.floor?.base_price || 0;
});

const billStore = useBillStore();
const { saving } = storeToRefs(billStore);

const form = ref({
    booking_id: booking.id,
    bill_date: new Date(),
    description: "",
    amount: 0,

    bill_details: [
        {
            type_name: "Rent",
            previous_reading: 0,
            current_reading: 0,
            rate: 0,
            description: "",
            amount: bookingPrice.value || 0,
        },
        {
            type_name: "Water",
            previous_reading: 0,
            current_reading: 0,
            rate: 1.5,
            description: "",
            amount: 0,
        },
        {
            type_name: "Electricity",
            previous_reading: 0,
            current_reading: 0,
            rate: 0.4,
            description: "",
            amount: 0,
        },
    ],
});

const electricity = computed(() => {
    return form.value.bill_details.find(
        (item) => item.type_name === "Electricity",
    );
});

const water = computed(() => {
    return form.value.bill_details.find((item) => item.type_name === "Water");
});

const electricityTotal = computed(() => {
    const usage =
        electricity.value.current_reading - electricity.value.previous_reading;

    return usage > 0 ? usage * electricity.value.rate : 0;
});

const waterTotal = computed(() => {
    const usage = water.value.current_reading - water.value.previous_reading;

    return usage > 0 ? usage * water.value.rate : 0;
});

const additionalFees = computed(() => {
    return form.value.bill_details.filter(
        (item) =>
            item.type_name !== "Rent" &&
            item.type_name !== "Electricity" &&
            item.type_name !== "Water",
    );
});

const additionalFeesTotal = computed(() => {
    return additionalFees.value.reduce((total, fee) => {
        return total + Number(fee.amount || 0);
    }, 0);
});

const grandTotal = computed(() => {
    return (
        Number(form.value.bill_details[0].amount || 0) +
        electricityTotal.value +
        waterTotal.value +
        additionalFeesTotal.value
    );
});

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

const onSubmit = async () => {
    form.value.amount = grandTotal.value;
    form.value.bill_date = form.value.bill_date.toISOString();
    const res = await billStore.createBill(form.value);
    MessageSuccess("", res.message, toast);
    dialogRef.value.close({ created: true });
};
</script>

<template>
    <form class="max-w-3xl mx-auto" @submit.prevent="onSubmit">
        <!-- HEADER -->
        <div class="flex flex-col gap-1 mb-6">
            <h2 class="text-2xl font-bold text-surface-900">Create New Bill</h2>

            <p class="text-sm text-surface-500">
                Create a new bill for your tenants
            </p>
        </div>

        <div class="flex flex-col gap-5">
            <!-- TOP -->
            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-sm">Due Date</label>

                    <DatePicker v-model="form.bill_date" showIcon fluid />
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-sm"> Description </label>

                    <InputText
                        v-model="form.description"
                        placeholder="Monthly bill"
                    />
                </div>
            </div>

            <!-- ROOM RENT -->
            <Card>
                <template #content>
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center gap-2">
                            <i class="pi pi-home text-emerald-500"></i>

                            <span class="font-semibold"> Room Rent </span>
                        </div>

                        <InputNumber
                            v-model="form.bill_details[0].amount"
                            fluid
                        />
                    </div>
                </template>
            </Card>

            <!-- ELECTRICITY -->
            <Card>
                <template #content>
                    <div class="flex flex-col gap-5">
                        <div class="flex items-center gap-2">
                            <i class="pi pi-bolt text-yellow-500"></i>

                            <span class="font-semibold"> Electricity </span>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div class="flex flex-col gap-2">
                                <label class="text-sm"> Previous </label>

                                <InputNumber
                                    v-model="electricity.previous_reading"
                                    fluid
                                />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="text-sm"> Current </label>

                                <InputNumber
                                    v-model="electricity.current_reading"
                                    fluid
                                />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="text-sm"> Rate </label>

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

            <!-- WATER -->
            <Card>
                <template #content>
                    <div class="flex flex-col gap-5">
                        <div class="flex items-center gap-2">
                            <i class="pi pi-refresh text-cyan-500"></i>

                            <span class="font-semibold"> Water </span>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div class="flex flex-col gap-2">
                                <label class="text-sm"> Previous </label>

                                <InputNumber
                                    v-model="water.previous_reading"
                                    fluid
                                />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="text-sm"> Current </label>

                                <InputNumber
                                    v-model="water.current_reading"
                                    fluid
                                />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="text-sm"> Rate </label>

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

            <!-- ADDITIONAL FEES -->
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
                                @click="addFee"
                            />
                        </div>

                        <div
                            v-for="(fee, index) in additionalFees"
                            :key="index"
                            class="grid grid-cols-12 gap-3 items-end"
                        >
                            <div class="col-span-5 flex flex-col gap-2">
                                <label class="text-sm"> Fee Name </label>

                                <InputText
                                    v-model="fee.type_name"
                                    placeholder="Internet"
                                />
                            </div>

                            <div class="col-span-5 flex flex-col gap-2">
                                <label class="text-sm"> Amount </label>

                                <InputNumber v-model="fee.amount" fluid />
                            </div>

                            <div class="col-span-2">
                                <Button
                                    icon="pi pi-trash"
                                    severity="danger"
                                    text
                                    rounded
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

            <!-- TOTAL -->
            <div
                class="bg-surface-100 rounded-xl p-4 flex items-center justify-between"
            >
                <span class="text-lg font-bold"> Grand Total </span>

                <span class="text-2xl font-bold text-emerald-600">
                    ${{ grandTotal.toFixed(2) }}
                </span>
            </div>

            <!-- FOOTER -->
            <div class="flex justify-end gap-3">
                <Button label="Cancel" severity="secondary" outlined />

                <Button label="Create Bill" icon="pi pi-check" type="submit" />
            </div>
        </div>
    </form>
</template>
