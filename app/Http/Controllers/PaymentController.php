<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->integer('per_page', 10), 100);
        $search = $request->string('search')->toString();

        $payments = Payment::query()
            ->with([
                'bill.booking.room.floor.building',
                'bill.booking.tenant:id,name,username,email,phone',
                'creator:id,name',
            ])
            ->when($search, function ($query) use ($search) {
                $query->where('payment_method', 'like', "%{$search}%")
                    ->orWhereHas('bill.booking.tenant', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('bill.booking.room', function ($query) use ($search) {
                        $query->where('room_number', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'payments' => $payments->items(),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
                'last_page' => $payments->lastPage(),
                'total_payments' => Payment::count(),
                'total_paid' => Payment::sum('amount'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bill_id' => ['required', 'exists:bills,id'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $payment = DB::transaction(function () use ($data) {
            $bill = Bill::lockForUpdate()->findOrFail($data['bill_id']);

            if ($bill->status === 'paid') {
                throw ValidationException::withMessages([
                    'bill_id' => ['This bill is already paid.'],
                ]);
            }

            $remaining = $this->remainingAmount($bill);

            if ((float) $data['amount'] > $remaining) {
                throw ValidationException::withMessages([
                    'amount' => ['The payment amount cannot be greater than the remaining bill balance.'],
                ]);
            }

            $payment = Payment::create([
                'bill_id' => $data['bill_id'],
                'payment_date' => $this->formatPaymentDate($data['payment_date']),
                'payment_method' => $data['payment_method'],
                'amount' => $data['amount'],
                'created_by' => Auth::user()?->id,
            ]);

            $this->syncBillStatus($bill);

            return $payment;
        });

        return response()->json([
            'message' => 'Payment created successfully.',
            'payment' => $payment->load([
                'bill.booking.room.floor.building',
                'bill.booking.tenant:id,name,username,email,phone',
                'creator:id,name',
            ]),
        ], 201);
    }

    public function show(Payment $payment)
    {
        return response()->json([
            'payment' => $payment->load([
                'bill.booking.room.floor.building',
                'bill.booking.tenant:id,name,username,email,phone',
                'creator:id,name',
            ]),
        ]);
    }

    public function update(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'bill_id' => ['required', 'exists:bills,id'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        DB::transaction(function () use ($payment, $data) {
            $oldBill = $payment->bill;
            $bill = Bill::lockForUpdate()->findOrFail($data['bill_id']);
            $remaining = $this->remainingAmount($bill, $payment->id);

            if ((float) $data['amount'] > $remaining) {
                throw ValidationException::withMessages([
                    'amount' => ['The payment amount cannot be greater than the remaining bill balance.'],
                ]);
            }

            $payment->update([
                'bill_id' => $data['bill_id'],
                'payment_date' => $this->formatPaymentDate($data['payment_date']),
                'payment_method' => $data['payment_method'],
                'amount' => $data['amount'],
            ]);

            if ($oldBill && $oldBill->id !== $bill->id) {
                $this->syncBillStatus($oldBill);
            }

            $this->syncBillStatus($bill);
        });

        return response()->json([
            'message' => 'Payment updated successfully.',
            'payment' => $payment->fresh()->load([
                'bill.booking.room.floor.building',
                'bill.booking.tenant:id,name,username,email,phone',
                'creator:id,name',
            ]),
        ]);
    }

    public function destroy(Payment $payment)
    {
        DB::transaction(function () use ($payment) {
            $bill = $payment->bill;
            $payment->delete();

            if ($bill) {
                $this->syncBillStatus($bill);
            }
        });

        return response()->json([
            'message' => 'Payment deleted successfully.',
        ]);
    }

    private function formatPaymentDate(string $paymentDate): string
    {
        return Carbon::parse($paymentDate)->format('Y-m-d');
    }

    private function remainingAmount(Bill $bill, ?int $ignorePaymentId = null): float
    {
        $paid = $bill->payments()
            ->when($ignorePaymentId, fn ($query) => $query->whereKeyNot($ignorePaymentId))
            ->sum('amount');

        return max((float) $bill->amount - (float) $paid, 0);
    }

    private function syncBillStatus(Bill $bill): void
    {
        $status = $bill->payments()->exists() ? 'paid' : 'pending';

        $bill->update(['status' => $status]);
        $this->syncBookingStatus($bill, $status);
    }

    private function syncBookingStatus(Bill $bill, string $billStatus): void
    {
        $booking = $bill->booking()->with('room')->first();

        if (!$booking) {
            return;
        }

        if ($billStatus === 'paid') {
            $booking->update(['status' => 'completed']);
            $booking->room()->update(['status' => 'available']);
            return;
        }

        $booking->update(['status' => 'active']);
        $booking->room()->update(['status' => 'occupied']);
    }
}
