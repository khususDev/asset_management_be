<?php

namespace App\Http\Controllers\Api\Administration\Asset;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetModelRequest;
use App\Http\Requests\UpdateAssetModelRequest;
use App\Models\Administration\Asset\AssetModel;
use App\Services\Administration\MasterDataService;
use Illuminate\Http\Request;

class AssetModelController extends Controller
{
    public function __construct(private readonly MasterDataService $masterDataService)
    {
    }

    public function index(Request $request)
    {
        $entries = $request->entries ?? 10;
        $search = $request->search ?? '';

        $query = AssetModel::with('brand');

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhereHas('brand', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest()->paginate($entries),
        ]);
    }

    public function store(StoreAssetModelRequest $request)
    {
        $model = $this->masterDataService->createModel(new AssetModel(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $model,
        ]);
    }

    public function show(string $id)
    {
        return response()->json([
            'success' => true,
            'data' => AssetModel::with('brand')->findOrFail($id),
        ]);
    }

    public function update(UpdateAssetModelRequest $request, string $id)
    {
        $model = AssetModel::findOrFail($id);
        $model = $this->masterDataService->updateModel($model, $request->validated());

        return response()->json([
            'success' => true,
            'data' => $model,
        ]);
    }

    public function destroy(string $id)
    {
        AssetModel::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
