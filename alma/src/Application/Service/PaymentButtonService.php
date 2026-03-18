<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\PaymentButtonAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;

class PaymentButtonService
{
    private \Context $context;
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configurationRepository;

    public function __construct(
        \Context $context,
        ConfigurationRepository $configurationRepository
    ) {
        $this->context = $context;
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * @return string
     */
    public function createTemplate(): string
    {
        $tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . 'alma/views/templates/admin/payment_button.tpl');

        return $tpl->fetch();
    }

    public function defaultFieldsToSave(): array
    {
        if (!empty($this->configurationRepository->get(ApiAdminForm::KEY_FIELD_MERCHANT_ID))) {
            return [];
        }

        return [
            PaymentButtonAdminForm::KEY_FIELD_PAYNOW_BUTTON_TITLE => 'Pay now by credit card',
            PaymentButtonAdminForm::KEY_FIELD_PAYNOW_BUTTON_DESC => 'Fast and secure payments.',
            PaymentButtonAdminForm::KEY_FIELD_PNX_BUTTON_TITLE => 'Pay in %d installments',
            PaymentButtonAdminForm::KEY_FIELD_PNX_BUTTON_DESC => 'Fast and secure payment by credit card.',
            PaymentButtonAdminForm::KEY_FIELD_PAYLATER_BUTTON_TITLE => 'Buy now Pay in %d days',
            PaymentButtonAdminForm::KEY_FIELD_PAYLATER_BUTTON_DESC => 'Fast and secure payment by credit card.',
            PaymentButtonAdminForm::KEY_FIELD_CREDIT_BUTTON_TITLE => 'Pay in %d installments',
            PaymentButtonAdminForm::KEY_FIELD_CREDIT_BUTTON_DESC => 'Fast and secure payment by credit card.',
        ];
    }
}
