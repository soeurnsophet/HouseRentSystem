<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->integer('per_page', 10), 100);
        $search = $request->string('search')->toString();

        $bills = Bill::query()
            ->withSum('payments as paid_amount', 'amount')
            ->with([
                'billType',
                'payments',
                'booking.room.floor.building',
                'booking.tenant:id,name,username,email,phone',
                'creator:id,name',
            ])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('booking.tenant', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })->orWhereHas('booking.room', function ($query) use ($search) {
                        $query->where('room_number', 'like', "%{$search}%");
                    });
                });
            })
            ->latest()
            ->paginate($perPage);
        return response()->json([
            'bills' => $bills->items(),
            'meta' => [
                'current_page' => $bills->currentPage(),
                'per_page' => $bills->perPage(),
                'total' => $bills->total(),
                'last_page' => $bills->lastPage(),
                'total_bills' => Bill::count(),
                'pending_bills' => Bill::where('status', 'pending')->count(),
                'paid_bills' => Bill::where('status', 'paid')->count(),
                'total_amount' => Bill::sum('amount'),
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // bill
            'booking_id' => ['required', 'exists:bookings,id'],
            'amount' => ['nullable', 'numeric'],
            'bill_date' => ['required', 'date'],
            // bill type
            'bill_details' => ['nullable', 'array'],
            'bill_details.*.type_name' => ['required', 'string'],
            'bill_details.*.previous_reading' => ['nullable', 'numeric'],
            'bill_details.*.current_reading' => ['nullable', 'numeric'],
            'bill_details.*.rate' => ['nullable', 'numeric'],
            'bill_details.*.amount' => ['nullable', 'numeric'],
            'bill_details.*.description' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            // 1 bill
            $bill = Bill::create([
                'booking_id' => $validated['booking_id'],
                'amount' => $validated['amount'],
                'bill_date' => $this->formatBillDate($validated['bill_date']),
                'status' => 'pending',
                'created_by' => Auth::user()?->id,
            ]);

            $this->saveBillDetails($bill, $validated['bill_details'] ?? []);
            $this->activateBooking($validated['booking_id']);
        });

        return response()->json([
            'message' => 'Bill created successfully.',
        ], 201);
    }

    public function show(Bill $bill)
    {
        return response()->json([
            'bill' => $bill->load([
                'billType',
                'payments',
                'booking.room.floor.building',
                'booking.tenant:id,name,username,email,phone',
                'creator:id,name',
            ]),
        ]);
    }

    public function update(Request $request, Bill $bill)
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'amount' => ['required', 'numeric'],
            'bill_date' => ['required', 'date'],
            'bill_details' => ['nullable', 'array'],
            'bill_details.*.type_name' => ['required', 'string'],
            'bill_details.*.previous_reading' => ['nullable', 'numeric'],
            'bill_details.*.current_reading' => ['nullable', 'numeric'],
            'bill_details.*.rate' => ['nullable', 'numeric'],
            'bill_details.*.amount' => ['nullable', 'numeric'],
            'bill_details.*.description' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($bill, $validated) {
            $bill->update([
                'booking_id' => $validated['booking_id'],
                'amount' => $validated['amount'],
                'bill_date' => $this->formatBillDate($validated['bill_date']),
            ]);

            $bill->billType()->delete();
            // bill type
            $this->saveBillDetails($bill, $validated['bill_details'] ?? []);

            $this->activateBooking($validated['booking_id']);
            $this->syncBillStatus($bill->fresh());
        });

        return response()->json([
            'message' => 'Bill updated successfully.',
            'bill' => $bill->fresh()->load([
                'billType',
                'payments',
                'booking.room.floor.building',
                'booking.tenant:id,name,username,email,phone',
                'creator:id,name',
            ]),
        ]);
    }

    public function destroy(Bill $bill)
    {
        $bill->delete();

        return response()->json([
            'message' => 'Bill deleted successfully.',
        ]);
    }

    private function formatBillDate(string $billDate): string
    {
        return Carbon::parse($billDate)->format('Y-m-d');
    }

    private function saveBillDetails(Bill $bill, array $details): void
    {
        foreach ($details as $detail) {
            $bill->billType()->create([
                'type_name' => $detail['type_name'],
                'previous_reading' => $detail['previous_reading'] ?? 0,
                'current_reading' => $detail['current_reading'] ?? 0,
                'rate' => $detail['rate'] ?? 0,
                'amount' => $detail['amount'] ?? 0,
                'description' => $detail['description'] ?? null,
            ]);
        }
    }


    private function activateBooking(int $bookingId): void
    {
        $booking = Booking::with('room')->findOrFail($bookingId);
        $booking->update(['status' => 'active']);
        $booking->room()->update(['status' => 'occupied']);
    }

    private function syncBillStatus(Bill $bill): void
    {
        $status = $bill->payments()->exists() ? 'paid' : 'pending';

        $bill->update(['status' => $status]);

        if ($status === 'paid') {
            $this->completeBooking($bill->booking_id);
        }
    }

    private function completeBooking(int $bookingId): void
    {
        $booking = Booking::with('room')->findOrFail($bookingId);
        $booking->update(['status' => 'completed']);
        $booking->room()->update(['status' => 'available']);
    }
}
