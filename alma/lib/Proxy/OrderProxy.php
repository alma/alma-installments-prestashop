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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Proxifies Order static methods that differ across PrestaShop versions.
 *
 * `Order::getIdByCartId()` was introduced in PS 1.7.1.0.
 * In earlier versions (>= 1.5.3.1) the equivalent is `Order::getOrderByCartId()`.
 */
class OrderProxy
{
    /**
     * @var string
     */
    private $psVersion;

    public function __construct()
    {
        $this->psVersion = _PS_VERSION_;
    }

    /**
     * Returns the order ID associated with the given cart ID.
     *
     * Delegates to `Order::getIdByCartId()` on PS >= 1.7.1.0,
     * and falls back to `Order::getOrderByCartId()` on older versions.
     *
     * @param int $cartId
     *
     * @return int
     */
    public function getIdByCartId($cartId)
    {
        if (version_compare($this->psVersion, '1.7.1.0', '<')) {
            return (int) $this->callGetOrderByCartId((int) $cartId);
        }

        return (int) $this->callGetIdByCartId((int) $cartId);
    }

    /**
     * @param int $cartId
     *
     * @return int
     */
    protected function callGetIdByCartId($cartId)
    {
        return \Order::getIdByCartId($cartId);
    }

    /**
     * @param int $cartId
     *
     * @return int
     */
    protected function callGetOrderByCartId($cartId)
    {
        return \Order::getOrderByCartId($cartId);
    }

    /**
     * Setter for unit tests.
     *
     * @param string $psVersion
     *
     * @return void
     */
    public function setPsVersion($psVersion)
    {
        $this->psVersion = $psVersion;
    }
}
