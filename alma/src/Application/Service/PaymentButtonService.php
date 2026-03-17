<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\PaymentButtonAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\LanguageRepository;
use PrestaShopBundle\Translation\TranslatorInterface;

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

    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    public function __construct(
        \Context $context,
        ConfigurationRepository $configurationRepository,
        LanguageRepository $languageRepository,
        TranslatorInterface $translator
    ) {
        $this->context = $context;
        $this->configurationRepository = $configurationRepository;
        $this->languageRepository = $languageRepository;
        $this->translator = $translator;
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
     * On the first save we set the default value of the payment button configurations.
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
            $fields[PaymentButtonAdminForm::KEY_FIELD_PAYNOW_BUTTON_TITLE . $suffixLanguage] = $this->translator->trans('Pay now by credit card', [], 'Modules.Alma.Settings', $language['locale']);
            $fields[PaymentButtonAdminForm::KEY_FIELD_PAYNOW_BUTTON_DESC . $suffixLanguage] = $this->translator->trans('Fast and secure payments.', [], 'Modules.Alma.Settings', $language['locale']);
            $fields[PaymentButtonAdminForm::KEY_FIELD_PNX_BUTTON_TITLE . $suffixLanguage] = $this->translator->trans('Pay in %d installments', [], 'Modules.Alma.Settings', $language['locale']);
            $fields[PaymentButtonAdminForm::KEY_FIELD_PNX_BUTTON_DESC . $suffixLanguage] = $this->translator->trans('Fast and secure payment by credit card.', [], 'Modules.Alma.Settings', $language['locale']);
            $fields[PaymentButtonAdminForm::KEY_FIELD_PAYLATER_BUTTON_TITLE . $suffixLanguage] = $this->translator->trans('Buy now Pay in %d days', [], 'Modules.Alma.Settings', $language['locale']);
            $fields[PaymentButtonAdminForm::KEY_FIELD_PAYLATER_BUTTON_DESC . $suffixLanguage] = $this->translator->trans('Fast and secure payment by credit card.', [], 'Modules.Alma.Settings', $language['locale']);
            $fields[PaymentButtonAdminForm::KEY_FIELD_CREDIT_BUTTON_TITLE . $suffixLanguage] = $this->translator->trans('Pay in %d installments', [], 'Modules.Alma.Settings', $language['locale']);
            $fields[PaymentButtonAdminForm::KEY_FIELD_CREDIT_BUTTON_DESC . $suffixLanguage] = $this->translator->trans('Fast and secure payment by credit card.', [], 'Modules.Alma.Settings', $language['locale']);
        }

        return $fields;
    }
}
