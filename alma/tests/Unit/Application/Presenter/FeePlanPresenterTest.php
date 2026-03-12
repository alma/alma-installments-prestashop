<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Presenter;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\FeePlansException;
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

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testCheckLimitAmountPlanMaxAmountExceededThrowException()
    {
        $feePlan = FeePlansMock::feePlan(2);
        $this->expectException(FeePlansException::class);
        $this->expectExceptionMessage('The maximum purchase amount cannot be higher than the maximum allowed by Alma.');
        FeePlanPresenter::checkLimitAmountPlan($feePlan, 10000, 200100);
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     * @throws \PrestaShop\Module\Alma\Application\Exception\FeePlansException
     */
    public function testCheckLimitAmountPlanMinAmountExceededThrowException()
    {
        $feePlan = FeePlansMock::feePlan(2);
        $this->expectException(FeePlansException::class);
        $this->expectExceptionMessage('The minimum purchase amount cannot be lower than the minimum allowed by Alma.');
        FeePlanPresenter::checkLimitAmountPlan($feePlan, 5000, 200000);
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testCheckLimitAmountPlanMinAmountExceededMaxAmountThrowException()
    {
        $feePlan = FeePlansMock::feePlan(2);
        $this->expectException(FeePlansException::class);
        $this->expectExceptionMessage('The minimum purchase amount cannot be higher than the maximum.');
        FeePlanPresenter::checkLimitAmountPlan($feePlan, 200100, 200000);
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testCheckLimitAmountPlanMaxAmountExceededMinAmountThrowException()
    {
        $feePlan = FeePlansMock::feePlan(2);
        $this->expectException(FeePlansException::class);
        $this->expectExceptionMessage('The maximum purchase amount cannot be lower than the minimum.');
        FeePlanPresenter::checkLimitAmountPlan($feePlan, 10000, 5000);
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     * @throws \PrestaShop\Module\Alma\Application\Exception\FeePlansException
     */
    public function testCheckLimitAmountPlanWithRightAmountReturnVoid()
    {
        $feePlan = FeePlansMock::feePlan(2);
        $this->assertNull(FeePlanPresenter::checkLimitAmountPlan($feePlan, 10000, 200000));
    }
}
