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
use Order;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class CartHelper
 */
class CartHelper
{
    public function __construct(
        Context $context
    )
    {
        $this->context = $context;
    }

    /**
     * Previous cart items of the customer
     *
     * @param int $idCustomer
     * @return array
     */
    public function previousCartOrdered($idCustomer)
    {
        $ordersData = [];
        $orders = $this->getOrdersByCustomerWithLimit($idCustomer);
        
        $carrier = new CarrierHelper($this->context);
        foreach ($orders as $order) {
            $cart = new Cart((int) $order['id_cart']);
            $purchaseAmount = (float) Tools::ps_round((float) $cart->getOrderTotal(true, Cart::BOTH), 2);
            $ordersData[] = [
                'purchase_amount' => almaPriceToCents($purchaseAmount),
                'payment_method' => $order['payment'],
                'shipping_method' => $carrier->getNameCarrierById($cart->id_carrier),
                'items' => CartData::getCartItems($cart),
            ];
        }
        
        return $ordersData;
    }

    /**
     * Get ids order by customer id with limit (default = 10)
     *
     * @param int $idCustomer
     * @param integer $limit
     * @return array
     */
    private function getOrdersByCustomerWithLimit($idCustomer, $limit = 10)
    {
        $orders = Order::getCustomerOrders($idCustomer);

        return array_slice($orders, 0, $limit);
    }
}
