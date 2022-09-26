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

use Cart;
use Context;
use Customer;
use Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class CustomerData
 */
class CustomerData
{
    /** @var string Phone of customer */
    private $phone = null;

    /**
     * Customer Data construct
     *
     * @param Context $context
     * @param Customer $customer
     */
    public function __construct(
        Context $context,
        Customer $customer
    ) {
        $this->context = $context;
        $this->customer = $customer;
    }

    /**
     * Get addresses
     *
     * @return array getAddresses
     */
    public function getAddresses()
    {
        if (version_compare(_PS_VERSION_, '1.5.4.0', '<')) {
            $idLang = $this->context->language->id;
        } else {
            $idLang = $this->customer->id_lang;
        }

        return $this->customer->getAddresses($idLang);
    }

    /**
     * Get birthday date
     *
     * @return string|null
     */
    public function getBirthday()
    {
        $birthday = $this->customer->birthday;
        if ($birthday == '0000-00-00') {
            return null;
        }

        return $birthday;
    }

    /**
     * If is new customer or not
     *
     * @param int $idCustomer
     *
     * @return bool
     */
    public function isNew($idCustomer)
    {
        return Order::getCustomerNbOrders($idCustomer) === 0;
    }

    /**
     * Get customer's phone number
     *
     * @return null|string
     */
    public function getPhone()
    {
        if (is_null($this->phone)) {
            foreach ($this->getAddresses() as $address) {
                if ($address['phone']) {
                    $this->phone = $address['phone'];

                    return $this->phone;
                } elseif ($address['phone_mobile']) {
                    $this->phone = $address['phone_mobile'];

                    return $this->phone;
                }
            }
        }

        return $this->phone;
    }

    /**
     * Get ids cart ordered by customer id with limit (default = 10)
     *
     * @param int $idCustomer
     * @param int $limit Limits the list of carts returned.
     *
     * @return array
     */
    public function getCarts($idCustomer, $limit = 10)
    {
        $carts = [];
        $orders = Order::getCustomerOrders($idCustomer);
        $i = 0;

        foreach ($orders as $order) {
            $carts[] = new Cart($order['id_cart']);

            $i++;
            if ($i == $limit) { break; }
        }

        return $carts;
    }
}
