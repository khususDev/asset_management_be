<?php

namespace App\Http\Controllers\Api\Administration\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Administration\Organization\Location;
use App\Services\Administration\MasterDataService;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function __construct(private readonly MasterDataService $masterDataService)
    {
    }

    public function index(Request $request)
    {
        $entries = $request->entries ?? 10;
        $search = $request->search ?? '';

        $query = Location::with('branch');

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhereHas('branch', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest()->paginate($entries),
        ]);
    }

    public function store(StoreLocationRequest $request)
    {
        $location = $this->masterDataService->createModel(new Location(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $location,
        ]);
    }

    public function show(string $id)
    {
        return response()->json([
            'success' => true,
            'data' => Location::with('branch')->findOrFail($id),
        ]);
    }

    public function update(UpdateLocationRequest $request, string $id)
    {
        $location = Location::findOrFail($id);
        $location = $this->masterDataService->updateModel($location, $request->validated());

        return response()->json([
            'success' => true,
            'data' => $location,
        ]);
    }

    public function destroy(string $id)
    {
        Location::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
