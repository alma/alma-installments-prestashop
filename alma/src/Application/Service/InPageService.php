<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\InPageAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;

class InPageService
{
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configurationRepository;

    public function __construct(
        ConfigurationRepository $configurationRepository
    ) {
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * Get the default values to save for the in page form, if the merchant id is not set, otherwise return an empty array.
     * @return array
     */
    public function defaultFieldsToSave(): array
    {
        if (!empty($this->configurationRepository->get(ApiAdminForm::KEY_FIELD_MERCHANT_ID))) {
            return [];
        }

        return [
            InPageAdminForm::KEY_FIELD_INPAGE_STATE => 1,
            InPageAdminForm::KEY_FIELD_INPAGE_PAYMENT_BUTTON_SELECTOR => '[data-module-name=alma]',
            InPageAdminForm::KEY_FIELD_INPAGE_PLACE_ORDER_BUTTON_SELECTOR => '#payment-confirmation button',
        ];
    }
}
