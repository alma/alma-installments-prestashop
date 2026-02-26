<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Helper;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Helper\FeePlanHelper;
use PrestaShop\Module\Alma\Tests\Mocks\FeePlansMock;

class FeePlanHelperTest extends TestCase
{
    public function testGetTitleWithPayNow()
    {
        $this->assertEquals('Pay Now', FeePlanHelper::getTitle(
            FeePlansMock::feePlan(1)
        ));
    }

    public function testGetTitleWithPnx()
    {
        $this->assertEquals('2-installment payments', FeePlanHelper::getTitle(
            FeePlansMock::feePlan(2)
        ));
    }

    public function testGetTitleWithDeferredDays()
    {
        $this->assertEquals('Deferred payments + 30 days', FeePlanHelper::getTitle(
            FeePlansMock::feePlan(1, 30)
        ));
    }

    public function testGetLabelWithPayNow()
    {
        $this->assertEquals('Enable pay now', FeePlanHelper::getLabel(
            FeePlansMock::feePlan(1)
        ));
    }

    public function testGetLabelWithPnx()
    {
        $this->assertEquals('Enable 2-installment payments', FeePlanHelper::getLabel(
            FeePlansMock::feePlan(2)
        ));
    }

    public function testGetLabelWithDeferredDays()
    {
        $this->assertEquals('Enable deferred payments +30 days', FeePlanHelper::getLabel(
            FeePlansMock::feePlan(1, 30, 0)
        ));
    }
}
