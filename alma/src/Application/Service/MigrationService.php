<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Infrastructure\Form\CartWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\DebugAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ExcludedCategoriesAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FeePlansAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\InPageAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\PaymentButtonAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ProductWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\RefundAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\LanguageRepository;

class MigrationService
{
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configurationRepository;
    /**
     * @var PaymentButtonService
     */
    private PaymentButtonService $paymentButtonService;
    /**
     * @var LanguageRepository
     */
    private LanguageRepository $languageRepository;
    /**
     * @var ExcludedCategoriesService
     */
    private ExcludedCategoriesService $excludedCategoriesService;

    public function __construct(
        PaymentButtonService $paymentButtonService,
        ExcludedCategoriesService $excludedCategoriesService,
        ConfigurationRepository $configurationRepository,
        LanguageRepository $languageRepository
    ) {
        $this->paymentButtonService = $paymentButtonService;
        $this->excludedCategoriesService = $excludedCategoriesService;
        $this->configurationRepository = $configurationRepository;
        $this->languageRepository = $languageRepository;
    }

    /**
     * Run all the migration methods
     */
    public function migrate(): void
    {
        $this->feePlanMigration();
        $this->widgetMigration();
        $this->languageKeyMigration();
        $this->simpleKeyMigration();
    }

    /**
     * Migrate the fee plan configuration from the old format to the new one
     * If the old configuration is empty, it will get the fee plan list from the API and save it in the new format
     */
    public function feePlanMigration(): void
    {
        $newDataConfiguration = [];
        $oldDataConfiguration = $this->configurationRepository->get('ALMA_FEE_PLANS');
        $oldDataConfiguration = json_decode($oldDataConfiguration, true);

        foreach ($oldDataConfiguration as $key => $feePlan) {
            $newDataConfiguration[$key] = [
                'state' => (string) $feePlan['enabled'],
                'min_amount' => (string) $feePlan['min'],
                'max_amount' => (string) $feePlan['max'],
                'sort_order' => (string) $feePlan['order']
            ];
        }

        $newDataConfiguration = json_encode($newDataConfiguration);

        $this->configurationRepository->updateValue(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_LIST, $newDataConfiguration);
    }

    /**
     * Migrate the widget configuration from the old format to the new one
     */
    public function widgetMigration(): void
    {
        $oldWidgetKeysToMigrate = [
            'ALMA_SHOW_PRODUCT_ELIGIBILITY' => ProductWidgetAdminForm::KEY_FIELD_PRODUCT_WIDGET_STATE,
            'ALMA_PRODUCT_WDGT_NOT_ELGBL' => ProductWidgetAdminForm::KEY_FIELD_PRODUCT_WIDGET_DISPLAY_NOT_ELIGIBLE,
            'ALMA_SHOW_CART_ELIGIBILITY' => CartWidgetAdminForm::KEY_FIELD_CART_WIDGET_STATE,
            'ALMA_CART_WDGT_NOT_ELGBL' => CartWidgetAdminForm::KEY_FIELD_CART_WIDGET_DISPLAY_NOT_ELIGIBLE,
        ];
        $oldWidgetPositionKeysToRemove = [
            'ALMA_WIDGET_POSITION_CUSTOM',
            'ALMA_CART_WIDGET_POSITION_CUSTOM',
        ];
        $oldWidgetKeysToRemove = [
            'ALMA_PRODUCT_PRICE_SELECTOR',
            'ALMA_PRODUCT_ATTR_SELECTOR',
            'ALMA_PRODUCT_ATTR_RADIO_SELECTOR',
            'ALMA_PRODUCT_COLOR_PICK_SELECTOR',
            'ALMA_PRODUCT_QUANTITY_SELECTOR',
        ];

        foreach ($oldWidgetKeysToMigrate as $oldWidgetKey => $newWidgetKey) {
            $oldValue = $this->configurationRepository->get($oldWidgetKey);
            $this->configurationRepository->updateValue($newWidgetKey, $oldValue);
        }

        foreach ($oldWidgetPositionKeysToRemove as $oldWidgetPositionKey) {
            $oldPositionValue = $this->configurationRepository->get($oldWidgetPositionKey);
            if (!$oldPositionValue) {
                $this->configurationRepository->deleteByName($oldWidgetPositionKey);
                if ($oldWidgetPositionKey === 'ALMA_WIDGET_POSITION_CUSTOM') {
                    $this->configurationRepository->deleteByName('ALMA_WIDGET_POSITION_SELECTOR');
                } elseif ($oldWidgetPositionKey === 'ALMA_CART_WIDGET_POSITION_CUSTOM') {
                    $this->configurationRepository->deleteByName('ALMA_CART_WDGT_POS_SELECTOR');
                }
            }
        }

        foreach ($oldWidgetKeysToRemove as $oldWidgetKeyToRemove) {
            $this->configurationRepository->deleteByName($oldWidgetKeyToRemove);
        }
    }

    /**
     * Migrate the language keys from the old configuration to the new one
     * These keys will need to get the value with the language id and string to create the new key with
     * the language id and the string for the value
     */
    public function languageKeyMigration(): void
    {
        $languageKeys = [
            'ALMA_PAY_NOW_BUTTON_TITLE' => PaymentButtonAdminForm::KEY_FIELD_PAYNOW_BUTTON_TITLE . '_%d',
            'ALMA_PAY_NOW_BUTTON_DESC' => PaymentButtonAdminForm::KEY_FIELD_PAYNOW_BUTTON_DESC . '_%d',
            'ALMA_PNX_BUTTON_TITLE' => PaymentButtonAdminForm::KEY_FIELD_PNX_BUTTON_TITLE . '_%d',
            'ALMA_PNX_BUTTON_DESC' => PaymentButtonAdminForm::KEY_FIELD_PNX_BUTTON_DESC . '_%d',
            'ALMA_PNX_AIR_BUTTON_TITLE' => PaymentButtonAdminForm::KEY_FIELD_CREDIT_BUTTON_TITLE . '_%d',
            'ALMA_PNX_AIR_BUTTON_DESC' => PaymentButtonAdminForm::KEY_FIELD_CREDIT_BUTTON_DESC . '_%d',
            'ALMA_DEFERRED_BUTTON_TITLE' => PaymentButtonAdminForm::KEY_FIELD_PAYLATER_BUTTON_TITLE . '_%d',
            'ALMA_DEFERRED_BUTTON_DESC' => PaymentButtonAdminForm::KEY_FIELD_PAYLATER_BUTTON_DESC . '_%d',
            'ALMA_NOT_ELIGIBLE_CATEGORIES' => ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES_MESSAGE . '_%d',
        ];
        $defaultPaymentButtonKey = $this->paymentButtonService->defaultFieldsToSave();
        $defaultExcludedCategoriesKey = $this->excludedCategoriesService->defaultFieldsToSave();
        $languagesStore = $this->languageRepository->getActiveLanguages();
        foreach ($languageKeys as $oldLanguageKey => $newLanguageKey) {
            $languageValue = $this->configurationRepository->get($oldLanguageKey);
            $languageValue = json_decode($languageValue, true);
            if (is_array($languageValue)) {
                foreach ($languageValue as $idLang => $value) {
                    $newLanguageKeyFormated = sprintf($newLanguageKey, $idLang);
                    $this->configurationRepository->updateValue($newLanguageKeyFormated, $value['string']);
                }
            }
            if (!$languageValue) {
                foreach ($languagesStore as $value) {
                    $newLanguageKeyFormated = sprintf($newLanguageKey, $value['id_lang']);
                    if (str_contains($newLanguageKeyFormated, 'EXCLUDED_CATEGORIES_MESSAGE')) {
                        $defaultValue = $defaultExcludedCategoriesKey[$newLanguageKeyFormated];
                    } else {
                        $defaultValue = $defaultPaymentButtonKey[$newLanguageKeyFormated];
                    }
                    $this->configurationRepository->updateValue($newLanguageKeyFormated, $defaultValue);
                }
            }
            $this->configurationRepository->deleteByName($oldLanguageKey);
        }
    }

    /**
     * Migrate the simple keys from the old configuration to the new one
     * These keys will just need to keep the same value but will need to be saved with the new key in the configuration
     */
    public function simpleKeyMigration(): void
    {
        $simpleKeyToMigrate = [
            'ALMA_CATEGORIES_WDGT_NOT_ELGBL' => ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES_WIDGET_DISPLAY_NOT_ELIGIBLE,
            'ALMA_STATE_REFUND_ENABLED' => RefundAdminForm::KEY_FIELD_REFUND_ON_CHANGE_STATE,
            'ALMA_STATE_REFUND' => RefundAdminForm::KEY_FIELD_STATE_REFUND_SELECT,
            'ALMA_ACTIVATE_INPAGE' => InPageAdminForm::KEY_FIELD_INPAGE_STATE,
            'ALMA_ACTIVATE_LOGGING_ON' => DebugAdminForm::KEY_FIELD_DEBUG_STATE
        ];

        foreach ($simpleKeyToMigrate as $oldKey => $newKey) {
            $oldValue = $this->configurationRepository->get($oldKey);
            $defaultValue = '';
            if ($oldValue === '') {
                switch ($newKey) {
                    case InPageAdminForm::KEY_FIELD_INPAGE_STATE:
                    case ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES_WIDGET_DISPLAY_NOT_ELIGIBLE:
                        $defaultValue = '1';
                        break;
                    case DebugAdminForm::KEY_FIELD_DEBUG_STATE:
                    case RefundAdminForm::KEY_FIELD_REFUND_ON_CHANGE_STATE:
                        $defaultValue = '0';
                        break;
                    case RefundAdminForm::KEY_FIELD_STATE_REFUND_SELECT:
                        $defaultValue = '7';
                        break;
                }
                $oldValue = $defaultValue;
            }
            $this->configurationRepository->updateValue($newKey, $oldValue);
            $this->configurationRepository->deleteByName($oldKey);
        }
    }
}
