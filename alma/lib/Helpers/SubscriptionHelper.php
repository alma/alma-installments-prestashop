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

use Alma\PrestaShop\Exceptions\SubscriptionException;
use Alma\PrestaShop\Exceptions\TokenException;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class SubscriptionHelper
 */
class SubscriptionHelper
{
    /**
     */
    /**
     */
    /**
     * @var TokenHelper
     */
    protected $tokenHelper;
    /**
     */

    public function __construct(
        $tokenHelper,
    ) {
        $this->tokenHelper = $tokenHelper;
    }

    /**
     * @param $sid
     *
     * @return void
     *
     * @throws TokenException
     */
    public function cancelSubscriptionWithToken($sid)
    {
        if (!$this->tokenHelper->isAdminTokenValid('token')) {
            throw new TokenException('Invalid Token', 401);
        }
    }

    /**
     * @param string $trace
     * @param string $sid
     *
     * @return mixed
     *
     * @throws SubscriptionException
     */
    public function updateSubscriptionWithTrace($sid, $trace)
    {
        $this->isTraceValid($trace);

        return null;
    }

    /**
     * @param $trace
     *
     * @throws SubscriptionException
     */
    private function isTraceValid($trace)
    {
        if (!is_string($trace) || empty($trace)) {
            throw new SubscriptionException('Security trace is missing', 500);
        }
    }
}
