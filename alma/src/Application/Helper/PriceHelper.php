<?php

namespace PrestaShop\Module\Alma\Application\Helper;

class PriceHelper
{
    /**
     * Convert a price to cent, by multiplying it by 100 and rounding it.
     * @param float $price
     * @return int
     */
    public static function priceToCent(float $price): int
    {
        return (int) round($price * 100);
    }

    /**
     * Convert a price in cent to euro, by dividing it by 100.
     * Cents aren't rounded.
     * @param int $price
     * @return float
     */
    public static function priceToEuro(int $price): float
    {
        return $price / 100;
    }
}
