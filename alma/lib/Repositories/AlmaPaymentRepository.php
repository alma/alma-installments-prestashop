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

namespace Alma\PrestaShop\Repositories;

use Alma\PrestaShop\Factories\LoggerFactory;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Repository for the ps_alma_payment table.
 *
 * This table is a SQL-level safety net against duplicate order creation.
 * The UNIQUE KEY on (id_cart, status) makes it impossible to insert two
 * CAPTURED rows for the same cart — even under concurrent load — because
 * the second INSERT will raise a MySQL 1062 error before validateOrder() is
 * ever called.
 *
 * It works as a complement to the advisory lock (CartLockService): the lock
 * handles the common race condition, while this constraint handles the edge
 * case where two processes both pass the lock (e.g. abnormal lock timeout).
 */
class AlmaPaymentRepository
{
    const STATUS_PENDING = 'PENDING';
    const STATUS_CAPTURED = 'CAPTURED';
    const STATUS_FAILED = 'FAILED';
    const MYSQL_DUPLICATE_ENTRY_CODE = 1062;

    /**
     * Creates the ps_alma_payment table.
     * The UNIQUE KEY uq_cart_captured on (id_cart, status) guarantees that
     * a given cart cannot have more than one CAPTURED row — blocking SQL-level duplicates.
     *
     * @return bool
     */
    public function createTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'alma_payment` (
            `id_alma_payment`  INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_cart`          INT UNSIGNED NOT NULL,
            `alma_payment_id`  VARCHAR(255) NOT NULL,
            `status`           VARCHAR(50)  NOT NULL,
            `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_alma_payment`),
            UNIQUE KEY `uq_cart_captured` (`id_cart`, `status`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        return \Db::getInstance()->execute($sql);
    }

    /**
     * Attempts to insert a CAPTURED row for the given cart before validateOrder() is called.
     *
     * Returns true if the INSERT succeeded (this process is the sole owner of the capture).
     * Returns false if a UNIQUE KEY violation is raised (MySQL 1062), meaning another process
     * already inserted a CAPTURED row for this cart — validateOrder() must NOT be called.
     *
     * Any other database exception is re-thrown so the caller can handle it appropriately.
     *
     * @param int    $cartId
     * @param string $almaPaymentId
     *
     * @return bool true if INSERT succeeded, false on duplicate
     *
     * @throws \PrestaShopDatabaseException on unexpected DB errors
     */
    public function insertCapture($cartId, $almaPaymentId)
    {
        try {
            return \Db::getInstance()->insert(
                'alma_payment',
                [
                    'id_cart' => (int) $cartId,
                    'alma_payment_id' => pSQL($almaPaymentId),
                    'status' => pSQL(self::STATUS_CAPTURED),
                ]
            );
        } catch (\PrestaShopDatabaseException $e) {
            if ($this->isDuplicateEntryError($e)) {
                LoggerFactory::instance()->warning(
                    '[Alma] Duplicate CAPTURED row blocked for cart ' . $cartId . ' — order already being created by another process'
                );

                return false;
            }

            throw $e;
        }
    }

    /**
     * Removes all payment rows for a given cart.
     * Useful for cleanup in tests or error recovery.
     *
     * @param int $cartId
     *
     * @return bool
     */
    public function deleteByCartId($cartId)
    {
        return \Db::getInstance()->delete(
            'alma_payment',
            '`id_cart` = ' . (int) $cartId
        );
    }

    /**
     * Returns true if the given exception was caused by a MySQL duplicate entry error (1062).
     *
     * @param \PrestaShopDatabaseException $e
     *
     * @return bool
     */
    private function isDuplicateEntryError(\PrestaShopDatabaseException $e)
    {
        return strpos($e->getMessage(), (string) self::MYSQL_DUPLICATE_ENTRY_CODE) !== false
            || strpos($e->getMessage(), 'Duplicate entry') !== false;
    }
}
