<?php

namespace PrestaShop\Module\Alma\Application\Validator;

use PrestaShop\Module\Alma\Application\Exception\CurrencyException;
use PrestaShop\Module\Alma\Infrastructure\Proxy\CurrencyProxy;

class CurrencyValidator
{
    private const SUPPORTED_CURRENCIES = ['EUR'];

    /**
     * @var \PrestaShop\Module\Alma\Infrastructure\Proxy\CurrencyProxy
     */
    private CurrencyProxy $currencyProxy;

    public function __construct(
        CurrencyProxy $currencyProxy
    ) {
        $this->currencyProxy = $currencyProxy;
    }

    public function checkCurrency($idCurrency)
    {
        $currency = $this->currencyProxy->getCurrencyIsoCode($idCurrency);
        if (!in_array($currency, self::SUPPORTED_CURRENCIES)) {
            throw new CurrencyException('Currency not supported');
        }
    }
}
