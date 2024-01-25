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

namespace Alma\PrestaShop\Helpers;

use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Model\CartData;
use Alma\PrestaShop\Model\OrderData;
use Alma\PrestaShop\Repositories\OrderRepository;
use Alma\PrestaShop\Repositories\ProductRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class CartHelper
 */
class CartHelper
{
    /** @var \ContextCore */
    private $context;
    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    public function __construct()
    {
        $this->context = \Context::getContext();
        $this->orderRepository = new OrderRepository();
    }

    /**
     * @return null/int
     */
    public function getCartIdFromContext()
    {
        $cartId = null;

        if(isset($this->context->cart->id)) {
            $cartId = $this->context->cart->id;
        }

        return $cartId;
    }
    /**
     * Previous cart items of the customer
     *
     * @param int $idCustomer
     *
     * @return array
     */
    public function previousCartOrdered($idCustomer)
    {
        $ordersData = [];
        $orders = $this->getOrdersByCustomer($idCustomer, 10);
        $orderStateHelper = new OrderStateHelper($this->context);
        $productHelper = new ProductHelper();
        $productRepository = new ProductRepository();

        $carrier = new CarrierHelper($this->context);
        foreach ($orders as $order) {
            $cart = new \Cart((int) $order['id_cart']);
            $purchaseAmount = -1;
            try {
                $purchaseAmount = \Tools::ps_round((float) $cart->getOrderTotal(), 2);
            } catch (\Exception $e) {
                $msg = '[Alma] purchase amount for previous cart ordered no found';
                Logger::instance()->warning($msg);
            }

            $cartItems = [];
            try {
                $cartItems = CartData::getCartItems($cart, $productHelper, $productRepository);
            } catch (\PrestaShopDatabaseException $e) {
                $msg = '[Alma] cart items for previous cart ordered no found';
                Logger::instance()->warning($msg);
            } catch (\PrestaShopException $e) {
                $msg = '[Alma] cart items for previous cart ordered no found';
                Logger::instance()->warning($msg);
            }
            $ordersData[] = [
                'purchase_amount' => PriceHelper::convertPriceToCents($purchaseAmount),
                'created' => strtotime($order['date_add']),
                'payment_method' => $order['payment'],
                'alma_payment_external_id' => $order['module'] === 'alma' ? $order['transaction_id'] : null,
                'current_state' => $orderStateHelper->getNameById($order['current_state']),
                'shipping_method' => $carrier->getParentCarrierNameById($cart->id_carrier),
                'items' => $cartItems,
            ];
        }

        return $ordersData;
    }

    /**
     * Get ids order by customer id with limit (default = 10)
     *
     * @param int $idCustomer
     * @param int $limit
     *
     * @return array
     */
    private function getOrdersByCustomer($idCustomer, $limit)
    {
        try {
            $orders = $this->orderRepository->getCustomerOrders($idCustomer, $limit);
        } catch (\PrestaShopDatabaseException $e) {
            return [];
        }

        return $orders;
    }
}
