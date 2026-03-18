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
        $encodedFeePlanList = json_encode(['plan1', 'plan2']);
        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_LIST)
            ->willReturn($encodedFeePlanList);

        $this->assertEquals(['plan1', 'plan2'], $this->feePlansProvider->getFeePlanFromConfiguration());
    }

    public function testGetFeePlanFromConfigurationWithoutKeySavedReturnsArrayEmpty(): void
    {
        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_LIST)
            ->willReturn('');

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
}
