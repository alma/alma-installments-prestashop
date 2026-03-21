<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Application\Provider\FeePlansProvider;
use PrestaShop\Module\Alma\Infrastructure\Form\CartWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FeePlansAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ProductWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;

class MigrationService
{
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configurationRepository;
    /**
     * @var FeePlansProvider
     */
    private FeePlansProvider $feePlansProvider;
    /**
     * @var FeePlansService
     */
    private FeePlansService $feePlansService;

    public function __construct(
        FeePlansProvider $feePlansProvider,
        FeePlansService $feePlansService,
        ConfigurationRepository $configurationRepository
    ) {
        $this->feePlansProvider = $feePlansProvider;
        $this->feePlansService = $feePlansService;
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * Run all the migration methods
     */
    public function migrate(): void
    {
        $this->feePlanMigration();
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

        if (empty($newDataConfiguration)) {
            $feePlanList = $this->feePlansProvider->getFeePlanList();
            $fieldFeePlan = $this->feePlansService->fieldsToSaveFromApi($feePlanList);
            $newDataConfiguration = $fieldFeePlan[FeePlansAdminForm::KEY_FIELD_FEE_PLAN_LIST];
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
    }

    /**
     * Migrate the simple keys from the old configuration to the new one
     * These keys will just need to keep the same value but will need to be saved with the new key in the configuration
     */
    public function simpleKeyMigration(): void
    {
    }
}
