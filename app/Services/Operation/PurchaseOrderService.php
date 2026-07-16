<?php

namespace App\Services\Operation;

use App\Models\Operation\Procurement\PurchaseOrder;
use App\Models\Operation\Procurement\PurchaseOrderItem;
use App\Helpers\DocNumberHelper;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    public function generateFromPR($purchaseRequest)
    {
        DB::beginTransaction();

        try {

            $items = $purchaseRequest->items
                ->where('need_to_issue_po', true)
                ->whereNotNull('vendor_id');

            if ($items->count() == 0) {
                DB::commit();
                return;
            }

            $groupedItems = $items->groupBy('vendor_id');

            foreach ($groupedItems as $vendorId => $vendorItems) {

                $firstItem = $vendorItems->first();

                $po = PurchaseOrder::create([
                    'po_number' => 'TEMP',
                    'purchase_request_id' => $purchaseRequest->id,
                    'vendor_id' => $vendorId,
                    'department_id' => $purchaseRequest->department_id,
                    'created_by' => auth()->id(),
                    'po_date' => now(),
                    'payment_term_id' => $firstItem->payment_term_id,
                    'subtotal' => 0,
                    'ppn_amount' => 0,
                    'grand_total' => 0,
                    'status' => 'DRAFT',
                ]);

                $total = 0;
                $ppn = 0;

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
                        'expected_arrival_date' => $item->expected_arrival_date,
                        'delivery_id' => $item->delivery_id,
                        'item_purpose' => $item->item_purpose,
                    ]);

                    $total += $item->total_amount;

                    if ($item->is_pkp) {
                        $ppn += ($item->total_amount * 11 / 100);
                    }
                }

                $po->update([
                    'subtotal' => $total,
                    'ppn_amount' => $ppn,
                    'grand_total' => $total + $ppn,
                ]);

                /*
                 |---------------------------------------------------
                 | Generate nomor PO
                 |---------------------------------------------------
                 */

                $department = $purchaseRequest->department;

                $poNumber = DocNumberHelper::generate(
                    'PO',
                    $department->code
                );

                $po->update([
                    'po_number' => $poNumber
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }
}