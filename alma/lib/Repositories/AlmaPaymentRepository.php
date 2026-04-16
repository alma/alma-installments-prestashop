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

use Alma\PrestaShop\Exceptions\PaymentValidationException;
use Alma\PrestaShop\Factories\LoggerFactory;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Repository for the ps_alma_payment table.
 *
 * This table is a SQL-level safety net against duplicate order creation.
 * The UNIQUE KEY on (alma_payment_id, status) makes it impossible to insert two
 * CAPTURED rows for the same Alma payment — even under concurrent load — because
 * the second INSERT will raise a MySQL 1062 error before validateOrder() is
 * ever called.
 *
 * It works as a complement to the advisory lock (CartLockService): the lock
 * handles the common race condition, while this constraint handles the edge
 * case where two processes both pass the lock (e.g. abnormal lock timeout).
 * Using alma_payment_id (not id_cart) as the key ensures the protection is
 * tied to the actual payment entity, which is globally unique at Alma's side.
 */
class AlmaPaymentRepository
{
    const STATUS_CAPTURED = 'CAPTURED';

    /**
     * Creates the ps_alma_payment table.
     * The UNIQUE KEY uq_payment_status on (alma_payment_id, status) guarantees that
     * a given Alma payment cannot have more than one CAPTURED row — blocking SQL-level duplicates.
     * alma_payment_id is globally unique at Alma's side and is the correct entity identifier.
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
            UNIQUE KEY `uq_payment_status` (`alma_payment_id`, `status`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        return \Db::getInstance()->execute($sql);
    }

    /**
     * Records the initial payment state right after creation in payment.php, before the customer
     * is redirected to Alma's checkout. This creates the row that will later be updated to CAPTURED
     * when the customer returns. Uses INSERT ... ON DUPLICATE KEY UPDATE so it is safely idempotent
     * (browser retries, network glitches).
     *
     * @param int    $cartId
     * @param string $almaPaymentId
     * @param string $status        The initial state returned by Alma (e.g. "not_started")
     *
     * @return bool
     */
    public function trackInitialPayment($cartId, $almaPaymentId, $status)
    {
        $sql = 'INSERT IGNORE INTO `' . _DB_PREFIX_ . 'alma_payment`
                    (`id_cart`, `alma_payment_id`, `status`)
                VALUES
                    (' . (int) $cartId . ', \'' . pSQL($almaPaymentId) . '\', \'' . pSQL($status) . '\')';

        return \Db::getInstance()->execute($sql);
    }

    /**
     * Attempts to insert a CAPTURED row for the given payment before validateOrder() is called.
     *
     * Returns true if the INSERT succeeded (this process is the sole owner of the capture).
     * Returns false if a UNIQUE KEY violation is raised (MySQL 1062), meaning another process
     * already inserted a CAPTURED row for this payment — validateOrder() must NOT be called.
     *
     * Any other database exception is wrapped in a PaymentValidationException so the caller
     * can handle it explicitly (e.g. redirect to error page) without leaking DB internals.
     *
     * @param int    $cartId
     * @param string $almaPaymentId
     *
     * @return bool true if INSERT succeeded, false on duplicate
     *
     * @throws PaymentValidationException on unexpected DB errors
     */
    public function insertCapture($cartId, $almaPaymentId)
    {
        try {
            return \Db::getInstance()->insert(
                'alma_payment',
                [
                    'id_cart' => (int) $cartId,
                    'alma_payment_id' => $almaPaymentId,
                    'status' => self::STATUS_CAPTURED,
                ]
            );
        } catch (\PrestaShopDatabaseException $e) {
            if ($this->isDuplicateEntryError($e)) {
                LoggerFactory::instance()->warning(
                    '[Alma] Duplicate CAPTURED row blocked for payment ' . $almaPaymentId . ' — order already being created by another process'
                );

                return false;
            }

            LoggerFactory::instance()->error(
                '[Alma] Unexpected DB error in insertCapture for payment ' . $almaPaymentId . ': ' . $e->getMessage()
            );

            throw new PaymentValidationException('[Alma] DB error during capture insert for payment ' . $almaPaymentId, (int) $cartId, 0, $e);
        }
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
        return strpos($e->getMessage(), 'Duplicate entry') !== false;
    }
}
