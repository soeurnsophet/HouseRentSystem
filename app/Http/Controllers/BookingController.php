<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->integer('per_page', 10), 100);
        $search = $request->string('search')->toString();

        $bookings = Booking::query()
            ->with(['room.floor.building', 'room.roomType', 'tenant:id,name,username,email,phone', 'creator:id,name'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->room_id, function ($query, $roomId) {
                $query->where('room_id', $roomId);
            })
            ->when($request->tenant_id, function ($query, $tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('tenant', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })->orWhereHas('room', function ($query) use ($search) {
                        $query->where('room_number', 'like', "%{$search}%");
                    });
                });
            })
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'bookings' => $bookings->items(),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
                'last_page' => $bookings->lastPage(),
                'total_bookings' => Booking::count(),
                'pending_bookings' => Booking::where('status', 'pending')->count(),
                'active_bookings' => Booking::where('status', 'active')->count(),
                'completed_bookings' => Booking::where('status', 'completed')->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'tenant_id' => ['required', 'exists:users,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['pending', 'active', 'completed'])],
        ]);

        $data['created_by'] = $request->user()?->id;

        $booking = Booking::create($data);

        return response()->json([
            'message' => 'Booking created successfully.',
            'booking' => $booking->load(['room.floor.building', 'room.roomType', 'tenant:id,name,username,email,phone', 'creator:id,name']),
        ], 201);
    }

    public function show(Booking $booking)
    {
        return response()->json([
            'booking' => $booking->load(['room.floor.building', 'room.roomType', 'tenant:id,name,username,email,phone', 'creator:id,name']),
        ]);
    }

    public function update(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'tenant_id' => ['required', 'exists:users,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['pending', 'active', 'completed'])],
        ]);

        $booking->update($data);

        return response()->json([
            'message' => 'Booking updated successfully.',
            'booking' => $booking->fresh()->load(['room.floor.building', 'room.roomType', 'tenant:id,name,username,email,phone', 'creator:id,name']),
        ]);
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();

        return response()->json([
            'message' => 'Booking deleted successfully.',
        ]);
    }
}
