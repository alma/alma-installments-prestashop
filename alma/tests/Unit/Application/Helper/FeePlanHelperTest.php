<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Helper;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Helper\FeePlanHelper;

class FeePlanHelperTest extends TestCase
{
    public function testGetTitleWithFeePlanUnknown()
    {
        $this->assertEquals('Unknown', FeePlanHelper::getTitle(1, 1, 1));
    }

    public function testGetTitleWithPayNow()
    {
        $this->assertEquals('Pay Now', FeePlanHelper::getTitle(1, 0, 0));
    }

    public function testGetTitleWithPnx()
    {
        $this->assertEquals('2-installment payments', FeePlanHelper::getTitle(2, 0, 0));
    }

    public function testGetTitleWithDeferredDays()
    {
        $this->assertEquals('Deferred payments + 30 days', FeePlanHelper::getTitle(1, 30, 0));
    }

    public function testGetTitleWithDeferredMonths()
    {
        $this->assertEquals('Deferred payments + 2 months', FeePlanHelper::getTitle(1, 0, 2));
    }

    public function testGetLabelWithFeePlanUnknown()
    {
        $this->assertEquals('Unknown', FeePlanHelper::getLabel(1, 1, 1));
    }

    public function testGetLabelWithPayNow()
    {
        $this->assertEquals('Enable pay now', FeePlanHelper::getLabel(1, 0, 0));
    }

    public function testGetLabelWithPnx()
    {
        $this->assertEquals('Enable 2-installment payments', FeePlanHelper::getLabel(2, 0, 0));
    }

    public function testGetLabelWithDeferredDays()
    {
        $this->assertEquals('Enable deferred payments +30 days', FeePlanHelper::getLabel(1, 30, 0));
    }

    public function testGetLabelWithDeferredMonths()
    {
        $this->assertEquals('Enable deferred payments +2 months', FeePlanHelper::getLabel(1, 0, 2));
    }
}
