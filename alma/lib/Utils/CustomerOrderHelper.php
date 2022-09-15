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

use Alma\PrestaShop\Model\CarrierData;
use Alma\PrestaShop\Model\CartData;
use Cart;
use Context;
use Exception;
use PrestaShopDatabaseException;
use PrestaShopException;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class CustomerOrderHelper
 */
class CustomerOrderHelper
{
    /** @var Context */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Get previous cart items of the customer
     *
     * @param int $idCustomer
     *
     * @return array
     */
    public function previous($idCustomer)
    {
        $cartsData = [];
        $idsCart = $this->getCartIdsByCustomerIdWithLimit($idCustomer);

        $carrier = new CarrierData($this->context);
        foreach ($idsCart as $idCart) {
            $cart = new Cart((int) $idCart);
            $purchaseAmount = (float) Tools::ps_round((float) $cart->getOrderTotal(true, Cart::BOTH), 2);
            $cartsData[] = [
                'purchase_amount' => almaPriceToCents($purchaseAmount),
                'payment_method' => 'alma',
                'shipping_method' => $carrier->getNameById($cart->id_carrier),
                'items' => CartData::getCartItems($cart),
            ];
        }

        return $cartsData;
    }

    /**
     * Get ids cart ordered by customer id with limit (default = 10)
     *
     * @param int $idCustomer
     * @param int $currentIdCart
     * @param int $limit
     *
     * @return array
     */
    private function getCartIdsByCustomerIdWithLimit($idCustomer, $limit = 10)
    {
        $idsCart = [];
        $orders = Order::getCustomerOrders($idCustomer);

        foreach ($orders as $order) {
            $idsCart[] = $order['id_cart'];
        }

        return array_slice($idsCart, 0, $limit);
    }
}
