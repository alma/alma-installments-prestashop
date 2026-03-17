<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Presenter;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Presenter\FeePlanPresenter;
use PrestaShop\Module\Alma\Tests\Mocks\FeePlansMock;

class FeePlanPresenterTest extends TestCase
{
    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testGetTitleWithPayNow()
    {
        $this->assertEquals('Pay Now', FeePlanPresenter::getTitle(
            FeePlansMock::feePlan(1)
        ));
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testGetTitleWithPnx()
    {
        $this->assertEquals('2-installment payments', FeePlanPresenter::getTitle(
            FeePlansMock::feePlan(2)
        ));
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testGetTitleWithDeferredDays()
    {
        $this->assertEquals('Deferred payments + 30 days', FeePlanPresenter::getTitle(
            FeePlansMock::feePlan(1, 30)
        ));
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testGetLabelWithPayNow()
    {
        $this->assertEquals('Enable pay now', FeePlanPresenter::getLabel(
            FeePlansMock::feePlan(1)
        ));
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testGetLabelWithPnx()
    {
        $this->assertEquals('Enable 2-installment payments', FeePlanPresenter::getLabel(
            FeePlansMock::feePlan(2)
        ));
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testGetLabelWithDeferredDays()
    {
        $this->assertEquals('Enable deferred payments +30 days', FeePlanPresenter::getLabel(
            FeePlansMock::feePlan(1, 30, 0)
        ));
    }
}
