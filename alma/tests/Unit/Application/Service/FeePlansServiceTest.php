<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use Alma\Client\Domain\Entity\FeePlanList;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Presenter\FeePlanPresenter;
use PrestaShop\Module\Alma\Application\Provider\FeePlansProvider;
use PrestaShop\Module\Alma\Application\Service\FeePlansService;
use PrestaShop\Module\Alma\Infrastructure\Form\FeePlansAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Tests\Mocks\FeePlansMock;
use PrestaShopBundle\Translation\TranslatorInterface;

class FeePlansServiceTest extends TestCase
{
    /**
     * @var FeePlansProvider
     */
    private $feePlansProvider;
    /**
     * @var FeePlansService
     */
    private FeePlansService $feePlansService;
    /**
     * @var FeePlanPresenter
     */
    private $feePlanPresenter;
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var ConfigurationRepository
     */
    private $configurationRepository;

    public function setUp(): void
    {
        $this->context = $this->createMock(\Context::class);
        $this->feePlansProvider = $this->createMock(FeePlansProvider::class);
        $this->feePlanPresenter = $this->createMock(FeePlanPresenter::class);
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->feePlansService = new FeePlansService(
            $this->context,
            $this->feePlansProvider,
            $this->feePlanPresenter,
            $this->configurationRepository,
            $this->translator
        );
    }

    public function testFeePlansTabsGetFeePlanListEmptyReturnEmptyArray()
    {
        $this->feePlansProvider->expects($this->once())
            ->method('getOriginalFeePlan')
            ->willReturn(new FeePlanList());

        $this->assertEquals([], $this->feePlansService->feePlansTabs());
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testFeePlansTabsWithP2And3And4XWithDefaultTabsActiveP3x()
    {
        $expected = array_merge(
            FeePlansMock::feePlansTabsExpected(2),
            FeePlansMock::feePlansTabsExpected(3, true),
            FeePlansMock::feePlansTabsExpected(4),
        );

        $feePlanFromConfig = [
            'general_2_0_0' => [
                'state' => '0',
                'min_amount' => '5000',
                'max_amount' => '300000',
                'sort_order' => '2',
            ],
            'general_3_0_0' => [
                'state' => '1',
                'min_amount' => '5000',
                'max_amount' => '300000',
                'sort_order' => '3',
            ],
            'general_4_0_0' => [
                'state' => '0',
                'min_amount' => '5000',
                'max_amount' => '300000',
                'sort_order' => '4',
            ],
        ];

        $feePlan2X = FeePlansMock::feePlan(2);
        $feePlan3X = FeePlansMock::feePlan(3, 0, 0, true, 10000, 200000, true, 5);
        $feePlan4X = FeePlansMock::feePlan(4);

        $feePlanList = new FeePlanList([$feePlan2X, $feePlan3X, $feePlan4X]);
        $this->feePlansProvider->expects($this->once())
            ->method('getOriginalFeePlan')
            ->willReturn($feePlanList);
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlanFromConfiguration')
            ->willReturn($feePlanFromConfig);
        $this->feePlanPresenter->expects($this->exactly(3))
            ->method('getTitle')
            ->withConsecutive(
                [FeePlansMock::feePlan(2)],
                [FeePlansMock::feePlan(3)],
                [FeePlansMock::feePlan(4)]
            )
            ->willReturnOnConsecutiveCalls(
                '2-installment payments',
                '3-installment payments',
                '4-installment payments'
            );
        $this->assertEquals($expected, $this->feePlansService->feePlansTabs());
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testFeePlansTabsWithP2And3And4XWithP2xEnableActiveP2xFirstTabsEnable()
    {
        $expected = array_merge(
            FeePlansMock::feePlansTabsExpected(2, true, 0, 0, 'general_2_0_0'),
            FeePlansMock::feePlansTabsExpected(3, true, 0, 0, 'general_2_0_0'),
            FeePlansMock::feePlansTabsExpected(4, false, 0, 0, 'general_2_0_0'),
        );

        $feePlanFromConfig = [
            'general_2_0_0' => [
                'state' => '1',
                'min_amount' => '5000',
                'max_amount' => '300000',
                'sort_order' => '2',
            ],
            'general_3_0_0' => [
                'state' => '1',
                'min_amount' => '5000',
                'max_amount' => '300000',
                'sort_order' => '3',
            ],
            'general_4_0_0' => [
                'state' => '0',
                'min_amount' => '5000',
                'max_amount' => '300000',
                'sort_order' => '4',
            ],
        ];

        $feePlan2X = FeePlansMock::feePlan(2, 0, 0, true, 10000, 200000, true, 5);
        $feePlan3X = FeePlansMock::feePlan(3, 0, 0, true, 10000, 200000, true, 5);
        $feePlan4X = FeePlansMock::feePlan(4);

        $feePlanList = new FeePlanList([$feePlan2X, $feePlan3X, $feePlan4X]);
        $this->feePlansProvider->expects($this->once())
            ->method('getOriginalFeePlan')
            ->willReturn($feePlanList);
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlanFromConfiguration')
            ->willReturn($feePlanFromConfig);
        $this->feePlanPresenter->expects($this->exactly(3))
            ->method('getTitle')
            ->withConsecutive(
                [FeePlansMock::feePlan(2)],
                [FeePlansMock::feePlan(3)],
                [FeePlansMock::feePlan(4)]
            )
            ->willReturnOnConsecutiveCalls(
                '2-installment payments',
                '3-installment payments',
                '4-installment payments'
            );

        $this->assertEquals($expected, $this->feePlansService->feePlansTabs());
    }

    public function testFeePlansTabsWithNewFeePlanAddAfterSaveInDb()
    {
        $expected = array_merge(
            FeePlansMock::feePlansTabsExpected(2, true, 0, 0, 'general_2_0_0'),
            FeePlansMock::feePlansTabsExpected(3, true, 0, 0, 'general_2_0_0'),
            FeePlansMock::feePlansTabsExpected(4, false, 0, 0, 'general_2_0_0'),
            FeePlansMock::feePlansTabsExpected(5, false, 0, 0, 'general_2_0_0'),
        );

        $feePlanFromConfig = [
            'general_2_0_0' => [
                'state' => '1',
                'min_amount' => '5000',
                'max_amount' => '300000',
                'sort_order' => '2',
            ],
            'general_3_0_0' => [
                'state' => '1',
                'min_amount' => '5000',
                'max_amount' => '300000',
                'sort_order' => '3',
            ],
            'general_4_0_0' => [
                'state' => '0',
                'min_amount' => '5000',
                'max_amount' => '300000',
                'sort_order' => '4',
            ]
        ];

        $feePlan2X = FeePlansMock::feePlan(2, 0, 0, true, 10000, 200000, true, 5);
        $feePlan3X = FeePlansMock::feePlan(3, 0, 0, true, 10000, 200000, true, 5);
        $feePlan4X = FeePlansMock::feePlan(4);
        $feePlan5X = FeePlansMock::feePlan(5);

        $feePlanList = new FeePlanList([$feePlan2X, $feePlan3X, $feePlan4X, $feePlan5X]);
        $this->feePlansProvider->expects($this->once())
            ->method('getOriginalFeePlan')
            ->willReturn($feePlanList);
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlanFromConfiguration')
            ->willReturn($feePlanFromConfig);
        $this->feePlanPresenter->expects($this->exactly(4))
            ->method('getTitle')
            ->withConsecutive(
                [FeePlansMock::feePlan(2)],
                [FeePlansMock::feePlan(3)],
                [FeePlansMock::feePlan(4)],
                [FeePlansMock::feePlan(5)]
            )
            ->willReturnOnConsecutiveCalls(
                '2-installment payments',
                '3-installment payments',
                '4-installment payments',
                '5-installment payments'
            );

        $this->assertEquals($expected, $this->feePlansService->feePlansTabs());
    }

    public function testFeePlansFieldsGetFeePlanListEmptyReturnEmptyArray()
    {
        $this->feePlansProvider->expects($this->once())
            ->method('getOriginalFeePlan')
            ->willReturn(new FeePlanList());

        $this->assertEquals([], $this->feePlansService->feePlansFields());
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testFeePlansFieldsWithP3X()
    {
        $expected = FeePlansMock::feePlanFieldsExpected(3);

        $feePlan3X = FeePlansMock::feePlan(3);

        $feePlanList = new FeePlanList([$feePlan3X]);
        $this->feePlansProvider->expects($this->once())
            ->method('getOriginalFeePlan')
            ->willReturn($feePlanList);

        $this->feePlanPresenter->expects($this->once())
            ->method('getLabel')
            ->with(FeePlansMock::feePlan(3))
            ->willReturn('Enable 3-installment payments');

        $this->translator->expects($this->any())
            ->method('trans')
            ->willReturnOnConsecutiveCalls(
                'Enabled',
                'Disabled',
                'Minimum amount (€)',
                'Minimum purchase amount to activate this plan',
                'Maximum amount (€)',
                'Maximum purchase amount to activate this plan',
                'Position',
                'Use relative values to set the order on the checkout page',
            );

        $this->assertEquals($expected, $this->feePlansService->feePlansFields());
    }

    public function testFieldsValueWithFeePlanFromApi()
    {
        $feePlanFromDb = FeePlansMock::almaFeePlanFromDb(3, 0, 0, '1', '10000', '200000', '5');
        $expected = [
            'ALMA_GENERAL_3_0_0_STATE' => '1',
            'ALMA_GENERAL_3_0_0_MIN_AMOUNT' => '100',
            'ALMA_GENERAL_3_0_0_MAX_AMOUNT' => '2000',
            'ALMA_GENERAL_3_0_0_SORT_ORDER' => '5',
        ];
        $feePlan3x = FeePlansMock::feePlan(3);
        $feePlanList = new FeePlanList([$feePlan3x]);
        $this->feePlansProvider->expects($this->once())
            ->method('getOriginalFeePlan')
            ->willReturn($feePlanList);
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlanFromConfiguration')
            ->willReturn($feePlanFromDb);
        $this->assertSame($expected, $this->feePlansService->fieldsValue());
    }

    public function testFieldsValueWithFeePlanFromDbAndNewPlanInApi()
    {
        $feePlanFromDb = FeePlansMock::almaFeePlanFromDb(3, 0, 0, '1', '15000', '200000', '5');
        $expected = [
            'ALMA_GENERAL_3_0_0_STATE' => '1',
            'ALMA_GENERAL_3_0_0_MIN_AMOUNT' => '150',
            'ALMA_GENERAL_3_0_0_MAX_AMOUNT' => '2000',
            'ALMA_GENERAL_3_0_0_SORT_ORDER' => '5',
            'ALMA_GENERAL_4_0_0_STATE' => '0',
            'ALMA_GENERAL_4_0_0_MIN_AMOUNT' => '100',
            'ALMA_GENERAL_4_0_0_MAX_AMOUNT' => '2000',
            'ALMA_GENERAL_4_0_0_SORT_ORDER' => '2',
        ];
        $feePlan3x = FeePlansMock::feePlan(3);
        $feePlan4x = FeePlansMock::feePlan(4);
        $feePlanList = new FeePlanList([$feePlan3x, $feePlan4x]);
        $this->feePlansProvider->expects($this->once())
            ->method('getOriginalFeePlan')
            ->willReturn($feePlanList);
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlanFromConfiguration')
            ->willReturn($feePlanFromDb);
        $this->assertSame($expected, $this->feePlansService->fieldsValue());
    }

    public function testFieldsValueWithFeePlanFromApiAndDbEmpty()
    {
        $this->feePlansProvider->expects($this->once())
            ->method('getOriginalFeePlan')
            ->willReturn(new FeePlanList());
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlanFromConfiguration')
            ->willReturn([]);
        $this->assertEquals([], $this->feePlansService->fieldsValue());
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testFieldsToSaveFromApiWithFeePlanList()
    {
        $feePlan = FeePlansMock::feePlan(3);
        $feePlanList = new FeePlanList([$feePlan]);
        $expected = FeePlansMock::almaFeePlanForDbExpected(3);
        $this->assertEquals($expected, $this->feePlansService->fieldsToSaveFromApi($feePlanList));
    }

    public function testFieldsToSaveFromApiWithEmptyFeePlanList()
    {
        $feePlanList = new FeePlanList();
        $plansEncode = json_encode([]);
        $expected = [
            'ALMA_FEE_PLAN_LIST' => $plansEncode
        ];
        $this->assertEquals($expected, $this->feePlansService->fieldsToSaveFromApi($feePlanList));
    }

    public function testFieldsToSaveFromPostWithFeePlanReturnOneKeyFeePlanJson()
    {
        $postFromForm = [
            'ALMA_OTHER_FIELD' => 'other_value',
            'ALMA_GENERAL_3_0_0_STATE' => '1',
            'ALMA_GENERAL_3_0_0_MIN_AMOUNT' => '100',
            'ALMA_GENERAL_3_0_0_MAX_AMOUNT' => '2000',
            'ALMA_GENERAL_3_0_0_SORT_ORDER' => '5',
        ];

        $expected = FeePlansMock::almaFeePlanForDbExpected(3, 0, 0, '1', '10000', '200000', '5');

        $this->assertEquals($expected, $this->feePlansService->fieldsToSaveFromPost($postFromForm));
    }

    public function testFieldsToSaveFromPostWithFeePlanEmptyReturnOneKeyFeePlanJsonEmpty()
    {
        $postFromForm = [
            'ALMA_OTHER_FIELD' => 'other_value',
        ];

        $plansEncode = json_encode([]);

        $expected = [
            'ALMA_FEE_PLAN_LIST' => $plansEncode
        ];

        $this->assertEquals($expected, $this->feePlansService->fieldsToSaveFromPost($postFromForm));
    }

    public function testSaveOriginalFeePlan()
    {
        $expected = '[{"allowed":true,"available_online":true,"customer_fee_variable":0,"deferred_days":0,"deferred_months":0,"installments_count":2,"kind":"general","max_purchase_amount":200000,"merchant_fee_variable":0,"merchant_fee_fixed":0,"min_purchase_amount":10000},{"allowed":true,"available_online":true,"customer_fee_variable":0,"deferred_days":0,"deferred_months":0,"installments_count":3,"kind":"general","max_purchase_amount":200000,"merchant_fee_variable":0,"merchant_fee_fixed":0,"min_purchase_amount":10000},{"allowed":true,"available_online":true,"customer_fee_variable":0,"deferred_days":0,"deferred_months":0,"installments_count":4,"kind":"general","max_purchase_amount":200000,"merchant_fee_variable":0,"merchant_fee_fixed":0,"min_purchase_amount":10000}]';
        $feePlan2x = FeePlansMock::feePlan(2);
        $feePlan3x = FeePlansMock::feePlan(3);
        $feePlan4x = FeePlansMock::feePlan(4);
        $feePlanList = new FeePlanList([$feePlan2x, $feePlan3x, $feePlan4x]);
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanList);
        $this->configurationRepository->expects($this->once())
            ->method('updateValue')
            ->with(FeePlansAdminForm::KEY_FIELD_ORIGINAL_FEE_PLAN, $expected);
        $this->feePlansService->saveOriginalFeePlan();
    }
}
