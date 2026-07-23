<?php

namespace App\Http\Controllers\Api\Operation\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Administration\AppSetting;
use App\Models\Operation\Procurement\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    // 1. Validasi
    $validated = $request->validate([
        'vendor_id' => 'required',
        'department_id' => 'required',
        'branch_id' => 'required',
        'payment_term_id' => 'required',
        'order_date' => 'required|date',
        // Tambahkan validasi is_pkp global
        'is_pkp' => 'nullable|boolean', 
        'items' => 'required|array|min:1',
        'items.*.item_description' => 'required|string',
        'items.*.quantity' => 'required|numeric|min:1',
        // Tambahkan uom_id karena dipakai saat insert item
        'items.*.uom_id' => 'required', 
        'items.*.unit_price' => 'required|numeric|min:0',
    ]);

    DB::transaction(function () use ($request) {
        // 2. Hitung Total
        $subtotal = 0;
        $ppn = 0;

        // Ambil nilai is_pkp dari luar array items (global)
        // Kita jadikan boolean agar mudah dikalkulasi
        $isGlobalPkp = $request->boolean('is_pkp');

        foreach ($request->items as $item) {
            $lineTotal = $item['quantity'] * $item['unit_price'];
            $subtotal += $lineTotal;

            // Cek jika ada is_pkp khusus di item, jika tidak, gunakan global is_pkp
            $itemIsPkp = isset($item['is_pkp']) ? (bool) $item['is_pkp'] : $isGlobalPkp;

            // Asumsi PPN 11% jika PKP
            if ($itemIsPkp) {
                $ppn += ($lineTotal * 0.11);
            }
        }

        // 3. Simpan PO (Manual PO = purchase_request_id adalah null)
        $po = PurchaseOrder::create([
            'po_number' => 'PO-' . date('YmdHis'), // Contoh generate sederhana
            'purchase_request_id' => null,
            'vendor_id' => $request->vendor_id,
            'department_id' => $request->department_id,
            'branch_id' => $request->branch_id,
            'payment_term_id' => $request->payment_term_id,
            'order_date' => $request->order_date,
            // Opsional: Tangkap juga expected_arrival_date dari Vue jika dibutuhkan tabel PO
            'expected_delivery_date' => $request->expected_arrival_date ?? null, 
            'subtotal' => $subtotal,
            'ppn_amount' => $ppn,
            'grand_total' => $subtotal + $ppn,
            'status' => 'DRAFT',
            'created_by' => auth()->id(),
        ]);

        // 4. Simpan Item
        foreach ($request->items as $item) {
            // Gunakan logika pengecekan yang sama untuk insert ke database
            $itemIsPkp = isset($item['is_pkp']) ? (bool) $item['is_pkp'] : $isGlobalPkp;

            $po->items()->create([
                'purchase_request_item_id' => null, // Manual
                'item_description' => $item['item_description'],
                'quantity' => $item['quantity'],
                'uom_id' => $item['uom_id'],
                'unit_price' => $item['unit_price'],
                'total_amount' => $item['quantity'] * $item['unit_price'],
                'is_pkp' => $itemIsPkp, // Simpan boolean ke table item
            ]);
        }
    });

    return response()->json(['message' => 'Manual PO created successfully']);
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

    public function edit($id)
    {
        $po = PurchaseOrder::with([
            'purchaseRequest',
            'vendor',
            'department',
            'items.uom',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $po
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $po = PurchaseOrder::with('items')->findOrFail($id);

        if ($po->status != 'DRAFT') {
            return response()->json([
                'success' => false,
                'message' => 'PO sudah diproses.'
            ], 422);
        }

        $request->validate([
            'vendor_id' => 'required',
            'order_date' => 'required',
            'items' => 'required|array'
        ]);

        DB::beginTransaction();

        try {

            $po->update([
                'vendor_id' => $request->vendor_id,
                'branch_id' => $request->branch_id,
                'payment_term_id' => $request->payment_term_id,
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'remarks' => $request->remarks,
            ]);

            $po->items()->delete();

            $subtotal = 0;
            $ppn = 0;

            foreach ($request->items as $item) {

                $amount =
                    $item['quantity']
                    * $item['unit_price'];

                $subtotal += $amount;

                if ($item['is_pkp']) {
                    $ppn += ($amount * 11 / 100);
                }

                $po->items()->create([
                    'purchase_request_item_id' =>
                        $item['purchase_request_item_id'] ?? null,

                    'item_description' =>
                        $item['item_description'],

                    'quantity' =>
                        $item['quantity'],

                    'uom_id' =>
                        $item['uom_id'],

                    'unit_price' =>
                        $item['unit_price'],

                    'total_amount' =>
                        $amount,

                    'is_pkp' =>
                        $item['is_pkp'],

                    'price_include_ppn' =>
                        $item['price_include_ppn'],

                    'item_purpose' =>
                        $item['item_purpose'],

                    'expected_arrival_date' =>
                        $item['expected_arrival_date'],
                ]);
            }

            $po->update([
                'subtotal' => $subtotal,
                'ppn_amount' => $ppn,
                'grand_total' => $subtotal + $ppn
            ]);

            DB::commit();

            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $po = PurchaseOrder::findOrFail($id);

        if ($po->status != 'DRAFT') {
            return response()->json([
                'success' => false,
                'message' => 'PO sudah diproses.'
            ], 422);
        }

        $po->delete();

        return response()->json([
            'success' => true
        ]);
    }

    public function printPdf($id)
    {
        $po = PurchaseOrder::with([
            'purchaseRequest',
            'vendor',
            'department',
            'branch',
            'paymentTerm',
            'items.uom'
        ])->findOrFail($id);

        $company = [
            'name' => AppSetting::getValue('company_name'),
            'logo' => AppSetting::getValue('logo_lg'),
            'address' => AppSetting::getValue('company_address'),
            'city' => AppSetting::getValue('company_city'),
            'phone' => AppSetting::getValue('company_phone'),
            'email' => AppSetting::getValue('company_email'),
            'website' => AppSetting::getValue('company_website'),
            'npwp' => AppSetting::getValue('company_npwp'),
        ];

        return view(
            'prints.purchase-order',
            compact(
                'po',
                'company'
            )
        );
    }

    public function send($id)
    {
        $po = PurchaseOrder::findOrFail($id);

        if ($po->status != 'DRAFT') {
            return response()->json([
                'success' => false,
                'message' => 'PO sudah dikirim.'
            ], 422);
        }

        $po->update([
            'status' => 'SENT'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'PO berhasil dikirim.'
        ]);
    }

    public function getFormMasters()
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'uoms' => \App\Models\Administration\Procurement\Uom::all(),
                    'vendors' => \App\Models\Administration\Procurement\Vendor::all(),
                    'payments' => \App\Models\Administration\Procurement\PaymentTerm::all(),
                    'branchs' => \App\Models\Administration\Organization\Branch::all(),
                    'departments' => \App\Models\Administration\Organization\Department::all(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil master data dropdown: ' . $e->getMessage()
            ], 500);
        }
    }
}