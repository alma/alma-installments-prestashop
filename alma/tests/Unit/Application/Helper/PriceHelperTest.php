<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Helper;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Helper\PriceHelper;

class PriceHelperTest extends TestCase
{
    public function testPriceToCent()
    {
        $this->assertSame(1100, PriceHelper::priceToCent(11));
        $this->assertSame(1020, PriceHelper::priceToCent(10.2));
        $this->assertSame(1013, PriceHelper::priceToCent(10.13));
        $this->assertSame(1014, PriceHelper::priceToCent(10.135));
    }

    public function testPriceToEuro()
    {
        $this->assertSame(13, PriceHelper::priceToEuro(1300));
        $this->assertSame(10, PriceHelper::priceToEuro(1030));
        $this->assertSame(10, PriceHelper::priceToEuro(1016));
        $this->assertSame(101, PriceHelper::priceToEuro(10165));
    }
}
