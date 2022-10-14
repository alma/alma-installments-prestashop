<?php

/**
 * 2018-2022 Alma SAS
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
 * @copyright 2018-2022 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Model;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Address;
use Alma\PrestaShop\Utils\Logger;
use Alma\PrestaShop\Utils\Settings;
use Alma\PrestaShop\Utils\SettingsCustomFields;
use Cart;
use Context;
use Country;
use Customer;
use Exception;
use Order;
use State;
use Tools;
use Validate;

class PaymentData
{
    const PAYMENT_METHOD = 'alma';

    /**
     * @param Cart $cart
     * @param Context $context
     * @param array $feePlans
     *
     * @return array|null
     *
     * @throws Exception
     */
    public static function dataFromCart($cart, $context, $feePlans, $forPayment = false)
    {
        if ($forPayment && (
            $cart->id_customer == 0 ||
            $cart->id_address_delivery == 0 ||
            $cart->id_address_invoice == 0
        )) {
            Logger::instance()->warning(
                "[Alma] Missing Customer ID or Delivery/Billing address ID for Cart {$cart->id}"
            );
        }

        $customer = null;
        if ($cart->id_customer) {
            $customer = new Customer($cart->id_customer);
            if (!Validate::isLoadedObject($customer)) {
                Logger::instance()->error(
                    "[Alma] Error loading Customer {$cart->id_customer} from Cart {$cart->id}"
                );

                return null;
            }
        }

        $shippingAddress = new Address((int) $cart->id_address_delivery);
        $billingAddress = new Address((int) $cart->id_address_invoice);

        $countryShippingAddress = Country::getIsoById((int) $shippingAddress->id_country);
        $countryBillingAddress = Country::getIsoById((int) $billingAddress->id_country);
        $countryShippingAddress = ($countryShippingAddress) ? $countryShippingAddress : '';
        $countryBillingAddress = ($countryBillingAddress) ? $countryBillingAddress : '';

        $locale = $context->language->iso_code;
        if (property_exists($context->language, 'locale')) {
            $locale = $context->language->locale;
        }

        $purchaseAmount = (float) Tools::ps_round((float) $cart->getOrderTotal(true, Cart::BOTH), 2);

        /* Eligibility Endpoint V2 */
        if (!$forPayment) {
            $queries = [];
            foreach ($feePlans as $plan) {
                $queries[] = [
                    'purchase_amount' => almaPriceToCents($purchaseAmount),
                    'installments_count' => $plan['installmentsCount'],
                    'deferred_days' => $plan['deferredDays'],
                    'deferred_months' => $plan['deferredMonths'],
                ];
            }

            return [
                'purchase_amount' => almaPriceToCents($purchaseAmount),
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

        if ($customerData['birth_date'] == '0000-00-00') {
            $customerData['birth_date'] = null;
        }

        if ($shippingAddress->phone) {
            $customerData['phone'] = $shippingAddress->phone;
        } elseif ($shippingAddress->phone_mobile) {
            $customerData['phone'] = $shippingAddress->phone_mobile;
        }

        if (version_compare(_PS_VERSION_, '1.5.4.0', '<')) {
            $addresses = $customer->getAddresses($context->language->id);
        } else {
            $addresses = $customer->getAddresses($customer->id_lang);
        }
        foreach ($addresses as $address) {
            array_push($customerData['addresses'], [
                'line1' => $address['address1'],
                'postal_code' => $address['postcode'],
                'city' => $address['city'],
                'country' => Country::getIsoById((int) $address['id_country']),
                'county_sublocality' => null,
                'state_province' => $address['state'],
            ]);

            if (is_null($customerData['phone']) && $address['phone']) {
                $customerData['phone'] = $address['phone'];
            } elseif (is_null($customerData['phone']) && $address['phone_mobile']) {
                $customerData['phone'] = $address['phone_mobile'];
            }
        }

        $idStateShipping = $shippingAddress->id_state;
        $idStateBilling = $billingAddress->id_state;
        $customerData['state_province'] = State::getNameById((int) $idStateBilling);
        $customerData['country'] = Country::getIsoById((int) $billingAddress->id_country);

        if ($billingAddress->company) {
            $customerData['is_business'] = true;
            $customerData['business_name'] = $billingAddress->company;
        }

        $dataPayment = [
            'website_customer_details' => self::buildWebsiteCustomerDetails($context, $customer, $cart, $purchaseAmount),
            'payment' => [
                'installments_count' => $feePlans['installmentsCount'],
                'deferred_days' => $feePlans['deferredDays'],
                'deferred_months' => $feePlans['deferredMonths'],
                'purchase_amount' => almaPriceToCents($purchaseAmount),
                'customer_cancel_url' => $context->link->getPageLink('order'),
                'return_url' => $context->link->getModuleLink('alma', 'validation'),
                'ipn_callback_url' => $context->link->getModuleLink('alma', 'ipn'),
                'shipping_address' => [
                    'line1' => $shippingAddress->address1,
                    'postal_code' => $shippingAddress->postcode,
                    'city' => $shippingAddress->city,
                    'country' => $countryShippingAddress,
                    'county_sublocality' => null,
                    'state_province' => $idStateShipping > 0 ? State::getNameById((int) $idStateShipping) : '',
                ],
                'shipping_info' => ShippingData::shippingInfo($cart),
                'cart' => CartData::cartInfo($cart),
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
                    'purchase_amount_new_conversion_func' => almaPriceToCents_str($purchaseAmount),
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

        if (Settings::isDeferredTriggerLimitDays($feePlans)) {
            $dataPayment['payment']['deferred'] = 'trigger';
            $dataPayment['payment']['deferred_description'] = SettingsCustomFields::getDescriptionPaymentTriggerByLang($context->language->id);
        }

        return $dataPayment;
    }

    private static function isNewCustomer($idCustomer)
    {
        if (Order::getCustomerNbOrders($idCustomer) > 0) {
            return false;
        }

        return true;
    }

    private static function buildWebsiteCustomerDetails($context, $customer, $cart, $purchaseAmount)
    {
        $carrierHelper = new CarrierHelper($context);
        $cartHelper = new CartHelper($context);

        return [
            'new_customer' => self::isNewCustomer($customer->id),
            'is_guest' => (bool) $customer->is_guest,
            'created' => strtotime($customer->date_add),
            'current_order' => [
                'purchase_amount' => almaPriceToCents($purchaseAmount),
                'payment_method' => self::PAYMENT_METHOD,
                'shipping_method' => $carrierHelper->getNameCarrierById($cart->id_carrier),
                'items' => CartData::getCartItems($cart),
            ],
            'previous_orders' => [
                $cartHelper->previousCartOrdered($customer->id),
            ],
        ];
    }
}
