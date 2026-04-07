<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Application\Exception\CurrencyException;
use PrestaShop\Module\Alma\Application\Validator\CurrencyValidator;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use PrestaShopBundle\Translation\TranslatorInterface;

class PaymentOptionsService
{
    private \Module $module;
    /**
     * @var \PrestaShop\PrestaShop\Core\Payment\PaymentOption
     */
    private PaymentOption $paymentOption;
    /**
     * @var \PrestaShopBundle\Translation\TranslatorInterface
     */
    private TranslatorInterface $translator;
    private \Cart $cart;
    /**
     * @var CurrencyValidator
     */
    private CurrencyValidator $currencyValidator;

    public function __construct(
        \Module $module,
        PaymentOption $paymentOption,
        CurrencyValidator $currencyValidator,
        TranslatorInterface $translator,
        \Cart $cart
    ) {
        $this->module = $module;
        $this->paymentOption = $paymentOption;
        $this->currencyValidator = $currencyValidator;
        $this->translator = $translator;
        $this->cart = $cart;
    }

    /**
     * @return array
     */
    public function buildPaymentOptions(): array
    {
        try {
            $this->currencyValidator->checkCurrency($this->cart->id_currency);
        } catch (CurrencyException $e) {
            return [];
        }
        $this->paymentOption->setModuleName($this->module->name);
        $this->paymentOption->setLogo(_PS_MODULE_DIR_ . 'alma/views/img/logos/p3x_logo.svg');
        $this->paymentOption->setAction('alma/payment');
        $this->paymentOption->setCallToActionText($this->translator->trans('Pay with Alma'));
        $this->paymentOption->setInputs([
            'token' => [
                'name' => 'token',
                'type' => 'hidden',
                'value' => 'totoken',
            ],
        ]);

        return [
            $this->paymentOption
        ];
    }
}
