<?php
/**
 * 2018-2020 Alma SAS
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
 * @copyright 2018-2020 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Model;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Address;
use Alma\PrestaShop\Utils\Logger;
use Cart;
use Context;
use Customer;
use Exception;
use Tools;
use Validate;

class PaymentData
{
    /**
     * @param Cart $cart
     * @param Context $context
     * @param int|array $installmentsCount
     *
     * @return array|null
     *
     * @throws Exception
     */
    public static function dataFromCart($cart, $context, $installmentsCount = 3)
    {
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0) {
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
        ];

        if ($customerData['birth_date'] == '0000-00-00') {
            $customerData['birth_date'] = null;
        }

        $shippingAddress = new Address((int) $cart->id_address_delivery);
        $billingAddress = new Address((int) $cart->id_address_invoice);

        if ($shippingAddress->phone) {
            $customerData['phone'] = $shippingAddress->phone;
        } elseif ($shippingAddress->phone_mobile) {
            $customerData['phone'] = $shippingAddress->phone_mobile;
        }

        $addresses = $customer->getAddresses($customer->id_lang);
        foreach ($addresses as $address) {
            array_push($customerData['addresses'], [
                'line1' => $address['address1'],
                'postal_code' => $address['postcode'],
                'city' => $address['city'],
                'country' => $address['country'],
            ]);

            if (is_null($customerData['phone']) && $address['phone']) {
                $customerData['phone'] = $address['phone'];
            } elseif (is_null($customerData['phone']) && $address['phone_mobile']) {
                $customerData['phone'] = $address['phone_mobile'];
            }
        }

        $purchaseAmount = (float) Tools::ps_round((float) $cart->getOrderTotal(true, Cart::BOTH), 2);

        return [
            'payment' => [
                'installments_count' => $installmentsCount,
                'customer_cancel_url' => $context->link->getPageLink('order'),
                'return_url' => $context->link->getModuleLink('alma', 'validation'),
                'ipn_callback_url' => $context->link->getModuleLink('alma', 'ipn'),
                'purchase_amount' => almaPriceToCents($purchaseAmount),
                'shipping_address' => [
                    'line1' => $shippingAddress->address1,
                    'postal_code' => $shippingAddress->postcode,
                    'city' => $shippingAddress->city,
                    'country' => $shippingAddress->country,
                ],
                //'shipping_info' => ShippingData::shippingInfo($cart),
                'cart' => CartData::cartInfo($cart),
                'billing_address' => [
                    'line1' => $billingAddress->address1,
                    'postal_code' => $billingAddress->postcode,
                    'city' => $billingAddress->city,
                    'country' => $billingAddress->country,
                ],
                'custom_data' => [
                    'cart_id' => $cart->id,
                    'purchase_amount_new_conversion_func' => almaPriceToCents_str($purchaseAmount),
                    'cart_totals' => $purchaseAmount,
                    'cart_totals_high_precision' => number_format($purchaseAmount, 16),
                ],
            ],
            'customer' => $customerData,
        ];
    }
}
