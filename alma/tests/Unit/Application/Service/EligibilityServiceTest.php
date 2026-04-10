<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use Alma\Client\Application\DTO\EligibilityDto;
use Alma\Client\Application\DTO\EligibilityQueryDto;
use Alma\Client\Domain\Entity\Eligibility;
use Alma\Client\Domain\Entity\EligibilityList;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\EligibilityException;
use PrestaShop\Module\Alma\Application\Provider\EligibilityProvider;
use PrestaShop\Module\Alma\Application\Service\EligibilityService;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Tests\Mocks\FeePlansMock;

class EligibilityServiceTest extends TestCase
{
    /**
     * @var ConfigurationRepository
     */
    private $configurationRepository;
    /**
     * @var EligibilityService
     */
    private EligibilityService $eligibilityService;

    public function setUp(): void
    {
        $this->cart = $this->createMock(\Cart::class);
        $this->eligibilityProvider = $this->createMock(EligibilityProvider::class);
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->eligibilityService = new EligibilityService(
            $this->eligibilityProvider,
            $this->configurationRepository
        );
    }

    public function testGetLocalEligibilityForCheckoutWithExceptionToGetOrderTotalThrowEligibilityException()
    {
        $this->cart->expects($this->once())
            ->method('getOrderTotal')
            ->willThrowException(new \Exception('Error getting order total'));

        $this->expectException(\PrestaShop\Module\Alma\Application\Exception\EligibilityException::class);
        $this->expectExceptionMessage('Error calculating purchase amount: Error getting order total');

        $this->eligibilityService->getLocalEligibilityForCheckout($this->cart);
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\EligibilityException
     */
    public function testGetLocalEligibilityWithPlanEnabledReturnQueriesForAlmaEligibility()
    {
        $expected = [
            'purchase_amount' => 42000,
            'queries' => [
                [
                    'installments_count' => 2,
                    'deferred_days' => 0,
                    'deferred_months' => 0,
                ],
                [
                    'installments_count' => 3,
                    'deferred_days' => 0,
                    'deferred_months' => 0,
                ],
                [
                    'installments_count' => 4,
                    'deferred_days' => 0,
                    'deferred_months' => 0,
                ]
            ]
        ];
        $this->cart->expects($this->once())
            ->method('getOrderTotal')
            ->willReturn(420.00);
        $feePlanFromDb = array_merge(
            FeePlansMock::almaFeePlanFromDb(2),
            FeePlansMock::almaFeePlanFromDb(3),
            FeePlansMock::almaFeePlanFromDb(4)
        );
        $this->configurationRepository->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanFromDb);

        $this->assertEquals($expected, $this->eligibilityService->getLocalEligibilityForCheckout($this->cart));
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\EligibilityException
     */
    public function testGetLocalEligibilityWithPlan2xNotEligibleMinAmountReturnQueriesForAlmaEligibilityP3And4X()
    {
        $expected = [
            'purchase_amount' => 42000,
            'queries' => [
                [
                    'installments_count' => 3,
                    'deferred_days' => 0,
                    'deferred_months' => 0,
                ],
                [
                    'installments_count' => 4,
                    'deferred_days' => 0,
                    'deferred_months' => 0,
                ]
            ]
        ];
        $this->cart->expects($this->once())
            ->method('getOrderTotal')
            ->willReturn(420.00);
        $feePlanFromDb = array_merge(
            FeePlansMock::almaFeePlanFromDb(2, 0, 0, '1', '50000', '200000'),
            FeePlansMock::almaFeePlanFromDb(3),
            FeePlansMock::almaFeePlanFromDb(4)
        );
        $this->configurationRepository->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanFromDb);

        $this->assertEquals($expected, $this->eligibilityService->getLocalEligibilityForCheckout($this->cart));
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\EligibilityException
     */
    public function testGetLocalEligibilityWithPlan3xNotEligibleMaxAmountReturnQueriesForAlmaEligibilityP2And4X()
    {
        $expected = [
            'purchase_amount' => 42000,
            'queries' => [
                [
                    'installments_count' => 2,
                    'deferred_days' => 0,
                    'deferred_months' => 0,
                ],
                [
                    'installments_count' => 4,
                    'deferred_days' => 0,
                    'deferred_months' => 0,
                ]
            ]
        ];
        $this->cart->expects($this->once())
            ->method('getOrderTotal')
            ->willReturn(420.00);
        $feePlanFromDb = array_merge(
            FeePlansMock::almaFeePlanFromDb(2),
            FeePlansMock::almaFeePlanFromDb(3, 0, 0, '1', '10000', '40000'),
            FeePlansMock::almaFeePlanFromDb(4)
        );
        $this->configurationRepository->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanFromDb);

        $this->assertEquals($expected, $this->eligibilityService->getLocalEligibilityForCheckout($this->cart));
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\EligibilityException
     */
    public function testGetLocalEligibilityWithPlan4xDisabledReturnQueriesForAlmaEligibilityP2And3X()
    {
        $expected = [
            'purchase_amount' => 42000,
            'queries' => [
                [
                    'installments_count' => 2,
                    'deferred_days' => 0,
                    'deferred_months' => 0,
                ],
                [
                    'installments_count' => 3,
                    'deferred_days' => 0,
                    'deferred_months' => 0,
                ]
            ]
        ];
        $this->cart->expects($this->once())
            ->method('getOrderTotal')
            ->willReturn(420.00);
        $feePlanFromDb = array_merge(
            FeePlansMock::almaFeePlanFromDb(2),
            FeePlansMock::almaFeePlanFromDb(3),
            FeePlansMock::almaFeePlanFromDb(4, 0, 0, '0')
        );
        $this->configurationRepository->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanFromDb);

        $this->assertEquals($expected, $this->eligibilityService->getLocalEligibilityForCheckout($this->cart));
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\EligibilityException
     */
    public function testGetLocalEligibilityWithoutPlansEligibleReturnQueriesForAlmaEligibilityEmpty()
    {
        $this->cart->expects($this->once())
            ->method('getOrderTotal')
            ->willReturn(420.00);
        $feePlanFromDb = array_merge(
            FeePlansMock::almaFeePlanFromDb(2, 0, 0, '1', '50000', '200000'),
            FeePlansMock::almaFeePlanFromDb(3, 0, 0, '1', '10000', '40000'),
            FeePlansMock::almaFeePlanFromDb(4, 0, 0, '0')
        );
        $this->configurationRepository->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanFromDb);

        $this->expectException(EligibilityException::class);
        $this->expectExceptionMessage('No eligible fee plans found from local eligibility');

        $this->eligibilityService->getLocalEligibilityForCheckout($this->cart);
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testGetEligibilityWithPlanEnabledReturnEligibilityList()
    {
        // We don't mock the eligibility, so we don't need to synchronize the eligibility list with the local eligibility queries,
        // To improve the test we will need to create Factory for the eligibility and the eligibility list to be able to create them with the same data as the local eligibility queries.
        // But I want to avoid sub-engineering the code for now.
        $eligibility = new Eligibility([
            'eligible' => true,
            'installments_count' => 2,
            'deferred_days' => 0,
            'deferred_months' => 0,
            'customer_fee' => 0,
            'customer_total_cost_amount' => 0,
            'customer_total_cost_bps' => 0,
            'payment_plan' => [],
            'annual_interest_rate' => 0,
        ]);
        $expected = new EligibilityList();
        $expected->add($eligibility);
        $localEligibility = [
            'purchase_amount' => 42000,
            'queries' => [
                [
                    'installments_count' => 2,
                    'deferred_days' => 0,
                    'deferred_months' => 0,
                ],
                [
                    'installments_count' => 3,
                    'deferred_days' => 0,
                    'deferred_months' => 0,
                ],
                [
                    'installments_count' => 4,
                    'deferred_days' => 0,
                    'deferred_months' => 0,
                ]
            ]
        ];
        $this->cart->expects($this->once())
            ->method('getOrderTotal')
            ->willReturn(420.00);
        $feePlanFromDb = array_merge(
            FeePlansMock::almaFeePlanFromDb(2),
            FeePlansMock::almaFeePlanFromDb(3),
            FeePlansMock::almaFeePlanFromDb(4)
        );
        $this->configurationRepository->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanFromDb);

        $eligibilityDto = new EligibilityDto($localEligibility['purchase_amount']);

        foreach ($localEligibility['queries'] as $eligibilityQuery) {
            $eligibilityQueryDto = new EligibilityQueryDto($eligibilityQuery['installments_count']);
            $eligibilityQueryDto->setDeferredDays($eligibilityQuery['deferred_days']);
            $eligibilityQueryDto->setDeferredMonths($eligibilityQuery['deferred_months']);
            $eligibilityDto->addQuery($eligibilityQueryDto);
        }

        $this->eligibilityProvider->expects($this->once())
            ->method('getEligibilityList')
            ->with($eligibilityDto)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->eligibilityService->getEligibilityForCheckout($this->cart));
    }

    public function testGetEligibilityThrowExceptionReturnEligibilityListEmpty()
    {
        $expected = new EligibilityList();
        $localEligibility = [
            'purchase_amount' => 42000,
            'queries' => [
                [
                    'installments_count' => 2,
                    'deferred_days' => 0,
                    'deferred_months' => 0,
                ],
                [
                    'installments_count' => 3,
                    'deferred_days' => 0,
                    'deferred_months' => 0,
                ],
                [
                    'installments_count' => 4,
                    'deferred_days' => 0,
                    'deferred_months' => 0,
                ]
            ]
        ];
        $this->cart->expects($this->once())
            ->method('getOrderTotal')
            ->willReturn(420.00);
        $feePlanFromDb = array_merge(
            FeePlansMock::almaFeePlanFromDb(2),
            FeePlansMock::almaFeePlanFromDb(3),
            FeePlansMock::almaFeePlanFromDb(4)
        );
        $this->configurationRepository->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanFromDb);

        $eligibilityDto = new EligibilityDto($localEligibility['purchase_amount']);

        foreach ($localEligibility['queries'] as $eligibilityQuery) {
            $eligibilityQueryDto = new EligibilityQueryDto($eligibilityQuery['installments_count']);
            $eligibilityQueryDto->setDeferredDays($eligibilityQuery['deferred_days']);
            $eligibilityQueryDto->setDeferredMonths($eligibilityQuery['deferred_months']);
            $eligibilityDto->addQuery($eligibilityQueryDto);
        }

        $this->eligibilityProvider->expects($this->once())
            ->method('getEligibilityList')
            ->with($eligibilityDto)
            ->willThrowException(new EligibilityException());

        $this->assertEquals($expected, $this->eligibilityService->getEligibilityForCheckout($this->cart));
    }
}
