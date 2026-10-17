<?php

namespace App\Services;

class InventoryValuationService
{
    public function remainingValue(float $availableNetto1, float $purchasedNetto1, float $netto2PurchaseValue): float
    {
        if ($purchasedNetto1 <= 0) {
            return 0;
        }

        return max(0, $availableNetto1) * ($netto2PurchaseValue / $purchasedNetto1);
    }
}
