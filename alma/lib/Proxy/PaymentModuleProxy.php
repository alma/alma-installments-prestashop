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

namespace Alma\PrestaShop\Proxy;

use Alma\PrestaShop\Factories\LoggerFactory;
use Alma\PrestaShop\Model\AlmaModuleModel;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentModuleProxy
{
    /**
     * @var \Alma
     */
    private $module;
    /**
     * @var \Alma\PrestaShop\Proxy\CartProxy
     */
    private $cartProxy;

    public function __construct($module = null, $cartProxy = null)
    {
        if (!$module) {
            $module = (new AlmaModuleModel())->getModule();
        }
        $this->module = $module;
        if (!$cartProxy) {
            $cartProxy = new CartProxy();
        }
        $this->cartProxy = $cartProxy;
    }

    /**
     * Issue duplicate order multiple payment in same time
     * We proxify this method to avoid duplicate order
     * Validate order if order has not been already placed
     * @param $id_cart
     * @param $id_order_state
     * @param $amount_paid
     * @param $payment_method
     * @param $message
     * @param $extra_vars
     * @param $currency_special
     * @param $dont_touch_amount
     * @param $secure_key
     * @param \Alma\PrestaShop\Proxy\Shop|null $shop
     * @return false|void
     * @throws \PrestaShopException
     */
    public function validateOrder(
        $id_cart,
        $id_order_state,
        $amount_paid,
        $payment_method = 'Unknown',
        $message = null,
        $extra_vars = [],
        $currency_special = null,
        $dont_touch_amount = false,
        $secure_key = false,
        Shop $shop = null
    ) {
        if ($this->cartProxy->orderExists($id_cart)) {
            LoggerFactory::instance()->warning('[Alma] Attempting to create a duplicate order for cart ID ' . $id_cart);
            return false;
        }

        $this->module->validateOrder(
            $id_cart,
            $id_order_state,
            $amount_paid,
            $payment_method,
            $message,
            $extra_vars,
            $currency_special,
            $dont_touch_amount,
            $secure_key,
            $shop
        );
    }
}
