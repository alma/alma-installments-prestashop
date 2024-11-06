<?php /** @noinspection ALL */

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

namespace Alma\PrestaShop\Repositories;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class CartEligibilityRepository.
 *
 * Use for Cart Product
 */
class CartEligibilityRepository
{
    public const TABLE_NAME = 'alma_cart_eligibility';

    /**
     * @param \Cart $cart
     * @param bool $eligibilityResult
     * @return bool
     */
    public function add($cart, $eligibilityResult)
    {
        return \Db::getInstance()->insert(self::TABLE_NAME, [
            'id_cart' => (int) $cart->id,
            'bnpl_eligibility_result' => $eligibilityResult,
        ], false, true, \Db::REPLACE);
    }

    public function remove($cart)
    {
        return \Db::getInstance()->delete(self::TABLE_NAME, 'id_cart = ' . (int) $cart->id);
    }

    public function get($cart)
    {
        return \Db::getInstance()->getValue(
            'SELECT `bnl_eligibility_result` FROM `' . self::TABLE_NAME . '` WHERE `id_cart` = ' . (int) $cart->id
        );
    }

    /**
     * @return bool
     */
    public function createTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . self::TABLE_NAME . '` (
            `id_cart` int(10) unsigned NOT NULL,
            `bnpl_eligibility_result` int(1) unsigned NOT NULL,
            unique (`id_cart`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        return \Db::getInstance()->execute($sql);
    }

    public function deleteTable()
    {
        $sql = 'DROP TABLE `' . _DB_PREFIX_ . self::TABLE_NAME . '`';

        return \Db::getInstance()->execute($sql);
    }
}
