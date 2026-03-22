<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use Alma\Client\Domain\Entity\FeePlanList;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Provider\FeePlansProvider;
use PrestaShop\Module\Alma\Application\Service\FeePlansService;
use PrestaShop\Module\Alma\Application\Service\MigrationService;
use PrestaShop\Module\Alma\Application\Service\PaymentButtonService;
use PrestaShop\Module\Alma\Infrastructure\Form\CartWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FeePlansAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ProductWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\LanguageRepository;
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
    /**
     * @var PaymentButtonService
     */
    private $paymentButtonService;
    /**
     * @var LanguageRepository
     */
    private $languageRepository;

    public function setUp(): void
    {
        $this->feePlansProvider = $this->createMock(FeePlansProvider::class);
        $this->feePlansService = $this->createMock(FeePlansService::class);
        $this->paymentButtonService = $this->createMock(PaymentButtonService::class);
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->languageRepository = $this->createMock(LanguageRepository::class);
        $this->migrationService = new MigrationService(
            $this->feePlansProvider,
            $this->feePlansService,
            $this->paymentButtonService,
            $this->configurationRepository,
            $this->languageRepository
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

    public function testLanguageKeyMigrationWithOneLang()
    {
        $oldLanguageKey = [
            'ALMA_PAY_NOW_BUTTON_TITLE' => '{"1":{"locale":"en-US","string":"Pay now by credit card"}}',
            'ALMA_PAY_NOW_BUTTON_DESC' => '{"1":{"locale":"en-US","string":"Fast and secure payments."}}',
            'ALMA_PNX_BUTTON_TITLE' => '{"1":{"locale":"en-US","string":"Pay in %d installments"}}',
            'ALMA_PNX_BUTTON_DESC' => '{"1":{"locale":"en-US","string":"Fast and secure payment by credit card."}}',
            'ALMA_PNX_AIR_BUTTON_TITLE' => '{"1":{"locale":"en-US","string":"Pay in %d installments"}}',
            'ALMA_PNX_AIR_BUTTON_DESC' => '{"1":{"locale":"en-US","string":"Fast and secure payment by credit card."}}',
            'ALMA_DEFERRED_BUTTON_TITLE' => '{"1":{"locale":"en-US","string":"Buy now Pay in %d days"}}',
            'ALMA_DEFERRED_BUTTON_DESC' => '{"1":{"locale":"en-US","string":"Fast and secure payment by credit card."}}',
            'ALMA_NOT_ELIGIBLE_CATEGORIES' => '{"1":{"locale":"en-US","string":"Your cart is not eligible for payments with Alma."}}',
        ];
        $this->configurationRepository->expects($this->exactly(9))
            ->method('get')
            ->willReturnMap([
                ['ALMA_PAY_NOW_BUTTON_TITLE', $oldLanguageKey['ALMA_PAY_NOW_BUTTON_TITLE']],
                ['ALMA_PAY_NOW_BUTTON_DESC', $oldLanguageKey['ALMA_PAY_NOW_BUTTON_DESC']],
                ['ALMA_PNX_BUTTON_TITLE', $oldLanguageKey['ALMA_PNX_BUTTON_TITLE']],
                ['ALMA_PNX_BUTTON_DESC', $oldLanguageKey['ALMA_PNX_BUTTON_DESC']],
                ['ALMA_PNX_AIR_BUTTON_TITLE', $oldLanguageKey['ALMA_PNX_AIR_BUTTON_TITLE']],
                ['ALMA_PNX_AIR_BUTTON_DESC', $oldLanguageKey['ALMA_PNX_AIR_BUTTON_DESC']],
                ['ALMA_DEFERRED_BUTTON_TITLE', $oldLanguageKey['ALMA_DEFERRED_BUTTON_TITLE']],
                ['ALMA_DEFERRED_BUTTON_DESC', $oldLanguageKey['ALMA_DEFERRED_BUTTON_DESC']],
                ['ALMA_NOT_ELIGIBLE_CATEGORIES', $oldLanguageKey['ALMA_NOT_ELIGIBLE_CATEGORIES']],
            ]);
        $this->configurationRepository->expects($this->exactly(9))
            ->method('updateValue')
            ->willReturnMap([
                ['ALMA_PAYNOW_BUTTON_TITLE_1', 'Pay now by credit card', true],
                ['ALMA_PAYNOW_BUTTON_DESC_1', 'Fast and secure payments.', true],
                ['ALMA_PNX_BUTTON_TITLE_1', 'Pay in %d installments', true],
                ['ALMA_PNX_BUTTON_DESC_1', 'Fast and secure payment by credit card.', true],
                ['ALMA_CREDIT_BUTTON_TITLE_1', 'Pay in %d installments', true],
                ['ALMA_CREDIT_BUTTON_DESC_1', 'Fast and secure payment by credit card.', true],
                ['ALMA_PAYLATER_BUTTON_TITLE_1', 'Buy now Pay in %d days', true],
                ['ALMA_PAYLATER_BUTTON_DESC_1', 'Fast and secure payment by credit card.', true],
                ['ALMA_EXCLUDED_CATEGORIES_MESSAGE_1', 'Your cart is not eligible for payments with Alma.', true],
            ]);
        $this->migrationService->languageKeyMigration();
    }

    public function testLanguageKeyMigrationWithTwoLang()
    {
        $oldLanguageKey = [
            'ALMA_PAY_NOW_BUTTON_TITLE' => '{"1":{"locale":"en-US","string":"Pay now by credit card"},"2":{"locale":"fr-FR","string":"Payer maintenant par carte de crédit"}}',
            'ALMA_PAY_NOW_BUTTON_DESC' => '{"1":{"locale":"en-US","string":"Fast and secure payments."},"2":{"locale":"fr-FR","string":"Paiements rapides et sécurisés."}}',
            'ALMA_PNX_BUTTON_TITLE' => '{"1":{"locale":"en-US","string":"Pay in %d installments"},"2":{"locale":"fr-FR","string":"Payer en %d fois"}}',
            'ALMA_PNX_BUTTON_DESC' => '{"1":{"locale":"en-US","string":"Fast and secure payment by credit card."},"2":{"locale":"fr-FR","string":"Paiement rapide et sécurisé par carte de crédit."}}',
            'ALMA_PNX_AIR_BUTTON_TITLE' => '{"1":{"locale":"en-US","string":"Pay in %d installments"},"2":{"locale":"fr-FR","string":"Payer en %d fois"}}',
            'ALMA_PNX_AIR_BUTTON_DESC' => '{"1":{"locale":"en-US","string":"Fast and secure payment by credit card."},"2":{"locale":"fr-FR","string":"Paiement rapide et sécurisé par carte de crédit."}}',
            'ALMA_DEFERRED_BUTTON_TITLE' => '{"1":{"locale":"en-US","string":"Buy now Pay in %d days"},"2":{"locale":"fr-FR","string":"Achetez maintenant Payez dans %d jours"}}',
            'ALMA_DEFERRED_BUTTON_DESC' => '{"1":{"locale":"en-US","string":"Fast and secure payment by credit card."},"2":{"locale":"fr-FR","string":"Paiement rapide et sécurisé par carte de crédit."}}',
            'ALMA_NOT_ELIGIBLE_CATEGORIES' => '{"1":{"locale":"en-US","string":"Your cart is not eligible for payments with Alma."},"2":{"locale":"fr-FR","string":"Votre panier n\'est pas éligible aux paiements avec Alma."}}',
        ];
        $this->configurationRepository->expects($this->exactly(9))
            ->method('get')
            ->willReturnMap([
                ['ALMA_PAY_NOW_BUTTON_TITLE', $oldLanguageKey['ALMA_PAY_NOW_BUTTON_TITLE']],
                ['ALMA_PAY_NOW_BUTTON_DESC', $oldLanguageKey['ALMA_PAY_NOW_BUTTON_DESC']],
                ['ALMA_PNX_BUTTON_TITLE', $oldLanguageKey['ALMA_PNX_BUTTON_TITLE']],
                ['ALMA_PNX_BUTTON_DESC', $oldLanguageKey['ALMA_PNX_BUTTON_DESC']],
                ['ALMA_PNX_AIR_BUTTON_TITLE', $oldLanguageKey['ALMA_PNX_AIR_BUTTON_TITLE']],
                ['ALMA_PNX_AIR_BUTTON_DESC', $oldLanguageKey['ALMA_PNX_AIR_BUTTON_DESC']],
                ['ALMA_DEFERRED_BUTTON_TITLE', $oldLanguageKey['ALMA_DEFERRED_BUTTON_TITLE']],
                ['ALMA_DEFERRED_BUTTON_DESC', $oldLanguageKey['ALMA_DEFERRED_BUTTON_DESC']],
                ['ALMA_NOT_ELIGIBLE_CATEGORIES', $oldLanguageKey['ALMA_NOT_ELIGIBLE_CATEGORIES']],
            ]);
        $this->configurationRepository->expects($this->exactly(18))
            ->method('updateValue')
            ->willReturnMap([
                ['ALMA_PAYNOW_BUTTON_TITLE_1', 'Pay now by credit card', true],
                ['ALMA_PAYNOW_BUTTON_TITLE_2', 'Payer maintenant par carte de crédit', true],
                ['ALMA_PAYNOW_BUTTON_DESC_1', 'Fast and secure payments.', true],
                ['ALMA_PAYNOW_BUTTON_DESC_2', 'Paiements rapides et sécurisés.', true],
                ['ALMA_PNX_BUTTON_TITLE_1', 'Pay in %d installments', true],
                ['ALMA_PNX_BUTTON_TITLE_2', 'Payer en %d fois', true],
                ['ALMA_PNX_BUTTON_DESC_1', 'Fast and secure payment by credit card.', true],
                ['ALMA_PNX_BUTTON_DESC_2', 'Paiement rapide et sécurisé par carte de crédit.', true],
                ['ALMA_CREDIT_BUTTON_TITLE_1', 'Pay in %d installments', true],
                ['ALMA_CREDIT_BUTTON_TITLE_2', 'Payer en %d fois', true],
                ['ALMA_CREDIT_BUTTON_DESC_1', 'Fast and secure payment by credit card.', true],
                ['ALMA_CREDIT_BUTTON_DESC_2', 'Paiement rapide et sécurisé par carte de crédit.', true],
                ['ALMA_PAYLATER_BUTTON_TITLE_1', 'Buy now Pay in %d days', true],
                ['ALMA_PAYLATER_BUTTON_TITLE_2', 'Achetez maintenant Payez dans %d jours', true],
                ['ALMA_PAYLATER_BUTTON_DESC_1', 'Fast and secure payment by credit card.', true],
                ['ALMA_PAYLATER_BUTTON_DESC_2', 'Paiement rapide et sécurisé par carte de crédit.', true],
                ['ALMA_EXCLUDED_CATEGORIES_MESSAGE_1', 'Your cart is not eligible for payments with Alma.', true],
                ['ALMA_EXCLUDED_CATEGORIES_MESSAGE_2', 'Votre panier n\'est pas éligible aux paiements avec Alma.', true],
            ]);
        $this->migrationService->languageKeyMigration();
    }

    public function testLanguageKeyMigrationWithoutLangValueAndOneLangConfiguredInStore()
    {
        $oldLanguageKey = [
            'ALMA_PAY_NOW_BUTTON_TITLE' => '',
            'ALMA_PAY_NOW_BUTTON_DESC' => '',
            'ALMA_PNX_BUTTON_TITLE' => '',
            'ALMA_PNX_BUTTON_DESC' => '',
            'ALMA_PNX_AIR_BUTTON_TITLE' => '',
            'ALMA_PNX_AIR_BUTTON_DESC' => '',
            'ALMA_DEFERRED_BUTTON_TITLE' => '',
            'ALMA_DEFERRED_BUTTON_DESC' => '',
            'ALMA_NOT_ELIGIBLE_CATEGORIES' => '',
        ];
        $this->configurationRepository->expects($this->exactly(9))
            ->method('get')
            ->willReturnMap([
                ['ALMA_PAY_NOW_BUTTON_TITLE', $oldLanguageKey['ALMA_PAY_NOW_BUTTON_TITLE']],
                ['ALMA_PAY_NOW_BUTTON_DESC', $oldLanguageKey['ALMA_PAY_NOW_BUTTON_DESC']],
                ['ALMA_PNX_BUTTON_TITLE', $oldLanguageKey['ALMA_PNX_BUTTON_TITLE']],
                ['ALMA_PNX_BUTTON_DESC', $oldLanguageKey['ALMA_PNX_BUTTON_DESC']],
                ['ALMA_PNX_AIR_BUTTON_TITLE', $oldLanguageKey['ALMA_PNX_AIR_BUTTON_TITLE']],
                ['ALMA_PNX_AIR_BUTTON_DESC', $oldLanguageKey['ALMA_PNX_AIR_BUTTON_DESC']],
                ['ALMA_DEFERRED_BUTTON_TITLE', $oldLanguageKey['ALMA_DEFERRED_BUTTON_TITLE']],
                ['ALMA_DEFERRED_BUTTON_DESC', $oldLanguageKey['ALMA_DEFERRED_BUTTON_DESC']],
                ['ALMA_NOT_ELIGIBLE_CATEGORIES', $oldLanguageKey['ALMA_NOT_ELIGIBLE_CATEGORIES']],
            ]);
        $this->paymentButtonService->expects($this->once())
            ->method('defaultFieldsToSave')
            ->willReturn([
                'ALMA_PAYNOW_BUTTON_TITLE_1' => 'Pay now by credit card',
                'ALMA_PAYNOW_BUTTON_DESC_1' => 'Fast and secure payments.',
                'ALMA_PNX_BUTTON_TITLE_1' => 'Pay in %d installments',
                'ALMA_PNX_BUTTON_DESC_1' => 'Fast and secure payment by credit card.',
                'ALMA_CREDIT_BUTTON_TITLE_1' => 'Pay in %d installments',
                'ALMA_CREDIT_BUTTON_DESC_1' => 'Fast and secure payment by credit card.',
                'ALMA_PAYLATER_BUTTON_TITLE_1' => 'Buy now Pay in %d days',
                'ALMA_PAYLATER_BUTTON_DESC_1' => 'Fast and secure payment by credit card.',
                'ALMA_EXCLUDED_CATEGORIES_MESSAGE_1' => 'Your cart is not eligible for payments with Alma.',
            ]);
        $this->languageRepository->expects($this->once())
            ->method('getActiveLanguages')
            ->willReturn([
                ['id_lang' => 1, 'locale' => 'en-US'],
            ]);
        $this->configurationRepository->expects($this->exactly(9))
            ->method('updateValue')
            ->willReturnMap([
                ['ALMA_PAYNOW_BUTTON_TITLE_1', 'Pay now by credit card', true],
                ['ALMA_PAYNOW_BUTTON_DESC_1', 'Fast and secure payments.', true],
                ['ALMA_PNX_BUTTON_TITLE_1', 'Pay in %d installments', true],
                ['ALMA_PNX_BUTTON_DESC_1', 'Fast and secure payment by credit card.', true],
                ['ALMA_CREDIT_BUTTON_TITLE_1', 'Pay in %d installments', true],
                ['ALMA_CREDIT_BUTTON_DESC_1', 'Fast and secure payment by credit card.', true],
                ['ALMA_PAYLATER_BUTTON_TITLE_1', 'Buy now Pay in %d days', true],
                ['ALMA_PAYLATER_BUTTON_DESC_1', 'Fast and secure payment by credit card.', true],
                ['ALMA_EXCLUDED_CATEGORIES_MESSAGE_1', 'Your cart is not eligible for payments with Alma.', true],
            ]);
        $this->migrationService->languageKeyMigration();
    }

    public function testLanguageKeyMigrationWithoutLangValueAndTwoLangConfiguredInStore()
    {
        $oldLanguageKey = [
            'ALMA_PAY_NOW_BUTTON_TITLE' => '',
            'ALMA_PAY_NOW_BUTTON_DESC' => '',
            'ALMA_PNX_BUTTON_TITLE' => '',
            'ALMA_PNX_BUTTON_DESC' => '',
            'ALMA_PNX_AIR_BUTTON_TITLE' => '',
            'ALMA_PNX_AIR_BUTTON_DESC' => '',
            'ALMA_DEFERRED_BUTTON_TITLE' => '',
            'ALMA_DEFERRED_BUTTON_DESC' => '',
            'ALMA_NOT_ELIGIBLE_CATEGORIES' => '',
        ];
        $this->configurationRepository->expects($this->exactly(9))
            ->method('get')
            ->willReturnMap([
                ['ALMA_PAY_NOW_BUTTON_TITLE', $oldLanguageKey['ALMA_PAY_NOW_BUTTON_TITLE']],
                ['ALMA_PAY_NOW_BUTTON_DESC', $oldLanguageKey['ALMA_PAY_NOW_BUTTON_DESC']],
                ['ALMA_PNX_BUTTON_TITLE', $oldLanguageKey['ALMA_PNX_BUTTON_TITLE']],
                ['ALMA_PNX_BUTTON_DESC', $oldLanguageKey['ALMA_PNX_BUTTON_DESC']],
                ['ALMA_PNX_AIR_BUTTON_TITLE', $oldLanguageKey['ALMA_PNX_AIR_BUTTON_TITLE']],
                ['ALMA_PNX_AIR_BUTTON_DESC', $oldLanguageKey['ALMA_PNX_AIR_BUTTON_DESC']],
                ['ALMA_DEFERRED_BUTTON_TITLE', $oldLanguageKey['ALMA_DEFERRED_BUTTON_TITLE']],
                ['ALMA_DEFERRED_BUTTON_DESC', $oldLanguageKey['ALMA_DEFERRED_BUTTON_DESC']],
                ['ALMA_NOT_ELIGIBLE_CATEGORIES', $oldLanguageKey['ALMA_NOT_ELIGIBLE_CATEGORIES']],
            ]);
        $this->paymentButtonService->expects($this->once())
            ->method('defaultFieldsToSave')
            ->willReturn([
                'ALMA_PAYNOW_BUTTON_TITLE_1' => 'Pay now by credit card',
                'ALMA_PAYNOW_BUTTON_TITLE_2' => 'Payer maintenant par carte de crédit',
                'ALMA_PAYNOW_BUTTON_DESC_1' => 'Fast and secure payments.',
                'ALMA_PAYNOW_BUTTON_DESC_2' => 'Paiements rapides et sécurisés.',
                'ALMA_PNX_BUTTON_TITLE_1' => 'Pay in %d installments',
                'ALMA_PNX_BUTTON_TITLE_2' => 'Payer en %d fois',
                'ALMA_PNX_BUTTON_DESC_1' => 'Fast and secure payment by credit card.',
                'ALMA_PNX_BUTTON_DESC_2' => 'Paiement rapide et sécurisé par carte de crédit.',
                'ALMA_CREDIT_BUTTON_TITLE_1' => 'Pay in %d installments',
                'ALMA_CREDIT_BUTTON_TITLE_2' => 'Payer en %d fois',
                'ALMA_CREDIT_BUTTON_DESC_1' => 'Fast and secure payment by credit card.',
                'ALMA_CREDIT_BUTTON_DESC_2' => 'Paiement rapide et sécurisé par carte de crédit.',
                'ALMA_PAYLATER_BUTTON_TITLE_1' => 'Buy now Pay in %d days',
                'ALMA_PAYLATER_BUTTON_TITLE_2' => 'Achetez maintenant Payez dans %d jours',
                'ALMA_PAYLATER_BUTTON_DESC_1' => 'Fast and secure payment by credit card.',
                'ALMA_PAYLATER_BUTTON_DESC_2' => 'Paiement rapide et sécurisé par carte de crédit.',
                'ALMA_EXCLUDED_CATEGORIES_MESSAGE_1' => 'Your cart is not eligible for payments with Alma.',
                'ALMA_EXCLUDED_CATEGORIES_MESSAGE_2' => 'Votre panier n\'est pas éligible aux paiements avec Alma.',
            ]);
        $this->languageRepository->expects($this->once())
            ->method('getActiveLanguages')
            ->willReturn([
                ['id_lang' => 1, 'locale' => 'en-US'],
                ['id_lang' => 2, 'locale' => 'fr-FR'],
            ]);
        $this->configurationRepository->expects($this->exactly(18))
            ->method('updateValue')
            ->willReturnMap([
                ['ALMA_PAYNOW_BUTTON_TITLE_1', 'Pay now by credit card', true],
                ['ALMA_PAYNOW_BUTTON_TITLE_2', 'Payer maintenant par carte de crédit', true],
                ['ALMA_PAYNOW_BUTTON_DESC_1', 'Fast and secure payments.', true],
                ['ALMA_PAYNOW_BUTTON_DESC_2', 'Paiements rapides et sécurisés.', true],
                ['ALMA_PNX_BUTTON_TITLE_1', 'Pay in %d installments', true],
                ['ALMA_PNX_BUTTON_TITLE_2', 'Payer en %d fois', true],
                ['ALMA_PNX_BUTTON_DESC_1', 'Fast and secure payment by credit card.', true],
                ['ALMA_PNX_BUTTON_DESC_2', 'Paiement rapide et sécurisé par carte de crédit.', true],
                ['ALMA_CREDIT_BUTTON_TITLE_1', 'Pay in %d installments', true],
                ['ALMA_CREDIT_BUTTON_TITLE_2', 'Payer en %d fois', true],
                ['ALMA_CREDIT_BUTTON_DESC_1', 'Fast and secure payment by credit card.', true],
                ['ALMA_CREDIT_BUTTON_DESC_2', 'Paiement rapide et sécurisé par carte de crédit.', true],
                ['ALMA_PAYLATER_BUTTON_TITLE_1', 'Buy now Pay in %d days', true],
                ['ALMA_PAYLATER_BUTTON_TITLE_2', 'Achetez maintenant Payez dans %d jours', true],
                ['ALMA_PAYLATER_BUTTON_DESC_1', 'Fast and secure payment by credit card.', true],
                ['ALMA_PAYLATER_BUTTON_DESC_2', 'Paiement rapide et sécurisé par carte de crédit.', true],
                ['ALMA_EXCLUDED_CATEGORIES_MESSAGE_1', 'Your cart is not eligible for payments with Alma.', true],
                ['ALMA_EXCLUDED_CATEGORIES_MESSAGE_2', 'Votre panier n\'est pas éligible aux paiements avec Alma.', true],
            ]);
        $this->migrationService->languageKeyMigration();
    }

    public function tearDown(): void
    {
        unset(
            $this->migrationService,
            $this->configurationRepository,
            $this->feePlansProvider,
            $this->feePlansService,
            $this->paymentButtonService,
            $this->languageRepository
        );
    }
}
