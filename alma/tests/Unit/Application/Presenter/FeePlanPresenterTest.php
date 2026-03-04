<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Presenter;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Presenter\FeePlanPresenter;
use PrestaShop\Module\Alma\Application\Exception\FeePlansException;
use PrestaShop\Module\Alma\Tests\Mocks\FeePlansMock;
use PrestaShopBundle\Translation\Translator;

class FeePlanPresenterTest extends TestCase
{
    /**
     * @var FeePlanPresenter
     */
    private FeePlanPresenter $feePlanPresenter;
    /**
     * @var Translator
     */
    private $translator;

    public function setUp(): void
    {
        $this->translator = $this->createMock(Translator::class);
        $this->feePlanPresenter = new FeePlanPresenter(
            $this->translator
        );
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testGetTitleWithPayNow()
    {
        $this->translator->expects($this->once())
            ->method('trans')
            ->willReturn('Pay Now');

        $this->assertEquals('Pay Now', $this->feePlanPresenter->getTitle(
            FeePlansMock::feePlan(1)
        ));
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testGetTitleWithPnx()
    {
        $this->translator->expects($this->exactly(2))
            ->method('trans')
            ->willReturn('2-installment payments');

        $this->assertEquals('2-installment payments', $this->feePlanPresenter->getTitle(
            FeePlansMock::feePlan(2)
        ));
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testGetTitleWithDeferredDays()
    {
        $this->translator->expects($this->exactly(2))
            ->method('trans')
            ->willReturn('Deferred payments + 30 days');

        $this->assertEquals('Deferred payments + 30 days', $this->feePlanPresenter->getTitle(
            FeePlansMock::feePlan(1, 30)
        ));
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testGetLabelWithPayNow()
    {
        $this->translator->expects($this->once())
            ->method('trans')
            ->willReturn('Enable pay now');

        $this->assertEquals('Enable pay now', $this->feePlanPresenter->getLabel(
            FeePlansMock::feePlan(1)
        ));
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testGetLabelWithPnx()
    {
        $this->translator->expects($this->exactly(2))
            ->method('trans')
            ->willReturn('Enable 2-installment payments');

        $this->assertEquals('Enable 2-installment payments', $this->feePlanPresenter->getLabel(
            FeePlansMock::feePlan(2)
        ));
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testGetLabelWithDeferredDays()
    {
        $this->translator->expects($this->exactly(2))
            ->method('trans')
            ->willReturn('Enable deferred payments +30 days');

        $this->assertEquals('Enable deferred payments +30 days', $this->feePlanPresenter->getLabel(
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
        FeePlanHelper::checkLimitAmountPlan($feePlan, 10000, 200100);
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
        FeePlanHelper::checkLimitAmountPlan($feePlan, 5000, 200000);
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testCheckLimitAmountPlanMinAmountExceededMaxAmountThrowException()
    {
        $feePlan = FeePlansMock::feePlan(2);
        $this->expectException(FeePlansException::class);
        $this->expectExceptionMessage('The minimum purchase amount cannot be higher than the maximum.');
        FeePlanHelper::checkLimitAmountPlan($feePlan, 200100, 200000);
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testCheckLimitAmountPlanMaxAmountExceededMinAmountThrowException()
    {
        $feePlan = FeePlansMock::feePlan(2);
        $this->expectException(FeePlansException::class);
        $this->expectExceptionMessage('The maximum purchase amount cannot be lower than the minimum.');
        FeePlanHelper::checkLimitAmountPlan($feePlan, 10000, 5000);
    }

    public function testCheckLimitAmountPlanWithRightAmountReturnVoid()
    {
        $feePlan = FeePlansMock::feePlan(2);
        $this->assertNull(FeePlanHelper::checkLimitAmountPlan($feePlan, 10000, 200000));
    }
}
