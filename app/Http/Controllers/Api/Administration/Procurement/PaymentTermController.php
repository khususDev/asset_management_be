<?php

namespace App\Http\Controllers\Api\Administration\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentTermRequest;
use App\Http\Requests\UpdatePaymentTermRequest;
use App\Models\Administration\Procurement\PaymentTerm;
use App\Services\Administration\MasterDataService;
use Illuminate\Http\Request;

class PaymentTermController extends Controller
{
    public function __construct(private readonly MasterDataService $masterDataService)
    {
    }

    public function index(Request $request)
    {
        $entries = $request->entries ?? 10;

        return response()->json([
            'success' => true,
            'data' => $this->masterDataService->listItems(PaymentTerm::query(), ['name', 'code'], $entries),
        ]);
    }

    public function store(StorePaymentTermRequest $request)
    {
        $term = $this->masterDataService->createModel(new PaymentTerm(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $term,
        ]);
    }

    public function show(string $id)
    {
        return response()->json([
            'success' => true,
            'data' => PaymentTerm::findOrFail($id),
        ]);
    }

    public function update(UpdatePaymentTermRequest $request, string $id)
    {
        $term = PaymentTerm::findOrFail($id);
        $term = $this->masterDataService->updateModel($term, $request->validated());

        return response()->json([
            'success' => true,
            'data' => $term,
        ]);
    }

    public function destroy(string $id)
    {
        PaymentTerm::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
