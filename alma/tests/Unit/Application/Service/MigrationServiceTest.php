<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use Alma\Client\Domain\Entity\FeePlanList;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Provider\FeePlansProvider;
use PrestaShop\Module\Alma\Application\Service\FeePlansService;
use PrestaShop\Module\Alma\Application\Service\MigrationService;
use PrestaShop\Module\Alma\Infrastructure\Form\CartWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FeePlansAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ProductWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Tests\Mocks\FeePlansMock;

class MigrationServiceTest extends TestCase
{
    /**
     * @var MigrationService
     */
    private MigrationService $migrationService;
    /**
     * @var ConfigurationRepository
     */
    private $configurationRepository;
    /**
     * @var FeePlansProvider
     */
    private $feePlansProvider;
    /**
     * @var FeePlansService
     */
    private $feePlansService;

    public function setUp(): void
    {
        $this->feePlansProvider = $this->createMock(FeePlansProvider::class);
        $this->feePlansService = $this->createMock(FeePlansService::class);
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->migrationService = new MigrationService(
            $this->feePlansProvider,
            $this->feePlansService,
            $this->configurationRepository
        );
    }

    public function testFeePlanMigrationWithOldData()
    {
        $oldDataFeePlan = '{"general_1_0_0":{"enabled":"1","min":100,"max":300000,"deferred_trigger_limit_days":null,"order":1},"general_1_15_0":{"enabled":"1","min":5000,"max":300000,"deferred_trigger_limit_days":null,"order":2},"general_1_30_0":{"enabled":"1","min":5000,"max":300000,"deferred_trigger_limit_days":null,"order":3},"general_2_0_0":{"enabled":"1","min":5000,"max":300000,"deferred_trigger_limit_days":null,"order":4},"general_3_0_0":{"enabled":"1","min":5000,"max":300000,"deferred_trigger_limit_days":null,"order":1},"general_4_0_0":{"enabled":"1","min":5000,"max":300000,"deferred_trigger_limit_days":null,"order":6},"general_10_0_0":{"enabled":"1","min":5000,"max":300000,"deferred_trigger_limit_days":null,"order":7},"general_12_0_0":{"enabled":"1","min":5000,"max":300000,"deferred_trigger_limit_days":null,"order":8}}';
        $newDataFeePlanToSave = '{"general_1_0_0":{"state":"1","min_amount":"100","max_amount":"300000","sort_order":"1"},"general_1_15_0":{"state":"1","min_amount":"5000","max_amount":"300000","sort_order":"2"},"general_1_30_0":{"state":"1","min_amount":"5000","max_amount":"300000","sort_order":"3"},"general_2_0_0":{"state":"1","min_amount":"5000","max_amount":"300000","sort_order":"4"},"general_3_0_0":{"state":"1","min_amount":"5000","max_amount":"300000","sort_order":"1"},"general_4_0_0":{"state":"1","min_amount":"5000","max_amount":"300000","sort_order":"6"},"general_10_0_0":{"state":"1","min_amount":"5000","max_amount":"300000","sort_order":"7"},"general_12_0_0":{"state":"1","min_amount":"5000","max_amount":"300000","sort_order":"8"}}';
        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with('ALMA_FEE_PLANS')
            ->willReturn($oldDataFeePlan);
        $this->configurationRepository->expects($this->once())
            ->method('updateValue')
            ->with(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_LIST, $newDataFeePlanToSave);
        $this->migrationService->feePlanMigration();
    }

    public function testFeePlanMigrationWithoutOldDataOrWithoutKeyInConfiguration()
    {
        $oldDataFeePlan = '';
        $newDataFeePlanToSave = '{"general_1_0_0":{"state":"0","min_amount":"100","max_amount":"200000","sort_order":"1"},"general_2_0_0":{"state":"0","min_amount":"10000","max_amount":"200000","sort_order":"2"},"general_3_0_0":{"state":"1","min_amount":"10000","max_amount":"200000","sort_order":"3"},"general_4_0_0":{"state":"0","min_amount":"10000","max_amount":"200000","sort_order":"4"}}';
        $feePlanP1x = FeePlansMock::feePlan(1, 0, 0, false, 100);
        $feePlanP2x = FeePlansMock::feePlan(2, 0, 0, false);
        $feePlanP3x = FeePlansMock::feePlan(3);
        $feePlanP4x = FeePlansMock::feePlan(4, 0, 0, false);

        $feePlanList = new FeePlanList([$feePlanP1x, $feePlanP2x, $feePlanP3x, $feePlanP4x]);
        $fieldFeePlan[FeePlansAdminForm::KEY_FIELD_FEE_PLAN_LIST] = array_merge(
            FeePlansMock::almaFeePlanFromDb(1, 0, 0, '0', '100', '200000', '1'),
            FeePlansMock::almaFeePlanFromDb(2, 0, 0, '0', '10000', '200000', '2'),
            FeePlansMock::almaFeePlanFromDb(3, 0, 0, '1', '10000', '200000', '3'),
            FeePlansMock::almaFeePlanFromDb(4, 0, 0, '0', '10000', '200000', '4')
        );

        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with('ALMA_FEE_PLANS')
            ->willReturn($oldDataFeePlan);
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanList);
        $this->feePlansService->expects($this->once())
            ->method('fieldsToSaveFromApi')
            ->with($feePlanList)
            ->willReturn($fieldFeePlan);
        $this->configurationRepository->expects($this->once())
            ->method('updateValue')
            ->with(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_LIST, $newDataFeePlanToSave);
        $this->migrationService->feePlanMigration();
    }

    public function testWidgetMigrationWithOldDataAndWidgetPositionEnabled()
    {
        $oldWidgetDataToMigrate = [
            'ALMA_SHOW_PRODUCT_ELIGIBILITY' => '1',
            'ALMA_PRODUCT_WDGT_NOT_ELGBL' => '1',
            'ALMA_SHOW_CART_ELIGIBILITY' => '1',
            'ALMA_CART_WDGT_NOT_ELGBL' => '1',
        ];

        $oldWidgetPositionDataToRemove = [
            'ALMA_WIDGET_POSITION_CUSTOM' => '1',
            'ALMA_CART_WIDGET_POSITION_CUSTOM' => '1',
        ];

        $this->configurationRepository->expects($this->exactly(6))
            ->method('get')
            ->willReturnMap([
                ['ALMA_SHOW_PRODUCT_ELIGIBILITY', $oldWidgetDataToMigrate['ALMA_SHOW_PRODUCT_ELIGIBILITY']],
                ['ALMA_PRODUCT_WDGT_NOT_ELGBL', $oldWidgetDataToMigrate['ALMA_PRODUCT_WDGT_NOT_ELGBL']],
                ['ALMA_SHOW_CART_ELIGIBILITY', $oldWidgetDataToMigrate['ALMA_SHOW_CART_ELIGIBILITY']],
                ['ALMA_CART_WDGT_NOT_ELGBL', $oldWidgetDataToMigrate['ALMA_CART_WDGT_NOT_ELGBL']],
                ['ALMA_WIDGET_POSITION_CUSTOM', $oldWidgetPositionDataToRemove['ALMA_WIDGET_POSITION_CUSTOM']],
                ['ALMA_CART_WIDGET_POSITION_CUSTOM', $oldWidgetPositionDataToRemove['ALMA_CART_WIDGET_POSITION_CUSTOM']],
            ]);
        $this->configurationRepository->expects($this->exactly(4))
            ->method('updateValue')
            ->willReturnMap([
                [ProductWidgetAdminForm::KEY_FIELD_PRODUCT_WIDGET_STATE, $oldWidgetDataToMigrate['ALMA_SHOW_PRODUCT_ELIGIBILITY'], true],
                [ProductWidgetAdminForm::KEY_FIELD_PRODUCT_WIDGET_DISPLAY_NOT_ELIGIBLE, $oldWidgetDataToMigrate['ALMA_PRODUCT_WDGT_NOT_ELGBL'], true],
                [CartWidgetAdminForm::KEY_FIELD_CART_WIDGET_STATE, $oldWidgetDataToMigrate['ALMA_SHOW_CART_ELIGIBILITY'], true],
                [CartWidgetAdminForm::KEY_FIELD_CART_WIDGET_DISPLAY_NOT_ELIGIBLE, $oldWidgetDataToMigrate['ALMA_CART_WDGT_NOT_ELGBL'], true],
            ]);
        $this->configurationRepository->expects($this->exactly(5))
            ->method('deleteByName')
            ->willReturnMap([
                ['ALMA_PRODUCT_PRICE_SELECTOR', true],
                ['ALMA_PRODUCT_ATTR_SELECTOR', true],
                ['ALMA_PRODUCT_ATTR_RADIO_SELECTOR', true],
                ['ALMA_PRODUCT_COLOR_PICK_SELECTOR', true],
                ['ALMA_PRODUCT_QUANTITY_SELECTOR', true],
            ]);

        $this->migrationService->widgetMigration();
    }

    public function testWidgetMigrationWithOldDataAndWidgetPositionDisabled()
    {
        $oldWidgetDataToMigrate = [
            'ALMA_SHOW_PRODUCT_ELIGIBILITY' => '1',
            'ALMA_PRODUCT_WDGT_NOT_ELGBL' => '1',
            'ALMA_SHOW_CART_ELIGIBILITY' => '1',
            'ALMA_CART_WDGT_NOT_ELGBL' => '1',
        ];

        $oldWidgetPositionDataToRemove = [
            'ALMA_WIDGET_POSITION_CUSTOM' => '0',
            'ALMA_CART_WIDGET_POSITION_CUSTOM' => '0',
        ];

        $this->configurationRepository->expects($this->exactly(6))
            ->method('get')
            ->willReturnMap([
                ['ALMA_SHOW_PRODUCT_ELIGIBILITY', $oldWidgetDataToMigrate['ALMA_SHOW_PRODUCT_ELIGIBILITY']],
                ['ALMA_PRODUCT_WDGT_NOT_ELGBL', $oldWidgetDataToMigrate['ALMA_PRODUCT_WDGT_NOT_ELGBL']],
                ['ALMA_SHOW_CART_ELIGIBILITY', $oldWidgetDataToMigrate['ALMA_SHOW_CART_ELIGIBILITY']],
                ['ALMA_CART_WDGT_NOT_ELGBL', $oldWidgetDataToMigrate['ALMA_CART_WDGT_NOT_ELGBL']],
                ['ALMA_WIDGET_POSITION_CUSTOM', $oldWidgetPositionDataToRemove['ALMA_WIDGET_POSITION_CUSTOM']],
                ['ALMA_CART_WIDGET_POSITION_CUSTOM', $oldWidgetPositionDataToRemove['ALMA_CART_WIDGET_POSITION_CUSTOM']],
            ]);
        $this->configurationRepository->expects($this->exactly(4))
            ->method('updateValue')
            ->willReturnMap([
                [ProductWidgetAdminForm::KEY_FIELD_PRODUCT_WIDGET_STATE, $oldWidgetDataToMigrate['ALMA_SHOW_PRODUCT_ELIGIBILITY'], true],
                [ProductWidgetAdminForm::KEY_FIELD_PRODUCT_WIDGET_DISPLAY_NOT_ELIGIBLE, $oldWidgetDataToMigrate['ALMA_PRODUCT_WDGT_NOT_ELGBL'], true],
                [CartWidgetAdminForm::KEY_FIELD_CART_WIDGET_STATE, $oldWidgetDataToMigrate['ALMA_SHOW_CART_ELIGIBILITY'], true],
                [CartWidgetAdminForm::KEY_FIELD_CART_WIDGET_DISPLAY_NOT_ELIGIBLE, $oldWidgetDataToMigrate['ALMA_CART_WDGT_NOT_ELGBL'], true],
            ]);
        $this->configurationRepository->expects($this->exactly(9))
            ->method('deleteByName')
            ->willReturnMap([
                ['ALMA_PRODUCT_PRICE_SELECTOR', true],
                ['ALMA_PRODUCT_ATTR_SELECTOR', true],
                ['ALMA_PRODUCT_ATTR_RADIO_SELECTOR', true],
                ['ALMA_PRODUCT_COLOR_PICK_SELECTOR', true],
                ['ALMA_PRODUCT_QUANTITY_SELECTOR', true],
                ['ALMA_WIDGET_POSITION_CUSTOM', true],
                ['ALMA_WIDGET_POSITION_SELECTOR', true],
                ['ALMA_CART_WIDGET_POSITION_CUSTOM', true],
                ['ALMA_CART_WDGT_POS_SELECTOR', true],
            ]);

        $this->migrationService->widgetMigration();
    }
}
