<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use Alma\Client\Domain\Entity\FeePlanList;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Provider\FeePlansProvider;
use PrestaShop\Module\Alma\Application\Service\FeePlansService;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FeePlansAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ToolsProxy;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Tests\Mocks\FeePlansMock;

class FeePlansServiceTest extends TestCase
{
    /**
     * @var FeePlansProvider
     */
    private $feePlansProvider;

    public function setUp(): void
    {
        $this->context = $this->createMock(\Context::class);
        $this->feePlansProvider = $this->createMock(FeePlansProvider::class);
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->toolsProxy = $this->createMock(ToolsProxy::class);
        $this->feePlansService = new FeePlansService(
            $this->context,
            $this->feePlansProvider,
            $this->configurationRepository,
            $this->toolsProxy
        );
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testFeePlansTabsGetFeePlanListEmptyReturnEmptyArray()
    {
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlanList')
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

        $feePlan2X = FeePlansMock::feePlan(2);

        $feePlan3X = FeePlansMock::feePlan(3, 0, 0, true, 10000, 200000, true, 5);

        $feePlan4X = FeePlansMock::feePlan(4);

        $feePlanList = new FeePlanList([$feePlan2X, $feePlan3X, $feePlan4X]);
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanList);
        $this->configurationRepository->expects($this->exactly(3))
            ->method('get')
            ->willReturnMap([
                [sprintf(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_STATE, 'GENERAL_2_0_0'), '0'],
                [sprintf(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_STATE, 'GENERAL_3_0_0'), '1'],
                [sprintf(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_STATE, 'GENERAL_4_0_0'), '0'],
            ]);
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

        $feePlan2X = FeePlansMock::feePlan(2, 0, 0, true, 10000, 200000, true, 5);

        $feePlan3X = FeePlansMock::feePlan(3, 0, 0, true, 10000, 200000, true, 5);

        $feePlan4X = FeePlansMock::feePlan(4);

        $feePlanList = new FeePlanList([$feePlan2X, $feePlan3X, $feePlan4X]);
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanList);
        $this->configurationRepository->expects($this->exactly(3))
            ->method('get')
            ->willReturnMap([
                [sprintf(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_STATE, 'GENERAL_2_0_0'), '1'],
                [sprintf(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_STATE, 'GENERAL_3_0_0'), '1'],
                [sprintf(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_STATE, 'GENERAL_4_0_0'), '0'],
            ]);

        $this->assertEquals($expected, $this->feePlansService->feePlansTabs());
    }

    public function testFeePlansFieldsGetFeePlanListEmptyReturnEmptyArray()
    {
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlanList')
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
            ->method('getFeePlanList')
            ->willReturn($feePlanList);

        $this->assertEquals($expected, $this->feePlansService->feePlansFields());
    }

    public function testFeePlansFieldsValueGetFeePlanListEmptyReturnEmptyArray()
    {
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn(new FeePlanList());

        $this->assertEquals([], $this->feePlansService->fieldsValue());
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testFieldsValueFirstSaveWithoutMerchantIdSavedInDbReturnFieldValueFromClient()
    {
        $feePlanFromClient = FeePlansMock::feePlanFieldsValueExpected(3);

        $feePlan3X = FeePlansMock::feePlan(3);

        $feePlanList = new FeePlanList([$feePlan3X]);
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanList);
        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MERCHANT_ID)
            ->willReturn('');

        $this->assertEquals($feePlanFromClient, $this->feePlansService->fieldsValue());
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testFieldsValueWithMerchantIdSavedInDbReturnFieldValueFromPost()
    {
        $feePlanFromPost = FeePlansMock::feePlanFieldsValueExpected(3, 0, 0, 0, 10000, 100000);

        $feePlan3X = FeePlansMock::feePlan(3);

        $feePlanList = new FeePlanList([$feePlan3X]);
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanList);
        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MERCHANT_ID)
            ->willReturn('merchant_id');
        $this->toolsProxy->expects($this->exactly(4))
            ->method('getValue')
            ->willReturnMap(
                [
                    ['ALMA_GENERAL_3_0_0_STATE', false, '0'],
                    ['ALMA_GENERAL_3_0_0_MIN_AMOUNT', false, '100'],
                    ['ALMA_GENERAL_3_0_0_MAX_AMOUNT', false, '1000'],
                    ['ALMA_GENERAL_3_0_0_SORT_ORDER', false, '1'],
                ]
            );

        $this->assertEquals($feePlanFromPost, $this->feePlansService->fieldsValue());
    }
}
