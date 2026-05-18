<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomTypeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->integer('per_page', 10), 100);

        $roomTypes = RoomType::query()
            ->withCount('rooms')
            ->when($request->search, function ($query, $search) {
                $query->where('type_name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage);
        return response()->json([
            'room_types' => $roomTypes->items(),
            'meta' => [
                'current_page' => $roomTypes->currentPage(),
                'per_page' => $roomTypes->perPage(),
                'total' => $roomTypes->total(),
                'last_page' => $roomTypes->lastPage(),
                'total_room_types' => RoomType::count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type_name' => ['required', 'string', 'max:255', 'unique:room_types,type_name'],
            'description' => ['nullable', 'string'],
        ]);

        $roomType = RoomType::create($data);

        return response()->json([
            'message' => 'Room type created successfully.',
            'room_type' => $roomType,
        ], 201);
    }

    public function show(RoomType $roomType)
    {
        return response()->json([
            'room_type' => $roomType->loadCount('rooms'),
        ]);
    }

    public function update(Request $request, RoomType $roomType)
    {
        $data = $request->validate([
            'type_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('room_types', 'type_name')->ignore($roomType->id),
            ],
            'description' => ['nullable', 'string'],
        ]);

        $roomType->update($data);

        return response()->json([
            'message' => 'Room type updated successfully.',
            'room_type' => $roomType,
        ]);
    }

    public function destroy(RoomType $roomType)
    {
        $roomType->delete();

        return response()->json([
            'message' => 'Room type deleted successfully.',
        ]);
    }
}
