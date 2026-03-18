<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Presenter;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Presenter\FeePlanPresenter;
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
            ->with('Pay Now', [], 'Modules.Alma.Settings')
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
            ->willReturnMap([
                ['Pay Now', [], 'Modules.Alma.Settings', null, 'Pay Now'],
                ['%installmentCount%-installment payments', ['%installmentCount%' => 2], 'Modules.Alma.Settings', null, '2-installment payments']
            ]);

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
            ->willReturnMap([
                ['Pay Now', [], 'Modules.Alma.Settings', null, 'Pay Now'],
                ['Deferred payments + %deferredDay% days', ['%deferredDay%' => 30], 'Modules.Alma.Settings', null, 'Deferred payments + 30 days']
            ]);

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
            ->with('Enable pay now', [], 'Modules.Alma.Settings')
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
            ->willReturnMap([
                ['Enable pay now', [], 'Modules.Alma.Settings', null, 'Enable pay now'],
                ['Enable %installmentCount%-installment payments', ['%installmentCount%' => 3], 'Modules.Alma.Settings', null, 'Enable 3-installment payments']
            ]);

        $this->assertEquals('Enable 3-installment payments', $this->feePlanPresenter->getLabel(
            FeePlansMock::feePlan(3)
        ));
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testGetLabelWithDeferredDays()
    {
        $this->translator->expects($this->exactly(2))
            ->method('trans')
            ->willReturnMap([
                ['Enable pay now', [], 'Modules.Alma.Settings', null, 'Enable pay now'],
                ['Enable deferred payments + %deferredDay% days', ['%deferredDay%' => 30], 'Modules.Alma.Settings', null, 'Enable deferred payments +30 days']
            ]);

        $this->assertEquals('Enable deferred payments +30 days', $this->feePlanPresenter->getLabel(
            FeePlansMock::feePlan(1, 30, 0)
        ));
    }
}
