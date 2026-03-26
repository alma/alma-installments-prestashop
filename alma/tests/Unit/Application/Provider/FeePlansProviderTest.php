<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Provider;

use Alma\Client\Application\Endpoint\MerchantEndpoint;
use Alma\Client\Application\Exception\Endpoint\MerchantEndpointException;
use Alma\Client\Domain\Entity\FeePlanList;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Provider\FeePlansProvider;
use PrestaShop\Module\Alma\Infrastructure\Form\FeePlansAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Tests\Mocks\FeePlansMock;

class FeePlansProviderTest extends TestCase
{
    /**
     * @var \PrestaShop\Module\Alma\Application\Provider\FeePlansProvider
     */
    private FeePlansProvider $feePlansProvider;
    /**
     * @var ConfigurationRepository
     */
    private $configurationRepository;

    public function setUp(): void
    {
        $this->merchantEndpoint = $this->createMock(MerchantEndpoint::class);
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->feePlansProvider = new FeePlansProvider(
            $this->merchantEndpoint,
            $this->configurationRepository
        );
    }

    public function testGetFeePlanListWithoutForceRefreshReturnsCachedValue(): void
    {
        $feePlanList = new FeePlanList();

        $this->merchantEndpoint->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanList);

        $result1 = $this->feePlansProvider->getFeePlanList();

        $result2 = $this->feePlansProvider->getFeePlanList(false);

        $this->assertSame($result1, $result2);
    }

    public function testGetFeePlanListWithForceRefreshCallsEndpoint(): void
    {
        $feePlanList = new FeePlanList();

        $this->merchantEndpoint->expects($this->exactly(2))
            ->method('getFeePlanList')
            ->willReturn($feePlanList);

        $this->feePlansProvider->getFeePlanList();

        $this->feePlansProvider->getFeePlanList(true);
    }

    public function testGetFeePlanFromConfigurationReturnsDecodedValue(): void
    {
        $encodedFeePlanList = ['plan1', 'plan2'];
        $this->configurationRepository->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($encodedFeePlanList);

        $this->assertEquals(['plan1', 'plan2'], $this->feePlansProvider->getFeePlanFromConfiguration());
    }

    public function testGetFeePlanFromConfigurationWithoutKeySavedReturnsArrayEmpty(): void
    {
        $this->configurationRepository->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn([]);

        $this->assertEquals([], $this->feePlansProvider->getFeePlanFromConfiguration());
    }

    public function testGetFeesPlansAllowedExpectExceptionReturnFeePlanListEmpty(): void
    {
        $this->merchantEndpoint->expects($this->once())
            ->method('getFeePlanList')
            ->willThrowException(new MerchantEndpointException());

        $this->assertEquals(new FeePlanList(), $this->feePlansProvider->getFeePlanList());
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testGetFeesPlansAllowedReturnFeePlanListOrdered(): void
    {
        $feePlanPayNow = FeePlansMock::feePlan(1);
        $feePlan30D = FeePlansMock::feePlan(1, 30);
        $feePlanP2x = FeePlansMock::feePlan(2);
        $feePlanP6x = FeePlansMock::feePlan(6);
        $expectedFeePlanListOrdered = new FeePlanList();
        $expectedFeePlanListOrdered->add($feePlanPayNow);
        $expectedFeePlanListOrdered->add($feePlanP2x);
        $expectedFeePlanListOrdered->add($feePlanP6x);
        $expectedFeePlanListOrdered->add($feePlan30D);
        $feePlanList = new FeePlanList();
        $feePlanList->add($feePlan30D);
        $feePlanList->add($feePlanP6x);
        $feePlanList->add($feePlanP2x);
        $feePlanList->add($feePlanPayNow);

        $this->merchantEndpoint->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanList);

        $this->assertEquals($expectedFeePlanListOrdered, $this->feePlansProvider->getFeePlanList());
    }

    public function testGetOriginalFeePlanReturnsDecodedValue(): void
    {
        $feePlan3x = FeePlansMock::originalFeePlan(3);
        $feePlanList = new FeePlanList([$feePlan3x]);
        $encodedOriginalFeePlan = json_encode($feePlanList);
        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(FeePlansAdminForm::KEY_FIELD_ORIGINAL_FEE_PLAN)
            ->willReturn($encodedOriginalFeePlan);

        $this->assertEquals($feePlanList, $this->feePlansProvider->getOriginalFeePlan());
    }

     public function testGetOriginalFeePlanWithoutKeySavedReturnsArrayEmpty(): void
     {
        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(FeePlansAdminForm::KEY_FIELD_ORIGINAL_FEE_PLAN)
            ->willReturn('');

        $this->assertEquals(new FeePlanList(), $this->feePlansProvider->getOriginalFeePlan());
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testGetOriginalFeePlanReturnWithOnePlanException()
    {
        $feePlan3x = FeePlansMock::originalFeePlan(3);
        $wrongFeePlan = [
            'allowed' => true,
            'available_online' => true,
            'customer_fee_variable' => 0,
            'deferred_days' => 0,
            'deferred_months' => 0,
            'kind' => 'general_4_0_0',
            'max_purchase_amount' => 10000,
            'merchant_fee_variable' => 0,
            'merchant_fee_fixed' => 0,
            'min_purchase_amount' => 200000,
        ];
        $expectedFeePlanList = new FeePlanList([FeePlansMock::feePlan(3)]);
        $feePlanList = [$feePlan3x, $wrongFeePlan];

        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(FeePlansAdminForm::KEY_FIELD_ORIGINAL_FEE_PLAN)
            ->willReturn(json_encode($feePlanList));

        $this->assertEquals($expectedFeePlanList, $this->feePlansProvider->getOriginalFeePlan());
    }
}
