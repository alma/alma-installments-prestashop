<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\PaymentButtonAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\LanguageRepository;

class PaymentButtonService
{
    private \Context $context;
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configurationRepository;
    /**
     * @var LanguageRepository
     */
    private LanguageRepository $languageRepository;

    // TODO : Provisional values, need to be get the transalation value from .xlf file when I18N rebased
    private const VALUE_PAYNOW_BUTTON_TITLE = [
        'en' => 'Pay now by credit card',
        'fr' => 'Payer maintenant par carte bancaire'
    ];
    private const VALUE_PAYNOW_BUTTON_DESC = [
        'en' => 'Fast and secure payments.',
        'fr' => 'Paiement rapide et sécurisé.'
    ];
    private const VALUE_PNX_BUTTON_TITLE = [
        'en' => 'Pay in %d installments',
        'fr' => 'Payer en %d fois'
    ];
    private const VALUE_PNX_BUTTON_DESC = [
        'en' => 'Fast and secure payment by credit card.',
        'fr' => 'Paiement rapide et sécurisé, par carte bancaire.'
    ];
    private const VALUE_PAYLATER_BUTTON_TITLE = [
        'en' => 'Buy now Pay in %d days',
        'fr' => 'Payer dans %d jours'
    ];
    private const VALUE_PAYLATER_BUTTON_DESC = [
        'en' => 'Fast and secure payment by credit card.',
        'fr' => 'Paiement rapide et sécurisé, par carte bancaire.'
    ];
    private const VALUE_CREDIT_BUTTON_TITLE = [
        'en' => 'Pay in %d installments',
        'fr' => 'Payer en %d fois'
    ];
    private const VALUE_CREDIT_BUTTON_DESC = [
        'en' => 'Fast and secure payment by credit card.',
        'fr' => 'Paiement rapide et sécurisé, par carte bancaire.'
    ];

    public function __construct(
        \Context $context,
        ConfigurationRepository $configurationRepository,
        LanguageRepository $languageRepository
    ) {
        $this->context = $context;
        $this->configurationRepository = $configurationRepository;
        $this->languageRepository = $languageRepository;
    }

    /**
     * @return string
     */
    public function createTemplate(): string
    {
        $tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . 'alma/views/templates/admin/payment_button.tpl');

        return $tpl->fetch();
    }

    /**
     * TODO : Need to get the default value from .xlf file when I18N rebased, and remove the provisional const values
     * @return array
     */
    public function defaultFieldsToSave(): array
    {
        if (!empty($this->configurationRepository->get(ApiAdminForm::KEY_FIELD_MERCHANT_ID))) {
            return [];
        }

        $fields = [];

        foreach ($this->languageRepository->getActiveLanguages() as $language) {
            $suffixLanguage = '_' . $language['id_lang'];
            $fields[PaymentButtonAdminForm::KEY_FIELD_PAYNOW_BUTTON_TITLE . $suffixLanguage] = self::VALUE_PAYNOW_BUTTON_TITLE[$language['iso_code']];
            $fields[PaymentButtonAdminForm::KEY_FIELD_PAYNOW_BUTTON_DESC . $suffixLanguage] = self::VALUE_PAYNOW_BUTTON_DESC[$language['iso_code']];
            $fields[PaymentButtonAdminForm::KEY_FIELD_PNX_BUTTON_TITLE . $suffixLanguage] = self::VALUE_PNX_BUTTON_TITLE[$language['iso_code']];
            $fields[PaymentButtonAdminForm::KEY_FIELD_PNX_BUTTON_DESC . $suffixLanguage] = self::VALUE_PNX_BUTTON_DESC[$language['iso_code']];
            $fields[PaymentButtonAdminForm::KEY_FIELD_PAYLATER_BUTTON_TITLE . $suffixLanguage] = self::VALUE_PAYLATER_BUTTON_TITLE[$language['iso_code']];
            $fields[PaymentButtonAdminForm::KEY_FIELD_PAYLATER_BUTTON_DESC . $suffixLanguage] = self::VALUE_PAYLATER_BUTTON_DESC[$language['iso_code']];
            $fields[PaymentButtonAdminForm::KEY_FIELD_CREDIT_BUTTON_TITLE . $suffixLanguage] = self::VALUE_CREDIT_BUTTON_TITLE[$language['iso_code']];
            $fields[PaymentButtonAdminForm::KEY_FIELD_CREDIT_BUTTON_DESC . $suffixLanguage] = self::VALUE_CREDIT_BUTTON_DESC[$language['iso_code']];
        }

        return $fields;
    }
}
