<?php

namespace PrestaShop\Module\Alma\Application\Service;

use Alma;
use PrestaShop\Module\Alma\Application\Exception\CurrencyException;
use PrestaShop\Module\Alma\Application\Validator\CurrencyValidator;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class PaymentOptionsService
{
    private \Module $module;
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
        Alma $module,
        CurrencyValidator $currencyValidator,
        ExcludedCategoriesService $excludedCategoriesService,
        EligibilityService $eligibilityService
    ) {
        $this->module = $module;
        $this->currencyValidator = $currencyValidator;
        $this->excludedCategoriesService = $excludedCategoriesService;
        $this->eligibilityService = $eligibilityService;
    }

    /**
     * @return array
     */
    public function buildPaymentOptions(): array
    {
        $paymentOptions = [];
        try {
            $this->currencyValidator->checkCurrency($this->cart->id_currency);
        } catch (CurrencyException $e) {
            return [];
        }
        if ($this->excludedCategoriesService->isExcluded($this->cart->getProducts())) {
            return [];
        }

        $eligibilityList = $this->eligibilityService->getEligibilityForCheckout($this->cart);

        // Handle the payment option for each payment methods
        foreach ($eligibilityList as $eligibility) {
            $paymentOption = new PaymentOption();
            $paymentOption->setModuleName($this->module->name);
            $paymentOption->setLogo(_PS_MODULE_DIR_ . 'alma/views/img/logos/p3x_logo.svg');
            $paymentOption->setAction('alma/payment');
            $paymentOption->setCallToActionText($this->module->translate('Pay with Alma', [], 'Modules.Alma.Checkout'));
            $paymentOption->setInputs([
                'token' => [
                    'name' => 'token',
                    'type' => 'hidden',
                    'value' => 'totoken',
                ],
            ]);

            $paymentOptions[] = $paymentOption;
        }

        return $paymentOptions;
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
