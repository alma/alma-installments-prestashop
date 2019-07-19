<?php
/**
 * 2018-2019 Alma SAS
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
 * @copyright 2018-2019 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaLogger.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/functions.php';

class PaymentData
{
    public static function dataFromCart($cart, $context, $installmentsCount = 3)
    {
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0) {
            AlmaLogger::instance()->warning(
                "[Alma] Missing Customer ID or Delivery/Billing address ID for Cart {$cart->id}"
            );
        }

        $customer = null;
        if ($cart->id_customer) {
            $customer = new Customer($cart->id_customer);
            if (!Validate::isLoadedObject($customer)) {
                AlmaLogger::instance()->error(
                    "[Alma] Error loading Customer {$cart->id_customer} from Cart {$cart->id}"
                );

                return null;
            }
        }

        if (!$customer) {
            $customer = $context->customer;
        }

        $customerData = array(
            'first_name' => $customer->firstname,
            'last_name' => $customer->lastname,
            'email' => $customer->email,
            'birth_date' => $customer->birthday,
            'addresses' => array(),
            'phone' => null,
        );

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
            array_push($customerData['addresses'], array(
                'line1' => $address['address1'],
                'postal_code' => $address['postcode'],
                'city' => $address['city'],
                'country' => $address['country'],
            ));

            if (is_null($customerData['phone']) && $address['phone']) {
                $customerData['phone'] = $address['phone'];
            } elseif (is_null($customerData['phone']) && $address['phone_mobile']) {
                $customerData['phone'] = $address['phone_mobile'];
            }
        }

        $purchaseAmount = (float) $cart->getOrderTotal(true, Cart::BOTH);

        $orderData = array();
        $products = $cart->getProducts();
        $carrier = new Carrier($cart->id_carrier);
        if ($products) {
            $orderData['line_items'] = array();
            foreach ($products as $productLine) {
                $product = new Product($productLine['id_product'], $productLine['id_product_attribute']);
                $orderData['line_items'][] = array(
                    'title' => $productLine['name'],
                    'category' => $productLine['category'],
                    'unit_price' => almaPriceToCents($productLine['price']),
                    'quantity' => $productLine['cart_quantity'],
                    'url' => $context->link->getProductLink($product),
                    'picture_url' => $context->link->getImageLink($productLine['link_rewrite'], $productLine['id_image']),
                );
            }
        }
        $orderData['shipping'] = array(
            'title' => $carrier->name,
            'price' => almaPriceToCents($cart->getOrderTotal(true, Cart::ONLY_SHIPPING)),
        );

        $data = array(
            'payment' => array(
                'installments_count' => (int) $installmentsCount,
                'customer_cancel_url' => $context->link->getPageLink('order'),
                'return_url' => $context->link->getModuleLink('alma', 'validation'),
                'ipn_callback_url' => $context->link->getModuleLink('alma', 'ipn'),
                'purchase_amount' => almaPriceToCents($purchaseAmount),
                'shipping_address' => array(
                    'line1' => $shippingAddress->address1,
                    'postal_code' => $shippingAddress->postcode,
                    'city' => $shippingAddress->city,
                    'country' => $shippingAddress->country,
                ),
                'billing_address' => array(
                    'line1' => $billingAddress->address1,
                    'postal_code' => $billingAddress->postcode,
                    'city' => $billingAddress->city,
                    'country' => $billingAddress->country,
                ),
                'custom_data' => array(
                    'cart_id' => $cart->id,
                ),
            ),
            'customer' => $customerData,
            'order' => array(
                'data' => $orderData,
            ),
        );

        return $data;
    }
}
