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
    const MYSQL_ERR_DUPLICATE_ENTRY = 1062;

    /**
     * Defensive self-heal called from front controllers: if the upgrade routine never ran
     * (e.g. files replaced via FTP/git pull without going through PS's Module Manager),
     * the table is missing and insertCapture would fail silently.
     *
     * Relies on the CREATE TABLE IF NOT EXISTS being idempotent rather than checking
     * existence first: a single statement is robust to weird DB states (ANSI_QUOTES,
     * SHOW TABLES permission issues, transient connection errors) and matches the cost
     * of the previous SHOW TABLES probe — both are O(1) data-dictionary lookups.
     *
     * @return void
     */
    public function createTableIfNotExist()
    {
        try {
            $this->createTable();
        } catch (\PrestaShopException $e) {
            LoggerFactory::instance()->warning('[Alma] Error in create table alma_payment: ' . $e->getMessage());
        }
    }

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
     * Returns false ONLY when a UNIQUE KEY violation is raised (MySQL 1062): another process
     * already inserted a CAPTURED row for this payment — validateOrder() must NOT be called.
     *
     * Every other failure mode (table missing, deadlock, FK error, server gone away, etc.)
     * is wrapped in a PaymentValidationException so the caller fails loudly instead of
     * misinterpreting the failure as a duplicate.
     *
     * Two detection paths run in parallel because PS's Db layer behaves differently
     * depending on the driver and PS_MODE_DEV / PS_DEBUG_SQL:
     *  - DbMySQLi (debug) throws PrestaShopDatabaseException, DbPDO throws plain
     *    PrestaShopException — both are handled by the catch block since the latter
     *    is the parent class.
     *  - In production with errors silenced, Db::insert() returns false → we inspect
     *    getNumberError() to tell 1062 from any other failure.
     *
     * @param int    $cartId
     * @param string $almaPaymentId
     *
     * @return bool true if INSERT succeeded, false on duplicate
     *
     * @throws PaymentValidationException on any other DB error
     */
    public function insertCapture($cartId, $almaPaymentId)
    {
        try {
            $inserted = \Db::getInstance()->insert(
                'alma_payment',
                [
                    'id_cart' => (int) $cartId,
                    'alma_payment_id' => $almaPaymentId,
                    'status' => self::STATUS_CAPTURED,
                ]
            );
        } catch (\PrestaShopException $e) {
            if ($this->isDuplicateEntryError($e)) {
                LoggerFactory::instance()->warning(
                    '[Alma] Duplicate CAPTURED row blocked for payment ' . $almaPaymentId . ' — order already being created by another process'
                );

                return false;
            }

            LoggerFactory::instance()->error(
                '[Alma] Unexpected DB exception in insertCapture for payment ' . $almaPaymentId . ': ' . $e->getMessage()
            );

            throw new PaymentValidationException('[Alma] DB error during capture insert for payment ' . $almaPaymentId, (int) $cartId, 0, $e);
        }

        if ($inserted) {
            return true;
        }

        $errno = (int) \Db::getInstance()->getNumberError();
        $errMsg = \Db::getInstance()->getMsgError();

        if (self::MYSQL_ERR_DUPLICATE_ENTRY === $errno) {
            LoggerFactory::instance()->warning(
                '[Alma] Duplicate CAPTURED row blocked for payment ' . $almaPaymentId . ' — order already being created by another process'
            );

            return false;
        }

        LoggerFactory::instance()->error(
            '[Alma] DB error in insertCapture for payment ' . $almaPaymentId . ' (errno ' . $errno . '): ' . $errMsg
        );

        throw new PaymentValidationException('[Alma] DB error during capture insert for payment ' . $almaPaymentId . ' (errno ' . $errno . ')', (int) $cartId);
    }

    /**
     * Returns true if the given exception was caused by a MySQL duplicate entry error (1062).
     *
     * @param \PrestaShopException $e
     *
     * @return bool
     */
    private function isDuplicateEntryError(\PrestaShopException $e)
    {
        return strpos($e->getMessage(), 'Duplicate entry') !== false;
    }
}
