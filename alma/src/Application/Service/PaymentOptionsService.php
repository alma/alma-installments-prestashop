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
    /**
     * @var ExcludedCategoriesService
     */
    private ExcludedCategoriesService $excludedCategoriesService;
    /**
     * @var EligibilityService
     */
    private EligibilityService $eligibilityService;

    public function __construct(
        \Alma $module,
        PaymentOption $paymentOption,
        CurrencyValidator $currencyValidator,
        ExcludedCategoriesService $excludedCategoriesService,
        EligibilityService $eligibilityService
    ) {
        $this->module = $module;
        $this->paymentOption = $paymentOption;
        $this->currencyValidator = $currencyValidator;
        $this->excludedCategoriesService = $excludedCategoriesService;
        $this->eligibilityService = $eligibilityService;
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
        if ($this->excludedCategoriesService->isExcluded($this->cart->getProducts())) {
            return [];
        }

        $this->eligibilityService->getEligibilityForCheckout($this->cart);

        // Make loop from eligibility to create multiple payment options if needed in the future

        $this->paymentOption->setModuleName($this->module->name);
        $this->paymentOption->setLogo(_PS_MODULE_DIR_ . 'alma/views/img/logos/p3x_logo.svg');
        $this->paymentOption->setAction('alma/payment');
        $this->paymentOption->setCallToActionText($this->module->translate('Pay with Alma'));
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

    /**
     * Set Cart from parameter or from Context if not provided, to not inject in constructor.
     * @param \Cart $cart
     */
    public function setCart(\Cart $cart): void
    {
        $this->cart = $cart;
    }
}
