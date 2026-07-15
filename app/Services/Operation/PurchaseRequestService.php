<?php

namespace App\Services\Operation;

class PurchaseRequestService
{
    public function calculateTotalAmount(array $items): float
    {
        $total = 0;

        foreach ($items as $item) {
            $quantity = (int) ($item['quantity'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $total += $quantity * $unitPrice;
        }

        return round($total, 2);
    }
}
