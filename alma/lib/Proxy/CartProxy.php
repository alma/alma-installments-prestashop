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

use Alma\PrestaShop\Exceptions\OrderException;
use Alma\PrestaShop\Factories\CartFactory;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartProxy
{
    /**
     * @var \Alma\PrestaShop\Factories\CartFactory
     */
    private $cartFactory;
    /**
     * @var string
     */
    private $psVersion;

    public function __construct($cartFactory = null)
    {
        if (!$cartFactory) {
            $cartFactory = new CartFactory();
        }
        $this->cartFactory = $cartFactory;
        $this->psVersion = _PS_VERSION_;
    }

    /**
     * Issue duplicate order multiple payment in same time
     * We proxify this method to avoid duplicate order for Prestashop versions < 1.7.7.0
     * Check if order has already been placed
     *
     * @param int|string $cartId
     * @return bool
     */
    public function orderExists($cartId)
    {
        if (version_compare($this->psVersion, '1.7.7.0', '<')) {
            return (bool) \Db::getInstance()->getValue(
                'SELECT count(*) FROM `' . _DB_PREFIX_ . 'orders` WHERE `id_cart` = ' . (int) $cartId,
                false
            );
        }

        $cart = $this->cartFactory->create($cartId);
        return $cart->orderExists();
    }

    /**
     * Vérification thread-safe pour les retours de paiement
     */
    public function checkOrderExistsForPayment($cartId)
    {
        \Db::getInstance()->execute('START TRANSACTION');

        try {
            $lockResult = \Db::getInstance()->execute(
                'SELECT id_cart FROM ' . _DB_PREFIX_ . 'cart
             WHERE id_cart = ' . (int) $cartId . '
             FOR UPDATE'
            );

            if (!$lockResult) {
                throw new OrderException('Unable to lock cart or cart not found');
            }

            $orderExists = $this->orderExists($cartId);

            if (!$orderExists) {
                return false;
            }

            \Db::getInstance()->execute('COMMIT');
            return true;
        } catch (OrderException $e) {
            \Db::getInstance()->execute('ROLLBACK');
            throw $e;
        }
    }

    /**
     * Setter for Unit Test
     * @param $psVersion
     * @return void
     */
    public function setPsVersion($psVersion)
    {
        $this->psVersion = $psVersion;
    }
}
