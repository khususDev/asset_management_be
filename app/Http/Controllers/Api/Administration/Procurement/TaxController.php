<?php

namespace App\Http\Controllers\Api\Administration\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaxRequest;
use App\Http\Requests\UpdateTaxRequest;
use App\Models\Administration\Procurement\Tax;
use App\Services\Administration\MasterDataService;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function __construct(private readonly MasterDataService $masterDataService)
    {
    }

    public function index(Request $request)
    {
        $entries = $request->entries ?? 10;

        return response()->json([
            'success' => true,
            'data' => $this->masterDataService->listItems(Tax::query(), ['name', 'code'], $entries),
        ]);
    }

    public function store(StoreTaxRequest $request)
    {
        $tax = $this->masterDataService->createModel(new Tax(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $tax,
        ]);
    }

    public function show(string $id)
    {
        return response()->json([
            'success' => true,
            'data' => Tax::findOrFail($id),
        ]);
    }

    public function update(UpdateTaxRequest $request, string $id)
    {
        $tax = Tax::findOrFail($id);
        $tax = $this->masterDataService->updateModel($tax, $request->validated());

        return response()->json([
            'success' => true,
            'data' => $tax,
        ]);
    }

    public function destroy(string $id)
    {
        Tax::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
