<?php

namespace App\Http\Controllers\Api\Administration\License;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLicenseTypeRequest;
use App\Http\Requests\UpdateLicenseTypeRequest;
use App\Models\Administration\License\LicenseType;
use App\Services\Administration\MasterDataService;
use Illuminate\Http\Request;

class LicenseTypeController extends Controller
{
    public function __construct(private readonly MasterDataService $masterDataService)
    {
    }

    public function index(Request $request)
    {
        $entries = $request->entries ?? 10;

        return response()->json([
            'success' => true,
            'data' => $this->masterDataService->listItems(LicenseType::query(), ['name', 'code'], $entries),
        ]);
    }

    public function store(StoreLicenseTypeRequest $request)
    {
        $type = $this->masterDataService->createModel(new LicenseType(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $type,
        ]);
    }

    public function show(string $id)
    {
        return response()->json([
            'success' => true,
            'data' => LicenseType::findOrFail($id),
        ]);
    }

    public function update(UpdateLicenseTypeRequest $request, string $id)
    {
        $type = LicenseType::findOrFail($id);
        $type = $this->masterDataService->updateModel($type, $request->validated());

        return response()->json([
            'success' => true,
            'data' => $type,
        ]);
    }

    public function destroy(string $id)
    {
        LicenseType::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
