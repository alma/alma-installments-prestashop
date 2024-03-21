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
use Alma\PrestaShop\Helpers\CarrierHelper;
use Alma\PrestaShop\Helpers\CartHelper;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\ProductHelper;
use Alma\PrestaShop\Helpers\SettingsCustomFieldsHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ShopHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Repositories\ProductRepository;

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
     * @var CartData
     */
    protected $cartData;

    /**
     * @var ShippingData
     */
    protected $shippingData;

    public function __construct()
    {
        $this->toolsHelper = new ToolsHelper();
        $this->settingsHelper = new SettingsHelper(new ShopHelper(), new ConfigurationHelper());
        $this->priceHelper = new PriceHelper();
        $this->cartData = new CartData();
        $this->shippingData = new ShippingData();
    }

    /**
     * @param \Cart $cart
     * @param \Context $context
     * @param array $feePlans
     * @param bool $forPayment
     *
     * @return array|null
     *
     * @throws ParamsError
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function dataFromCart($cart, $context, $feePlans, $forPayment = false)
    {
        if (
            $forPayment
            && (
                0 == $cart->id_customer
                || 0 == $cart->id_address_delivery
                || 0 == $cart->id_address_invoice
            )
        ) {
            Logger::instance()->warning("[Alma] Missing Customer ID or Delivery/Billing address ID for Cart {$cart->id}");
        }

        $customer = null;
        if ($cart->id_customer) {
            $customer = new \Customer($cart->id_customer);
            if (!\Validate::isLoadedObject($customer)) {
                Logger::instance()->error("[Alma] Error loading Customer {$cart->id_customer} from Cart {$cart->id}");

                return null;
            }
        }

        $shippingAddress = new \Address((int) $cart->id_address_delivery);
        $billingAddress = new \Address((int) $cart->id_address_invoice);
        $countryShippingAddress = \Country::getIsoById((int) $shippingAddress->id_country);
        $countryBillingAddress = \Country::getIsoById((int) $billingAddress->id_country);
        $countryShippingAddress = ($countryShippingAddress) ? $countryShippingAddress : '';
        $countryBillingAddress = ($countryBillingAddress) ? $countryBillingAddress : '';
        $locale = $context->language->iso_code;
        if (property_exists($context->language, 'locale')) {
            $locale = $context->language->locale;
        }
        $purchaseAmount = (float) $this->toolsHelper->psRound((float) $cart->getOrderTotal(true, \Cart::BOTH), 2);

        /* Eligibility Endpoint V2 */
        if (!$forPayment) {
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

        if (!$customer) {
            $customer = $context->customer;
        }

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
        } else {
            if ($shippingAddress->phone_mobile) {
                $customerData['phone'] = $shippingAddress->phone_mobile;
            }
        }

        if (version_compare(_PS_VERSION_, '1.5.4.0', '<')) {
            $addresses = $customer->getAddresses($context->language->id);
        } else {
            $addresses = $customer->getAddresses($customer->id_lang);
        }
        foreach ($addresses as $address) {
            $customerData['addresses'][] = [
                'line1' => $address['address1'],
                'postal_code' => $address['postcode'],
                'city' => $address['city'],
                'country' => \Country::getIsoById((int) $address['id_country']),
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

        $idStateShipping = $shippingAddress->id_state;
        $idStateBilling = $billingAddress->id_state;
        $customerData['state_province'] = \State::getNameById((int) $idStateBilling);
        $customerData['country'] = \Country::getIsoById((int) $billingAddress->id_country);

        if ($billingAddress->company) {
            $customerData['is_business'] = true;
            $customerData['business_name'] = $billingAddress->company;
        }

        $dataPayment = [
            'website_customer_details' => $this->buildWebsiteCustomerDetails($context, $customer, $cart, $purchaseAmount),
            'payment' => [
                'installments_count' => $feePlans['installmentsCount'],
                'deferred_days' => $feePlans['deferredDays'],
                'deferred_months' => $feePlans['deferredMonths'],
                'purchase_amount' => $this->priceHelper->convertPriceToCents($purchaseAmount),
                'customer_cancel_url' => $context->link->getPageLink('order&step=3'),
                'return_url' => $context->link->getModuleLink('alma', 'validation'),
                'ipn_callback_url' => $context->link->getModuleLink('alma', 'ipn'),
                'shipping_address' => [
                    'line1' => $shippingAddress->address1,
                    'postal_code' => $shippingAddress->postcode,
                    'city' => $shippingAddress->city,
                    'country' => $countryShippingAddress,
                    'county_sublocality' => null,
                    'state_province' => $idStateShipping > 0 ? \State::getNameById((int) $idStateShipping) : '',
                ],
                'shipping_info' => $this->shippingData->shippingInfo($cart),
                'billing_address' => [
                    'line1' => $billingAddress->address1,
                    'postal_code' => $billingAddress->postcode,
                    'city' => $billingAddress->city,
                    'country' => $countryBillingAddress,
                    'county_sublocality' => null,
                    'state_province' => $idStateBilling > 0 ? $customerData['state_province'] : '',
                ],
                'custom_data' => [
                    'cart_id' => $cart->id,
                    'purchase_amount_new_conversion_func' => $this->priceHelper->convertPriceToCentsStr($purchaseAmount),
                    'cart_totals' => $purchaseAmount,
                    'cart_totals_high_precision' => number_format($purchaseAmount, 16),
                    'poc' => [
                        'data-for-risk',
                    ],
                ],
                'locale' => $locale,
            ],
            'customer' => $customerData,
        ];

        if ($this->settingsHelper->isDeferredTriggerLimitDays($feePlans)) {
            $dataPayment['payment']['deferred'] = 'trigger';
            $dataPayment['payment']['deferred_description'] = SettingsCustomFieldsHelper::getDescriptionPaymentTriggerByLang($context->language->id);
        }

        if ($feePlans['installmentsCount'] > 4) {
            $dataPayment['payment']['cart'] = $this->cartInfo($cart);
        }

        if (static::isInPage($dataPayment)) {
            $dataPayment['payment']['origin'] = 'online_in_page';
        }

        PaymentValidator::checkPurchaseAmount($dataPayment);

        return $dataPayment;
    }

    /**
     * @param $paymentData
     *
     * @return bool
     */
    public static function isPayLater($paymentData)
    {
        return $paymentData['payment']['deferred_days'] >= 1 || $paymentData['payment']['deferred_months'] >= 1;
    }

    /**
     * @param $paymentData
     *
     * @return bool
     */
    public static function isPnXOnly($paymentData)
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
    public static function isPayNow($paymentData)
    {
        return $paymentData['payment']['installments_count'] === 1 && (0 === $paymentData['payment']['deferred_days'] && 0 === $paymentData['payment']['deferred_months']);
    }

    /**
     * @param $dataPayment
     *
     * @return bool
     */
    public static function isInPage($dataPayment)
    {
        return (
            static::isPnXOnly($dataPayment)
            || static::isPayNow($dataPayment)
            || static::isPayLater($dataPayment))
            && SettingsHelper::isInPageEnabled();
    }

    private static function isNewCustomer($idCustomer)
    {
        if (\Order::getCustomerNbOrders($idCustomer) > 0) {
            return false;
        }

        return true;
    }

    private function buildWebsiteCustomerDetails($context, $customer, $cart, $purchaseAmount)
    {
        $carrierHelper = new CarrierHelper($context);
        $cartHelper = new CartHelper();
        $productHelper = new ProductHelper();
        $productRepository = new ProductRepository();

        return [
            'new_customer' => self::isNewCustomer($customer->id),
            'is_guest' => (bool) $customer->is_guest,
            'created' => strtotime($customer->date_add),
            'current_order' => [
                'purchase_amount' => $this->priceHelper->convertPriceToCents($purchaseAmount),
                'created' => strtotime($cart->date_add),
                'payment_method' => PaymentData::PAYMENT_METHOD,
                'shipping_method' => $carrierHelper->getParentCarrierNameById($cart->id_carrier),
                'items' => $this->cartData->getCartItems($cart, $productHelper, $productRepository),
            ],
            'previous_orders' => [
                $cartHelper->previousCartOrdered($customer->id),
            ],
        ];
    }
}
