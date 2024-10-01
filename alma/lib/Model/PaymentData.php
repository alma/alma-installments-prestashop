<?php
/**
 * 2018-2023 Alma SAS.
 *
 * THE MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    Alma SAS <contact@getalma.eu>
 * @copyright 2018-2023 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Model;

use Alma\API\Lib\PaymentValidator;
use Alma\API\ParamsError;
use Alma\PrestaShop\Exceptions\AlmaException;
use Alma\PrestaShop\Factories\AddressFactory;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Helpers\AddressHelper;
use Alma\PrestaShop\Helpers\CarrierHelper;
use Alma\PrestaShop\Helpers\CartHelper;
use Alma\PrestaShop\Helpers\CountryHelper;
use Alma\PrestaShop\Helpers\CustomerHelper;
use Alma\PrestaShop\Helpers\CustomFieldsHelper;
use Alma\PrestaShop\Helpers\LocaleHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\StateHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use Alma\PrestaShop\Logger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentData
{
    const PAYMENT_METHOD = 'alma';

    /**
     * @var ToolsHelper
     */
    protected $toolsHelper;

    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var CustomFieldsHelper
     */
    protected $customFieldsHelper;

    /**
     * @var CartData
     */
    protected $cartData;

    /**
     * @var ShippingData
     */
    protected $shippingData;

    /**
     * @var AddressHelper
     */
    protected $addressHelper;

    /**
     * @var CountryHelper
     */
    protected $countryHelper;

    /**
     * @var LocaleHelper
     */
    protected $localeHelper;

    /**
     * @var StateHelper
     */
    protected $stateHelper;

    /**
     * @var CustomerHelper
     */
    protected $customerHelper;

    /**
     * @var CartHelper
     */
    protected $cartHelper;

    /**
     * @var CarrierHelper
     */
    protected $carrierHelper;

    /**
     * @var \Context
     */
    protected $context;

    /**
     * @var AddressFactory
     */
    protected $addressFactory;

    /**
     * @param ToolsHelper $toolsHelper
     * @param SettingsHelper $settingsHelper
     * @param PriceHelper $priceHelper
     * @param CustomFieldsHelper $customFieldsHelper
     * @param CartData $cartData
     * @param ShippingData $shippingData
     * @param ContextFactory $contextFactory
     * @param AddressHelper $addressHelper
     * @param CountryHelper $countryHelper
     * @param LocaleHelper $localeHelper
     * @param StateHelper $stateHelper
     * @param CustomerHelper $customerHelper
     * @param CartHelper $cartHelper
     * @param CarrierHelper $carrierHelper
     * @param AddressFactory $addressFactory
     */
    public function __construct(
        $toolsHelper,
        $settingsHelper,
        $priceHelper,
        $customFieldsHelper,
        $cartData,
        $shippingData,
        $contextFactory,
        $addressHelper,
        $countryHelper,
        $localeHelper,
        $stateHelper,
        $customerHelper,
        $cartHelper,
        $carrierHelper,
        $addressFactory
    ) {
        $this->toolsHelper = $toolsHelper;
        $this->settingsHelper = $settingsHelper;
        $this->priceHelper = $priceHelper;
        $this->customFieldsHelper = $customFieldsHelper;
        $this->cartData = $cartData;
        $this->shippingData = $shippingData;
        $this->context = $contextFactory->getContext();
        $this->addressHelper = $addressHelper;
        $this->countryHelper = $countryHelper;
        $this->localeHelper = $localeHelper;
        $this->stateHelper = $stateHelper;
        $this->customerHelper = $customerHelper;
        $this->cartHelper = $cartHelper;
        $this->carrierHelper = $carrierHelper;
        $this->addressFactory = $addressFactory;
    }

    /**
     * @param $feePlans
     * @param $forPayment
     *
     * @return array|null
     *
     * @throws ParamsError
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws AlmaException
     */
    public function dataFromCart($feePlans, $forPayment = false)
    {
        if (
            $forPayment
            && (
                0 == $this->context->cart->id_customer
                || 0 == $this->context->cart->id_address_delivery
                || 0 == $this->context->cart->id_address_invoice
            )
        ) {
            Logger::instance()->warning(
                sprintf(
                    '[Alma] Missing Customer ID or Delivery/Billing address ID for Cart %s',
                    $this->context->cart->id
                )
            );
        }

        $customer = $this->customerHelper->getCustomer();

        $shippingAddress = $this->addressFactory->create($this->context->cart->id_address_delivery);
        $billingAddress = $this->addressFactory->create((int) $this->context->cart->id_address_invoice);
        $countryShippingAddress = $this->countryHelper->getIsoById((int) $shippingAddress->id_country);
        $countryBillingAddress = $this->countryHelper->getIsoById((int) $billingAddress->id_country);
        $countryShippingAddress = ($countryShippingAddress) ? $countryShippingAddress : '';
        $countryBillingAddress = ($countryBillingAddress) ? $countryBillingAddress : '';

        $locale = $this->localeHelper->getLocaleFromContext($this->context);

        $purchaseAmount = (float) $this->toolsHelper->psRound(
            (float) $this->context->cart->getOrderTotal(true, \Cart::BOTH),
            2
        );

        /* Eligibility Endpoint V2 */
        if (!$forPayment) {
            return $this->getDataForEligibilityV2(
                $feePlans,
                $purchaseAmount,
                $countryShippingAddress,
                $countryBillingAddress,
                $locale
            );
        }

        $customerData = $this->buildCustomerData($customer, $shippingAddress, $billingAddress);

        $dataPayment = $this->buildDataPayment(
            $customer,
            $purchaseAmount,
            $feePlans,
            $shippingAddress,
            $countryShippingAddress,
            $locale,
            $billingAddress,
            $countryBillingAddress,
            $customerData
        );

        $this->checkPurchaseAmount($dataPayment);

        return $dataPayment;
    }

    /**
     * @param $customer
     * @param $purchaseAmount
     * @param $feePlans
     * @param $shippingAddress
     * @param $countryShippingAddress
     * @param $locale
     * @param $billingAddress
     * @param $countryBillingAddress
     * @param $customerData
     *
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws AlmaException
     */
    public function buildDataPayment(
        $customer,
        $purchaseAmount,
        $feePlans,
        $shippingAddress,
        $countryShippingAddress,
        $locale,
        $billingAddress,
        $countryBillingAddress,
        $customerData
    ) {
        $dataPayment = [
            'website_customer_details' => $this->buildWebsiteCustomerDetails($customer, $purchaseAmount),
            'payment' => [
                'installments_count' => $feePlans['installmentsCount'],
                'deferred_days' => $feePlans['deferredDays'],
                'deferred_months' => $feePlans['deferredMonths'],
                'purchase_amount' => $this->priceHelper->convertPriceToCents($purchaseAmount),
                'customer_cancel_url' => $this->context->link->getPageLink('order&step=3'),
                'return_url' => $this->context->link->getModuleLink('alma', 'validation'),
                'ipn_callback_url' => $this->context->link->getModuleLink('alma', 'ipn'),
                'shipping_address' => [
                    'line1' => $shippingAddress->address1,
                    'postal_code' => $shippingAddress->postcode,
                    'city' => $shippingAddress->city,
                    'country' => $countryShippingAddress,
                    'county_sublocality' => null,
                    'state_province' => $shippingAddress->id_state > 0 ? $this->stateHelper->getNameById(
                        (int) $shippingAddress->id_state) : '',
                ],
                'shipping_info' => $this->shippingData->shippingInfo($this->context->cart),
                'billing_address' => [
                    'line1' => $billingAddress->address1,
                    'postal_code' => $billingAddress->postcode,
                    'city' => $billingAddress->city,
                    'country' => $countryBillingAddress,
                    'county_sublocality' => null,
                    'state_province' => $billingAddress->id_state > 0 ? $customerData['state_province'] : '',
                ],
                'custom_data' => [
                    'cart_id' => $this->context->cart->id,
                    'purchase_amount_new_conversion_func' => $this->priceHelper->convertPriceToCentsStr(
                        $purchaseAmount
                    ),
                    'cart_totals' => $purchaseAmount,
                    'cart_totals_high_precision' => number_format($purchaseAmount, 16),
                    'poc' => [
                        'data-for-risk',
                    ],
                ],
                'locale' => $locale,
                'cart' => $this->cartData->cartInfo($this->context->cart),
            ],
            'customer' => $customerData,
        ];

        if ($this->settingsHelper->isDeferredTriggerLimitDays($feePlans)) {
            $dataPayment['payment']['deferred'] = 'trigger';
            $dataPayment['payment']['deferred_description'] = $this->customFieldsHelper->getDescriptionPaymentTriggerByLang($this->context->language->id);
        }

        if ($this->isInPage()) {
            $dataPayment['payment']['origin'] = 'online_in_page';
        }

        return $dataPayment;
    }

    /**
     * @param array $dataPayment
     *
     * @return void
     *
     * @throws ParamsError
     */
    public function checkPurchaseAmount($dataPayment)
    {
        PaymentValidator::checkPurchaseAmount($dataPayment);
    }

    /**
     * @param \Customer $customer
     * @param \Address $shippingAddress
     * @param \Address $billingAddress
     *
     * @return array
     */
    public function buildCustomerData($customer, $shippingAddress, $billingAddress)
    {
        $customerData = [
            'first_name' => $customer->firstname,
            'last_name' => $customer->lastname,
            'email' => $customer->email,
            'birth_date' => $customer->birthday,
            'addresses' => [],
            'phone' => null,
            'country' => null,
            'county_sublocality' => null,
            'state_province' => null,
        ];

        if ('0000-00-00' == $customerData['birth_date']) {
            $customerData['birth_date'] = null;
        }

        if ($shippingAddress->phone) {
            $customerData['phone'] = $shippingAddress->phone;
        } elseif ($shippingAddress->phone_mobile) {
            $customerData['phone'] = $shippingAddress->phone_mobile;
        }

        $addresses = $this->addressHelper->getAddressFromCustomer($customer);

        foreach ($addresses as $address) {
            $customerData['addresses'][] = [
                'line1' => $address['address1'],
                'postal_code' => $address['postcode'],
                'city' => $address['city'],
                'country' => $this->countryHelper->getIsoById((int) $address['id_country']),
                'county_sublocality' => null,
                'state_province' => $address['state'],
            ];

            if (is_null($customerData['phone']) && $address['phone']) {
                $customerData['phone'] = $address['phone'];
            } else {
                if (is_null($customerData['phone']) && $address['phone_mobile']) {
                    $customerData['phone'] = $address['phone_mobile'];
                }
            }
        }
        $customerData['state_province'] = $this->stateHelper->getNameById((int) $billingAddress->id_state);
        $customerData['country'] = $this->countryHelper->getIsoById((int) $billingAddress->id_country);

        if ($billingAddress->company) {
            $customerData['is_business'] = true;
            $customerData['business_name'] = $billingAddress->company;
        }

        return $customerData;
    }

    /**
     * @param string $purchaseAmount
     * @param string $countryShippingAddress
     * @param string $countryBillingAddress
     * @param string $locale
     *
     * @return array
     */
    public function getDataForEligibilityV2(
        $feePlans,
        $purchaseAmount,
        $countryShippingAddress,
        $countryBillingAddress,
        $locale
    ) {
        $queries = [];

        foreach ($feePlans as $plan) {
            $queries[] = [
                'purchase_amount' => $this->priceHelper->convertPriceToCents($purchaseAmount),
                'installments_count' => $plan['installmentsCount'],
                'deferred_days' => $plan['deferredDays'],
                'deferred_months' => $plan['deferredMonths'],
            ];
        }

        return [
            'purchase_amount' => $this->priceHelper->convertPriceToCents($purchaseAmount),
            'queries' => $queries,
            'shipping_address' => [
                'country' => $countryShippingAddress,
            ],
            'billing_address' => [
                'country' => $countryBillingAddress,
            ],
            'locale' => $locale,
        ];
    }

    /**
     * @param $paymentData
     *
     * @return bool
     */
    public function isPayLater($paymentData)
    {
        return $paymentData['payment']['deferred_days'] >= 1 || $paymentData['payment']['deferred_months'] >= 1;
    }

    /**
     * @param $paymentData
     *
     * @return bool
     */
    public function isPnXOnly($paymentData)
    {
        return $paymentData['payment']['installments_count'] > 1
            && $paymentData['payment']['installments_count'] <= 4
            && (0 === $paymentData['payment']['deferred_days'] && 0 === $paymentData['payment']['deferred_months']);
    }

    /**
     * @param $paymentData
     *
     * @return bool
     */
    public function isPayNow($paymentData)
    {
        return $paymentData['payment']['installments_count'] === 1
            && (
                0 === $paymentData['payment']['deferred_days']
                && 0 === $paymentData['payment']['deferred_months']
            );
    }

    /**
     * @param $dataPayment
     *
     * @return bool
     */
    public function isInPage()
    {
        return $this->settingsHelper->isInPageEnabled();
    }

    /**
     * @param \Customer $customer
     * @param $purchaseAmount
     *
     * @return array
     *
     * @throws AlmaException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function buildWebsiteCustomerDetails($customer, $purchaseAmount)
    {
        return [
            'new_customer' => $this->customerHelper->isNewCustomer($customer->id),
            'is_guest' => (bool) $customer->is_guest,
            'created' => strtotime($customer->date_add),
            'current_order' => [
                'purchase_amount' => $this->priceHelper->convertPriceToCents($purchaseAmount),
                'created' => strtotime($this->context->cart->date_add),
                'payment_method' => PaymentData::PAYMENT_METHOD,
                'shipping_method' => $this->carrierHelper->getParentCarrierNameById($this->context->cart->id_carrier),
                'items' => $this->cartData->getCartItems($this->context->cart),
            ],
            'previous_orders' => [
                $this->cartHelper->previousCartOrdered($customer->id),
            ],
        ];
    }
}
