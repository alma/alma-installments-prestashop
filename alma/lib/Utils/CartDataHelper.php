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

namespace Alma\PrestaShop\Utils;

use Address;
use Alma\PrestaShop\Model\CartData;
use Alma\PrestaShop\Model\ShippingData;
use Cart;
use Context;
use Country;
use Customer;
use State;
use Tools;
use Validate;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class CartDataHelper.
 *
 * Cart Data
 */
class CartDataHelper
{
    public function __construct(
        Cart $cart,
        Context $context,
        array $feePlans
    )
    {
        $this->cart = $cart;
        $this->context = $context;
        $this->feePlans = $feePlans;
        $this->customer = $this->getCustomer();
    }

    public function eligibilityData()
    {
        return [
            'purchase_amount' => almaPriceToCents($this->getPurchaseAmount()),
            'queries' => $this->getQueries(),
            'shipping_address' => [
                'country' => $this->getCountryAddressByType('shipping'),
            ],
            'billing_address' => [
                'country' => $this->getCountryAddressByType('billing'),
            ],
            'locale' => $this->getLocale(),
        ];
    }

    public function paymentData()
    {
        return [
            'payment' => [
                'installments_count' => $this->feePlans['installmentsCount'],
                'deferred_days' => $this->feePlans['deferredDays'],
                'deferred_months' => $this->feePlans['deferredMonths'],
                'purchase_amount' => almaPriceToCents($this->getPurchaseAmount()),
                'customer_cancel_url' => $this->context->link->getPageLink('order'),
                'return_url' => $this->context->link->getModuleLink('alma', 'validation'),
                'ipn_callback_url' => $this->context->link->getModuleLink('alma', 'ipn'),
                'shipping_address' => [
                    'line1' => $this->getCartAddressByType('shipping')->address1,
                    'postal_code' => $this->getCartAddressByType('shipping')->postcode,
                    'city' => $this->getCartAddressByType('shipping')->city,
                    'country' => $this->getCountryAddressByType('shipping'),
                    'county_sublocality' => null,
                    'state_province' => $this->getStateProvince('shipping'),
                ],
                'shipping_info' => $this->getShippingData(),
                'cart' => $this->getCartInfo(),
                'billing_address' => [
                    'line1' => $this->getCartAddressByType('billing')->address1,
                    'postal_code' => $this->getCartAddressByType('billing')->postcode,
                    'city' => $this->getCartAddressByType('billing')->city,
                    'country' => $this->getCountryAddressByType('billing'),
                    'county_sublocality' => null,
                    'state_province' => $this->getStateProvince('billing'),
                ],
                'custom_data' => [
                    'cart_id' => $this->cart->id,
                    'purchase_amount_new_conversion_func' => almaPriceToCents_str($this->getPurchaseAmount()),
                    'cart_totals' => $this->getPurchaseAmount(),
                    'cart_totals_high_precision' => number_format($this->getPurchaseAmount(), 16),
                ],
                'locale' => $this->getLocale(),
            ],
            'customer' => $this->getCustomerData(),
        ];
    }

    private function getCustomerData()
    {
        return [
            'first_name' => $this->customer->firstname,
            'last_name' => $this->customer->lastname,
            'email' => $this->customer->email,
            'birth_date' => $this->getBirthday(),
            'addresses' => $this->getAddressesData(),
            'phone' => $this->getPhone(),
            'country' => $this->getCountryAddressByType('billing'),
            'county_sublocality' => null,
            'state_province' => $this->getStateProvince('billing'),
        ];
    }

    private function getBirthday()
    {
        $birthday = $this->customer->birthday;
        if ($birthday == '0000-00-00') {
            return null;
        }

        return $birthday;
    }

    private function getCustomerAddresses()
    {
        if (version_compare(_PS_VERSION_, '1.5.4.0', '<')) {
            $idLang = $this->context->language->id;
        } else {
            $idLang = $this->customer->id_lang;
        }
        return $this->customer->getAddresses($idLang);
    }

    private function getAddressesData()
    {
        $addresses = [];
        
        foreach ($this->getCustomerAddresses() as $address) {
            array_push($addresses, [
                'line1' => $address['address1'],
                'postal_code' => $address['postcode'],
                'city' => $address['city'],
                'country' => Country::getIsoById((int) $address['id_country']),
                'county_sublocality' => null,
                'state_province' => $address['state'],
            ]);
        }

        return $addresses;
    }

    private function getPhone()
    {
        $phone = null;
        $shippingAddress = $this->getCartAddressByType('shipping');

        if ($shippingAddress->phone) {
            $phone = $shippingAddress->phone;
        } elseif ($shippingAddress->phone_mobile) {
            $phone = $shippingAddress->phone_mobile;
        }

        if (is_null($phone)) {
            foreach ($this->getCustomerAddresses() as $address) {
                if ($address['phone']) {
                    $phone = $address['phone'];
                } elseif ($address['phone_mobile']) {
                    $phone = $address['phone_mobile'];
                }
            }
        }

        return $phone;
    }

    private function getCustomer()
    {
        if ($this->cart->id_customer) {
            $customer = new Customer($this->cart->id_customer);
            if (!Validate::isLoadedObject($customer)) {
                Logger::instance()->error(
                    "[Alma] Error loading Customer {$this->cart->id_customer} from Cart {$this->cart->id}"
                );

                return null;
            }

            return $customer;
        }

        return $this->context->customer;
    }

    private function getCartInfo()
    {
        return CartData::cartInfo($this->cart);
    }

    private function getShippingData()
    {
        return ShippingData::shippingInfo($this->cart);
    }

    private function getStateProvince($type)
    {
        $idStateShipping = $this->getCartAddressByType($type)->id_state;

        return $idStateShipping > 0 ? State::getNameById((int) $idStateShipping) : null;
    }
    private function getLocale()
    {
        $locale = $this->context->language->iso_code;

        if (property_exists($this->context->language, 'locale')) {
            $locale = $this->context->language->locale;
        }

        return $locale;
    }

    private function getCartAddressByType($type)
    {
        if ($type == 'shipping') {
            $address = new Address((int) $this->cart->id_address_delivery);
        } elseif ($type == 'billing') {
            $address = new Address((int) $this->cart->id_address_invoice);
        }

        return $address;
    }

    private function getCountryAddressByType($type)
    {
        $countryAddress = '';

        if ($type == 'shipping') {
            $countryAddress = Country::getIsoById((int) $this->getCartAddressByType('shipping')->id_country);
        } elseif ($type == 'billing') {
            $countryAddress = Country::getIsoById((int) $this->getCartAddressByType('billing')->id_country);
        }

        return $countryAddress;
    }

    private function getQueries()
    {
        $queries = [];

        foreach ($this->feePlans as $plan) {
            $queries[] = [
                'purchase_amount' => almaPriceToCents($this->getPurchaseAmount()),
                'installments_count' => $plan['installmentsCount'],
                'deferred_days' => $plan['deferredDays'],
                'deferred_months' => $plan['deferredMonths'],
            ];
        }

        return $queries;
    }

    private function getPurchaseAmount()
    {
        $purchaseAmount = (float) Tools::ps_round((float) $this->cart->getOrderTotal(true, Cart::BOTH), 2);

        return $purchaseAmount;
    } 
}