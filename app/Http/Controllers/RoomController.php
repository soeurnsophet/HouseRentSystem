<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->integer('per_page', 10), 100);

        $rooms = Room::query()
            ->with(['floor.building', 'roomType'])
            ->when($request->floor_id, function ($query, $floorId) {
                $query->where('floor_id', $floorId);
            })
            ->when($request->room_type_id, function ($query, $roomTypeId) {
                $query->where('room_type_id', $roomTypeId);
            })
            ->when($request->building_id, function ($query, $buildingId) {
                $query->whereHas('floor', function ($query) use ($buildingId) {
                    $query->where('building_id', $buildingId);
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('room_number', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'rooms' => $rooms->items(),
            'meta' => [
                'current_page' => $rooms->currentPage(),
                'per_page' => $rooms->perPage(),
                'total' => $rooms->total(),
                'last_page' => $rooms->lastPage(),
                'total_rooms' => Room::count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'floor_id' => ['required', 'exists:floors,id'],
            'room_type_id' => ['nullable', 'exists:room_types,id'],
            'room_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('rooms', 'room_number')->where('floor_id', $request->floor_id),
            ],
            'status' => ['required', Rule::in(['available', 'occupied', 'maintenance', 'reserved'])],
        ]);

        $room = Room::create($data);

        return response()->json([
            'message' => 'Room created successfully.',
            'room' => $room->load(['floor.building', 'roomType']),
        ], 201);
    }

    public function show(Room $room)
    {
        return response()->json([
            'room' => $room->load(['floor.building', 'roomType']),
        ]);
    }

    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'floor_id' => ['required', 'exists:floors,id'],
            'room_type_id' => ['nullable', 'exists:room_types,id'],
            'room_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('rooms', 'room_number')
                    ->where('floor_id', $request->floor_id)
                    ->ignore($room->id),
            ],
            'status' => ['required', Rule::in(['available', 'occupied', 'maintenance', 'reserved'])],
        ]);

        $room->update($data);

        return response()->json([
            'message' => 'Room updated successfully.',
            'room' => $room->load(['floor.building', 'roomType']),
        ]);
    }

    public function destroy(Room $room)
    {
        $room->delete();

        return response()->json([
            'message' => 'Room deleted successfully.',
        ]);
    }
}
