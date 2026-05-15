<?php

namespace App\Http\Controllers;

use App\Http\Requests\Building\StoreBuildingRequest;
use App\Http\Requests\Building\UpdateBuildingRequest;
use App\Models\Building;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $buildings = Building::withCount([
            'floors',
            'rooms',
            'rooms as available_rooms' => fn($query) => $query->where('status', 'available'),
        ])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'buildings' => $buildings,
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBuildingRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        $building = Building::create($data);

        return [
            'building' => $building,
            'message' => 'Building created successfully.'
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(Building $building)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBuildingRequest $request, Building $building)
    {
        // $this->authorize('update', $building);
        $data = $request->validated();

        $building->update($data);

        return [
            'building' => $building,
            'message' => 'Building updated successfully.'
        ];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Building $building)
    {
        $this->authorize('delete', $building);
        $building->delete();

        return [
            'message' => 'Building deleted successfully.',
        ];
    }
}
