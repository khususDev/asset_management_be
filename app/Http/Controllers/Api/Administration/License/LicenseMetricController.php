<?php

namespace App\Http\Controllers\Api\Administration\License;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLicenseMetricRequest;
use App\Http\Requests\UpdateLicenseMetricRequest;
use App\Models\Administration\License\LicenseMetric;
use App\Services\Administration\MasterDataService;
use Illuminate\Http\Request;

class LicenseMetricController extends Controller
{
    public function __construct(private readonly MasterDataService $masterDataService)
    {
    }

    public function index(Request $request)
    {
        $entries = $request->entries ?? 10;

        return response()->json([
            'success' => true,
            'data' => $this->masterDataService->listItems(LicenseMetric::query(), ['name', 'code'], $entries),
        ]);
    }

    public function store(StoreLicenseMetricRequest $request)
    {
        $metric = $this->masterDataService->createModel(new LicenseMetric(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $metric,
        ]);
    }

    public function show(string $id)
    {
        return response()->json([
            'success' => true,
            'data' => LicenseMetric::findOrFail($id),
        ]);
    }

    public function update(UpdateLicenseMetricRequest $request, string $id)
    {
        $metric = LicenseMetric::findOrFail($id);
        $metric = $this->masterDataService->updateModel($metric, $request->validated());

        return response()->json([
            'success' => true,
            'data' => $metric,
        ]);
    }

    public function destroy(string $id)
    {
        LicenseMetric::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
