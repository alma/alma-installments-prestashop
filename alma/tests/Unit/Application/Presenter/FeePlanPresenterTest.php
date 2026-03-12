<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Presenter;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Presenter\FeePlanPresenter;
use PrestaShop\Module\Alma\Tests\Mocks\FeePlansMock;

class FeePlanPresenterTest extends TestCase
{
    public function testGetTitleWithPayNow()
    {
        $this->assertEquals('Pay Now', FeePlanPresenter::getTitle(
            FeePlansMock::feePlan(1)
        ));
    }

    public function testGetTitleWithPnx()
    {
        $this->assertEquals('2-installment payments', FeePlanPresenter::getTitle(
            FeePlansMock::feePlan(2)
        ));
    }

    public function testGetTitleWithDeferredDays()
    {
        $this->assertEquals('Deferred payments + 30 days', FeePlanPresenter::getTitle(
            FeePlansMock::feePlan(1, 30)
        ));
    }

    public function testGetLabelWithPayNow()
    {
        $this->assertEquals('Enable pay now', FeePlanPresenter::getLabel(
            FeePlansMock::feePlan(1)
        ));
    }

    public function testGetLabelWithPnx()
    {
        $this->assertEquals('Enable 2-installment payments', FeePlanPresenter::getLabel(
            FeePlansMock::feePlan(2)
        ));
    }

    public function testGetLabelWithDeferredDays()
    {
        $this->assertEquals('Enable deferred payments +30 days', FeePlanPresenter::getLabel(
            FeePlansMock::feePlan(1, 30, 0)
        ));
    }
}
