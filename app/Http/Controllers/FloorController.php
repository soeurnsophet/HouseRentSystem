<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use Illuminate\Http\Request;

class FloorController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->integer('per_page', 5), 100);
        $building_id = $request->building_id;
        $floors = Floor::query()
            ->with('building')
            ->withCount('rooms')
            ->when($building_id, function ($query) use ($building_id) {
                $query->where('building_id', $building_id);
            })
            ->latest()
            ->paginate($perPage);
        return response()->json([
            'floors' => $floors->items(),
            'meta' => [
                'current_page' => $floors->currentPage(),
                'per_page' => $floors->perPage(),
                'total' => $floors->total(),
                'last_page' => $floors->lastPage(),
            ],
        ]);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'building_id' => ['required', 'exists:buildings,id'],
            'base_price'  => ['required', 'numeric'],
            'description' => ['nullable', 'string'],
        ]);
        $floor = Floor::create($data);
        return response()->json([
            'message' => 'Floor created successfully.',
            'floor' => $floor,
        ], 201);
    }
    public function show(Floor $floor)
    {
        return response()->json([
            'floor' => $floor,
        ]);
    }
    public function update(Request $request, Floor $floor)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'building_id' => ['required', 'exists:buildings,id'],
            'base_price'  => ['required', 'numeric'],
            'description' => ['nullable', 'string'],
        ]);

        $floor->update($data);
        return response()->json([
            'message' => 'Floor updated successfully.',
        ]);
    }

    public function destroy(Floor $floor)
    {
        $floor->delete();
        return response()->json([
            'message' => 'Floor deleted successfully.',
        ]);
    }
}
