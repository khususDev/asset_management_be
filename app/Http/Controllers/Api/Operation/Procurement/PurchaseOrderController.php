<?php

namespace App\Http\Controllers\Api\Operation\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Operation\Procurement\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $entries = $request->entries ?? 10;
        $search = $request->search ?? '';

        $query = PurchaseOrder::with([
            'purchaseRequest',
            'vendor',
            'department',
            'user'
        ]);

        if ($search) {
            $query->where(function ($q) use ($search) {

                $q->where(
                    'po_number',
                    'ilike',
                    "%{$search}%"
                )

                    ->orWhereHas(
                        'purchaseRequest',
                        function ($pr) use ($search) {
                            $pr->where(
                                'request_number',
                                'ilike',
                                "%{$search}%"
                            );
                        }
                    )

                    ->orWhereHas(
                        'vendor',
                        function ($vendor) use ($search) {
                            $vendor->where(
                                'name',
                                'ilike',
                                "%{$search}%"
                            );
                        }
                    );
            });
        }

        return response()->json([
            'success' => true,
            'data' => $query
                ->latest()
                ->paginate($entries)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */

    public function show($id)
    {
        $po = PurchaseOrder::with([
            'purchaseRequest',
            'vendor',
            'department',
            'items.deliveryBranch',
            'items.uom'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $po
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
