<?php

namespace App\Http\Controllers\Api\Operation\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Operation\Procurement\GoodsReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Operation\Procurement\GoodsReceiptItem;
use App\Models\Operation\Procurement\PurchaseOrder;
use App\Helpers\DocNumberHelper;
use App\Models\Operation\AssetManagement\Asset;

class GoodsReceiptController extends Controller
{
    public function index(Request $request)
    {
        $entries = $request->entries ?? 10;
        $search = $request->search ?? '';

        $query = GoodsReceipt::with([
            'purchaseOrder.vendor',
            'purchaseOrder.department',
            'user'
        ]);

        if ($search) {
            $query->where('gr_number', 'ilike', "%{$search}%")
                ->orWhereHas('purchaseOrder', function ($q) use ($search) {
                    $q->where(
                        'po_number',
                        'ilike',
                        "%{$search}%"
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

    public function create() {}

    public function store(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:opt_purchase_order,id',
            'items'             => 'required|array|min:1',
        ]);

        DB::beginTransaction();

        try {

            $purchaseOrder = PurchaseOrder::with([
                'items',
                'goodsReceipts.items'
            ])->findOrFail($request->purchase_order_id);

            $gr = GoodsReceipt::create([
                'gr_number'         => DocNumberHelper::generate(
                    'GR',
                    optional($purchaseOrder->department)->id ?? 'HO'
                ),

                'purchase_order_id' => $purchaseOrder->id,

                'received_date'     => $request->receipt_date ?? now(),

                'receipt_status'    => $request->receipt_status,

                'remarks'           => $request->remarks,

                'status'            => 'DRAFT',

                'created_by'        => auth()->id(),
            ]);

            foreach ($request->items as $item) {

                if ($item['received_qty'] <= 0) {
                    continue;
                }

                $poItem = $purchaseOrder
                    ->items
                    ->firstWhere(
                        'id',
                        $item['purchase_order_item_id']
                    );

                if (!$poItem) {
                    throw new \Exception(
                        'Purchase Order Item tidak ditemukan.'
                    );
                }

                $receivedBefore = 0;

                foreach ($purchaseOrder->goodsReceipts as $receipt) {

                    // Hanya hitung GR yang sudah POSTED
                    if ($receipt->status != 'POSTED') {
                        continue;
                    }

                    foreach ($receipt->items as $grItem) {

                        if (
                            $grItem->purchase_order_item_id ==
                            $poItem->id
                        ) {

                            $receivedBefore +=
                                $grItem->accepted_qty;
                        }
                    }
                }

                $outstanding =
                    $poItem->quantity -
                    $receivedBefore;

                if (
                    $item['accepted_qty'] +
                    $item['rejected_qty']
                    !=
                    $item['received_qty']
                ) {

                    throw new \Exception(
                        "Qty Good + Qty Reject harus sama dengan Qty Receive pada item {$poItem->item_description}"
                    );
                }


                if (
                    $item['received_qty'] >
                    $outstanding
                ) {

                    throw new \Exception(
                        "Qty Receive melebihi Outstanding pada item {$poItem->item_description}"
                    );
                }

                GoodsReceiptItem::create([
                    'goods_receipt_id'       => $gr->id,
                    'purchase_order_item_id' => $poItem->id,
                    'item_description'       => $poItem->item_description,
                    'ordered_qty'            => $poItem->quantity,
                    'received_before_qty'    => $receivedBefore,
                    'receive_qty'            => $item['received_qty'],
                    'accepted_qty'           => $item['accepted_qty'],
                    'rejected_qty'           => $item['rejected_qty'],
                    'replacement_qty'        => $item['replacement_qty'] ?? 0,
                    'remarks'                => $item['remarks'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Goods Receipt berhasil dibuat.',
                'data'    => $gr
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function show($id)
    {
        $gr = GoodsReceipt::with([
            'purchaseOrder.vendor',
            'items.purchaseOrderItem'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $gr
        ]);
    }

    public function edit($id)
    {
        return $this->show($id);
    }

    public function update(Request $request, $id)
    {
        $gr = GoodsReceipt::findOrFail($id);

        if ($gr->status != 'DRAFT') {
            return response()->json([
                'message' => 'Goods Receipt sudah diposting.'
            ], 422);
        }

        $gr->update([
            'receipt_date' => $request->receipt_date,
            'remarks' => $request->remarks,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Goods Receipt berhasil diupdate.'
        ]);
    }

    public function destroy($id)
    {
        $gr = GoodsReceipt::findOrFail($id);

        if ($gr->status != 'DRAFT') {
            return response()->json([
                'message' => 'Goods Receipt tidak dapat dihapus.'
            ], 422);
        }

        $gr->items()->delete();

        $gr->delete();

        return response()->json([
            'success' => true,
            'message' => 'Goods Receipt berhasil dihapus.'
        ]);
    }

    public function post($id)
    {
        DB::beginTransaction();

        try {
            $gr = GoodsReceipt::with([
                'items.purchaseOrderItem',
                'purchaseOrder.items',
                'purchaseOrder.goodsReceipts.items',
                'purchaseOrder.vendor',
                'purchaseOrder.department'
            ])->findOrFail($id);

            if ($gr->status == 'POSTED') {
                throw new \Exception('Goods Receipt sudah diposting.');
            }

            // 1. Update Status GR
            $gr->update([
                'status' => 'POSTED'
            ]);

            $purchaseOrder = $gr->purchaseOrder;
            if ($gr->receipt_status == 'COMPLETE') {

                $purchaseOrder->update([
                    'status' => 'COMPLETED'
                ]);
            } else {

                $purchaseOrder->update([
                    'status' => 'PARTIAL_RECEIVED'
                ]);
            }

            foreach ($gr->items as $grItem) {
                $acceptedQty = (int) $grItem->accepted_qty;

                if ($acceptedQty <= 0) {
                    continue;
                }

                $poItem = $grItem->purchaseOrderItem;

                // Pecah menjadi entri individual (misal terima 3 unit laptop -> buat 3 row aset)
                for ($i = 0; $i < $acceptedQty; $i++) {
                    Asset::create([
                        'asset_code'             => null, // Di-generate saat registrasi final
                        'asset_name'             => $grItem->item_description,
                        'goods_receipt_item_id'  => $grItem->id,
                        'purchase_order_item_id' => $poItem ? $poItem->id : null,
                        'vendor_id'              => optional($purchaseOrder->vendor)->id,
                        'department_id'          => optional($purchaseOrder->department)->id,
                        'purchase_date'          => $gr->received_date ?? now(),
                        'purchase_cost'          => $poItem ? $poItem->unit_price : 0,
                        'registration_status'    => 'WAITING_REGISTRATION',
                        'created_by'             => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Goods Receipt berhasil diposting dan data Aset telah dibuat.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function print($id)
    {
        $gr = GoodsReceipt::with([

            'purchaseOrder.vendor',
            'purchaseOrder.department',
            'purchaseOrder.branch',
            'purchaseOrder.paymentTerm',

            'user',

            'items.purchaseOrderItem.uom'

        ])->findOrFail($id);

        return view(
            'prints.goods-receipt',
            compact('gr')
        );
    }

    public function getPurchaseOrders()
    {
        $purchaseOrders = PurchaseOrder::with([
            'vendor',
            'department',
            'branch',
            'paymentTerm',
            'items.uom',
            'goodsReceipts.items'
        ])
            ->whereIn('status', [
                'SENT',
                'PARTIAL_RECEIVED'
            ])
            ->orderBy('order_date')
            ->get();

        foreach ($purchaseOrders as $po) {

            foreach ($po->items as $item) {

                $received = 0;

                foreach ($po->goodsReceipts as $receipt) {

                    foreach ($receipt->items as $grItem) {

                        if ($grItem->purchase_order_item_id == $item->id) {
                            $received += $grItem->accepted_qty;
                        }
                    }
                }

                $item->received_qty = $received;

                $item->outstanding_qty = max(
                    0,
                    $item->quantity - $received
                );
            }
        }

        $purchaseOrders = $purchaseOrders->filter(function ($po) {

            return $po->items->contains(function ($item) {
                return $item->outstanding_qty > 0;
            });
        })->values();

        return response()->json([
            'success' => true,
            'data' => $purchaseOrders
        ]);
    }
}
