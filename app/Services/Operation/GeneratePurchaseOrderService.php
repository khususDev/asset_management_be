<?php

namespace App\Services\Operation;

use App\Models\Operation\Procurement\PurchaseOrder;
use App\Models\Operation\Procurement\PurchaseOrderItem;
use App\Models\Operation\Procurement\PurchaseOrderLog;
use App\Models\Operation\Procurement\PurchaseRequest;
use App\Helpers\DocNumberHelper;
use Illuminate\Support\Facades\DB;

class GeneratePurchaseOrderService
{
    public function generateFromPR(PurchaseRequest $pr)
    {
        $items = $pr->items
            ->where('need_to_issue_po', true)
            ->whereNotNull('vendor_id');

        if ($items->count() == 0) {
            return;
        }

        $groupedItems = $items->groupBy('vendor_id');

        foreach ($groupedItems as $vendorId => $vendorItems) {

            $firstItem = $vendorItems->first();

            $subtotal = $vendorItems->sum('total_amount');

            $ppn = 0;

            foreach ($vendorItems as $item) {

                if ($item->is_pkp) {
                    $ppn += $item->total_amount * 0.11;
                }
            }

            $grandTotal = $subtotal + $ppn;

            $po = PurchaseOrder::create([
                'po_number' => DocNumberHelper::generate(
                    'PO',
                    $pr->department->code
                ),
                'purchase_request_id' => $pr->id,
                'vendor_id' => $vendorId,
                'department_id' => $pr->department_id,
                'branch_id' => $firstItem->delivery_id,
                'payment_term_id' => $firstItem->payment_term_id,
                'order_date' => now(),
                'expected_delivery_date' => $firstItem->expected_arrival_date,
                'subtotal' => $subtotal,
                'ppn_amount' => $ppn,
                'grand_total' => $grandTotal,
                'status' => 'DRAFT',
                'created_by' => auth()->id(),
            ]);

            foreach ($vendorItems as $item) {

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'purchase_request_item_id' => $item->id,
                    'item_description' => $item->item_description,
                    'quantity' => $item->quantity,
                    'uom_id' => $item->uom_id,
                    'unit_price' => $item->unit_price,
                    'total_amount' => $item->total_amount,
                    'is_pkp' => $item->is_pkp,
                    'price_include_ppn' => $item->price_include_ppn,
                    'item_purpose' => $item->item_purpose,
                    'expected_arrival_date' => $item->expected_arrival_date,
                ]);
            }

            PurchaseOrderLog::create([
                'purchase_order_id' => $po->id,
                'status' => 'DRAFT',
                'remarks' => 'Purchase Order auto generated from Purchase Request.',
                'created_by' => auth()->id(),
            ]);
        }
    }
}