<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Booking;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    public function index()
    {
        $bills = Bill::with('billType')->get();
        return response()->json([
            'bills' => $bills
        ], 200);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            // bill
            'booking_id' => ['required', 'exists:bookings,id'],
            'amount' => ['nullable', 'numeric'],
            'bill_date' => ['required', 'date'],
            // 'created_by' => ['required', 'exists:users,id'],
            // bill type
            'bill_details' => ['nullable', 'array'],
            'bill_details.*.type_name' => ['required', 'string'],
            'bill_details.*.previous_reading' => ['nullable', 'numeric'],
            'bill_details.*.current_reading' => ['nullable', 'numeric'],
            'bill_details.*.rate' => ['nullable', 'numeric'],
            'bill_details.*.description' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            // 1 bill
            $billDate = DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $validated['bill_date'])->format('Y-m-d');
            $bill = Bill::create([
                'booking_id' => $validated['booking_id'],
                'amount' => $validated['amount'],
                'bill_date' => $billDate,
                'created_by' => Auth::user()->id,
            ]);

            // 2 bill type (details)
            foreach ($validated['bill_details'] as $detail) {
                $bill->billType()->create([
                    'bill_id' => $bill->id,
                    'type_name' => $detail['type_name'],
                    'previous_reading' => $detail['previous_reading'],
                    'current_reading' => $detail['current_reading'],
                    'rate' => $detail['rate'],
                    'description' => $detail['description'],
                ]);
            }
            Booking::where('id', $validated['booking_id'])->update(['status' => 'completed']);
        });
        return response()->json([
            'message' => 'Bill created successfully.',
        ], 201);
    }
}
