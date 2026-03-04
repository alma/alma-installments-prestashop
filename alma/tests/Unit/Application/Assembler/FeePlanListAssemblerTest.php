<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Assembler;

use Alma\Client\Domain\Entity\FeePlanList;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Assembler\FeePlanListAssembler;
use PrestaShop\Module\Alma\Application\Provider\FeePlansProvider;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Tests\Mocks\FeePlansMock;

class FeePlanListAssemblerTest extends TestCase
{
    public function setUp(): void
    {
        $this->feePlansProvider = $this->createMock(FeePlansProvider::class);
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->feePlanListAssembler = new FeePlanListAssembler(
            $this->feePlansProvider,
            $this->configurationRepository
        );
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testAssembleWithFeePlanSavedInDb()
    {
        $expectedFeePlanListAssembled = [
            [
                'allowed' => true,
                'available_online' => true,
                'customer_fee_variable' => 0,
                'deferred_days' => 0,
                'deferred_months' => 0,
                'installments_count' => 3,
                'kind' => 'general',
                'max_purchase_amount' => 200000,
                'merchant_fee_variable' => 0,
                'merchant_fee_fixed' => 0,
                'min_purchase_amount' => 10000,
                'enabled' => true,
                'sort_order' => 5,
            ],
        ];
        $feePlanList = new FeePlanList();
        $feePlanList->add(
            FeePlansMock::feePlan(3)
        );
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanList);
        $this->configurationRepository->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['ALMA_GENERAL_3_0_0_STATE', '1'],
                ['ALMA_GENERAL_3_0_0_SORT_ORDER', '5'],
            ]);
        $this->assertEquals($expectedFeePlanListAssembled, $this->feePlanListAssembler->assemble());
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testAssembleWithoutFeePlanSavedInDb()
    {
        $expectedFeePlanListAssembled = [
            [
                'allowed' => true,
                'available_online' => true,
                'customer_fee_variable' => 0,
                'deferred_days' => 0,
                'deferred_months' => 0,
                'installments_count' => 3,
                'kind' => 'general',
                'max_purchase_amount' => 200000,
                'merchant_fee_variable' => 0,
                'merchant_fee_fixed' => 0,
                'min_purchase_amount' => 10000,
                'enabled' => false,
                'sort_order' => 0,
            ],
        ];
        $feePlanList = new FeePlanList();
        $feePlanList->add(
            FeePlansMock::feePlan(3)
        );
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanList);
        $this->configurationRepository->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['ALMA_GENERAL_3_0_0_STATE', ''],
                ['ALMA_GENERAL_3_0_0_SORT_ORDER', ''],
            ]);
        $this->assertEquals($expectedFeePlanListAssembled, $this->feePlanListAssembler->assemble());
    }
}
