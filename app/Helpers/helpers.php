<?php

use App\Helpers\Currency as CurrencyHelper;

if (!function_exists('currency')) {
    /**
     * Convert currency using the Currency helper class.
     *
     * @param float $amount
     * @param string $from
     * @param string $to
     * @param int|null $places
     *
     * @return float|null
     */
    function currency(float $amount, string $from, string $to, ?string $date = "latest"): ?float
    {
        return CurrencyHelper::convert()
            ->amount($amount)
            ->from($from)
            ->to($to)
            ->date($date)
            ->get();
    }
}

if (!function_exists('deleteItems')) {
    function deleteItems($items)
    {
        foreach ($items as $item) {
            $item->delete();
        }
    }
}
