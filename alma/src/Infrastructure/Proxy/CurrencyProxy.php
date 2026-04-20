<?php

namespace PrestaShop\Module\Alma\Infrastructure\Proxy;

class CurrencyProxy
{
    public function getCurrencyIsoCode(int $idCurrency): string
    {
        $isoCode = (new \Currency($idCurrency))->iso_code;

        if (!$isoCode) {
            return '';
        }

        return $isoCode;
    }
}
