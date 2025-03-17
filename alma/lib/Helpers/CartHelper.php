<?php
/**
 * 2018-2024 Alma SAS.
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
 * @copyright 2018-2024 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Helpers;

use Alma\PrestaShop\Factories\CartFactory;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\LoggerFactory;
use Alma\PrestaShop\Model\CartData;
use Alma\PrestaShop\Repositories\OrderRepository;

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

    /**
     * @var ToolsHelper
     */
    protected $toolsHelper;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var CartData
     */
    protected $cartData;

    /**
     * @var OrderStateHelper
     */
    protected $orderStateHelper;

    /**
     * @var CarrierHelper
     */
    protected $carrierHelper;

    /**
     * @var CartFactory
     */
    protected $cartFactory;

    /**
     * @var OrderHelper
     */
    protected $orderHelper;

    /**
     * @param ContextFactory $contextFactory
     * @param ToolsHelper $toolsHelper
     * @param PriceHelper $priceHelper
     * @param CartData $cartData
     * @param OrderRepository $orderRepository
     * @param OrderStateHelper $orderStateHelper
     * @param CarrierHelper $carrierHelper
     * @param CartFactory $cartFactory
     */
    public function __construct(
        $contextFactory,
        $toolsHelper,
        $priceHelper,
        $cartData,
        $orderRepository,
        $orderStateHelper,
        $carrierHelper,
        $cartFactory,
        $orderHelper
    ) {
        $this->context = $contextFactory->getContext();
        $this->toolsHelper = $toolsHelper;
        $this->priceHelper = $priceHelper;
        $this->cartData = $cartData;
        $this->orderRepository = $orderRepository;
        $this->orderStateHelper = $orderStateHelper;
        $this->carrierHelper = $carrierHelper;
        $this->cartFactory = $cartFactory;
        $this->orderHelper = $orderHelper;
    }

    /**
     * @return int|null
     */
    public function getCartIdFromContext()
    {
        $cartId = null;

        if (isset($this->context->cart->id)) {
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
        $orders = $this->orderHelper->getOrdersByCustomer($idCustomer, 10);

        foreach ($orders as $order) {
            $cart = $this->cartFactory->create((int) $order['id_cart']);
            $purchaseAmount = -1;

            try {
                $purchaseAmount = $this->toolsHelper->psRound((float) $cart->getOrderTotal(), 2);
            } catch (\Exception $e) {
                LoggerFactory::instance()->warning('[Alma] purchase amount for previous cart ordered no found');
            }

            $cartItems = [];

            try {
                $cartItems = $this->cartData->getCartItems($cart);
            } catch (\PrestaShopDatabaseException $e) {
                LoggerFactory::instance()->warning('[Alma] cart items for previous cart ordered no found');
            } catch (\PrestaShopException $e) {
                LoggerFactory::instance()->warning('[Alma] cart items for previous cart ordered no found');
            }

            $ordersData[] = [
                'purchase_amount' => $this->priceHelper->convertPriceToCents($purchaseAmount),
                'created' => strtotime($order['date_add']),
                'payment_method' => $order['payment'],
                'alma_payment_external_id' => $order['module'] === 'alma' ? $order['transaction_id'] : null,
                'current_state' => $this->orderStateHelper->getNameById($order['current_state']),
                'shipping_method' => $this->carrierHelper->getParentCarrierNameById($cart->id_carrier),
                'items' => $cartItems,
            ];
        }

        return $ordersData;
    }

    /**
     * @param \Cart $cart
     *
     * @return float
     *
     * @throws \Exception
     */
    public function getCartTotal($cart)
    {
        return (float) $this->priceHelper->convertPriceToCents(
            $this->toolsHelper->psRound((float) $cart->getOrderTotal(true, \Cart::BOTH), 2)
        );
    }
}
