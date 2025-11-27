<?php

namespace App\Services;

class PurchaseService
{
    /**
     * Calculate subtotal, total discount, total tax, and final total
     *
     * @param array $items
     * @param float $globalDiscountPercent
     * @param float $globalTaxPercent
     * @return array
     */
    public static function calculate(array $items, float $globalDiscountPercent = 0, float $globalTaxPercent = 0)
    {
        $subtotal = 0;
        $totalItemDiscount = 0;
        $totalItemTax = 0;

        foreach ($items as $item) {
            $itemSubtotal = $item['quantity'] * $item['cost_price'];
            $itemDiscount = $itemSubtotal * (($item['item_discount'] ?? 0) / 100);
            $itemTax = ($itemSubtotal - $itemDiscount) * (($item['item_tax'] ?? 0) / 100);

            $subtotal += $itemSubtotal;
            $totalItemDiscount += $itemDiscount;
            $totalItemTax += $itemTax;
        }

        // Apply global discount & tax percentages on subtotal
        $globalDiscount = $subtotal * ($globalDiscountPercent / 100);
        $globalTax = $subtotal * ($globalTaxPercent / 100);

        $totalDiscount = $totalItemDiscount + $globalDiscount;
        $totalTax = $totalItemTax + $globalTax;

        $totalAmount = $subtotal - $totalDiscount + $totalTax;

        return [
            'subtotal' => $subtotal,
            'total_discount' => $totalDiscount,
            'total_tax' => $totalTax,
            'total_amount' => $totalAmount,
        ];
    }
}
