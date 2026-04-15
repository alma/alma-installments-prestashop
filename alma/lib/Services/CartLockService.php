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

namespace Alma\PrestaShop\Services;

use Alma\PrestaShop\Factories\LoggerFactory;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Provides an advisory MySQL lock (GET_LOCK / RELEASE_LOCK) to prevent concurrent order creation
 * for the same cart. This is the recommended approach for PrestaShop: no Redis or external
 * infrastructure required, and the lock is automatically released if the DB connection drops.
 *
 * Usage pattern:
 *   1. acquireLock($prefix, $lockId)  → enter critical section
 *   2. check + validateOrder()
 *   3. releaseLock($prefix)  → always in a finally block
 */
class CartLockService
{
    const LOCK_TIMEOUT_SECONDS = 10;
    const LOCK_ORDER_KEY_PREFIX = 'alma_order_cart_';
    const LOCK_REFUND_KEY_PREFIX = 'alma_refund_cart_';

    /** @var string|null Cart ID currently locked by this instance, null if no lock held */
    private $lockedId = null;

    /**
     * Acquires a MySQL advisory lock for the given lock ID.
     * If another process already holds the lock, waits up to $timeout seconds.
     * Returns true if the lock was acquired, false on timeout.
     *
     * @param string $prefix
     * @param string $lockId
     * @param int $timeout seconds to wait before giving up (default: 10)
     *
     * @return bool
     */
    public function acquireLock($prefix, $lockId, $timeout = self::LOCK_TIMEOUT_SECONDS)
    {
        $lockKey = $this->getLockKey($prefix, $lockId);
        $acquired = (bool) \Db::getInstance()->getValue(
            "SELECT GET_LOCK('" . $lockKey . "', " . (int) $timeout . ')'
        );

        if ($acquired) {
            $this->lockedId = $lockId;
        }

        return $acquired;
    }

    /**
     * Releases the advisory lock previously acquired for the given lock ID.
     * Safe to call even if the lock was never acquired (returns false in that case).
     *
     * @return bool true if the lock was released, false if it was not held by this connection
     */
    public function releaseLock($prefix)
    {
        if ($this->lockedId === null) {
            return false;
        }

        $lockKey = $this->getLockKey($prefix, $this->lockedId);
        $released = (bool) \Db::getInstance()->getValue(
            "SELECT RELEASE_LOCK('" . $lockKey . "')"
        );

        if ($this->lockedId !== null && $released === false) {
            LoggerFactory::instance()->warning('[Alma] releaseLock failed to release lock for Id ' . $this->lockedId);
        }

        $this->lockedId = null;

        return $released;
    }

    /**
     * Returns true if this instance currently holds a lock.
     *
     * @return bool
     */
    public function isLockAcquired()
    {
        return $this->lockedId !== null;
    }

    /**
     * Returns the lock ID currently locked by this instance, or null.
     *
     * @return string|null
     */
    public function getLockedId()
    {
        return $this->lockedId;
    }

    /**
     * @param string $lockId
     *
     * @return string
     */
    private function getLockKey($prefix, $lockId)
    {
        return $prefix . $lockId;
    }
}
