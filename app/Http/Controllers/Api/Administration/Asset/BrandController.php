<?php

namespace App\Http\Controllers\Api\Administration\Asset;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Models\Administration\Asset\Brand;
use App\Services\Administration\MasterDataService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct(private readonly MasterDataService $masterDataService)
    {
    }

    public function index(Request $request)
    {
        $entries = $request->entries ?? 10;

        return response()->json([
            'success' => true,
            'data' => $this->masterDataService->listItems(Brand::query(), ['name', 'code'], $entries),
        ]);
    }

    public function store(StoreBrandRequest $request)
    {
        $brand = $this->masterDataService->createModel(new Brand(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $brand,
        ]);
    }

    public function show(string $id)
    {
        return response()->json([
            'success' => true,
            'data' => Brand::findOrFail($id),
        ]);
    }

    public function update(UpdateBrandRequest $request, string $id)
    {
        $brand = Brand::findOrFail($id);
        $brand = $this->masterDataService->updateModel($brand, $request->validated());

        return response()->json([
            'success' => true,
            'data' => $brand,
        ]);
    }

    public function destroy(string $id)
    {
        Brand::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
