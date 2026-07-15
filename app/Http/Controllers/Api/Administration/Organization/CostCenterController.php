<?php

namespace App\Http\Controllers\Api\Administration\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCostCenterRequest;
use App\Http\Requests\UpdateCostCenterRequest;
use App\Models\Administration\Organization\CostCenter;
use App\Services\Administration\MasterDataService;
use Illuminate\Http\Request;

class CostCenterController extends Controller
{
    public function __construct(private readonly MasterDataService $masterDataService)
    {
    }

    public function index(Request $request)
    {
        $entries = $request->entries ?? 10;
        $search = $request->search ?? '';

        $query = CostCenter::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest()->paginate($entries),
        ]);
    }

    public function store(StoreCostCenterRequest $request)
    {
        $costcenter = $this->masterDataService->createModel(new CostCenter(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $costcenter,
        ]);
    }

    public function show(string $id)
    {
        return response()->json([
            'success' => true,
            'data' => CostCenter::findOrFail($id),
        ]);
    }

    public function update(UpdateCostCenterRequest $request, string $id)
    {
        $costcenter = CostCenter::findOrFail($id);
        $costcenter = $this->masterDataService->updateModel($costcenter, $request->validated());

        return response()->json([
            'success' => true,
            'data' => $costcenter,
        ]);
    }

    public function destroy(string $id)
    {
        CostCenter::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
