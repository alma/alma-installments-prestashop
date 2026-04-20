<?php

namespace PrestaShop\Module\Alma\Tests\Integration\Infrastructure;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Infrastructure\Proxy\CurrencyProxy;

class CurrencyProxyTest extends TestCase
{
    public function setUp(): void
    {
        $this->currencyProxy = new CurrencyProxy();
    }

    public function testGetCurrencyIsoCodeEUR()
    {
        $this->assertEquals('EUR', $this->currencyProxy->getCurrencyIsoCode(1));
    }

    public function testGetCurrencyIsoCodeUSD()
    {
        $this->assertEquals('USD', $this->currencyProxy->getCurrencyIsoCode(2));
    }

    public function testGetCurrencyIsoCodeDoesNotExist()
    {
        $this->assertEquals('', $this->currencyProxy->getCurrencyIsoCode(3));
    }
}
