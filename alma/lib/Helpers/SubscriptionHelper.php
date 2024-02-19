<?php
/**
 * 2018-2023 Alma SAS.
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
 * @copyright 2018-2023 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Helpers;

use Alma\PrestaShop\Exceptions\SubscriptionException;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class SubscriptionHelper
 */
class SubscriptionHelper
{
    /**
     * @var AlmaInsuranceProductRepository|mixed|null
     */
    protected $almaInsuranceProductRepository;

    public function __construct($almaInsuranceProductRepository = null)
    {
        if (!$almaInsuranceProductRepository) {
            $almaInsuranceProductRepository = new AlmaInsuranceProductRepository();
        }
        $this->almaInsuranceProductRepository = $almaInsuranceProductRepository;
    }

    /**
     * @param string $subscriptionId
     * @param string $status
     * @param string $subscriptionBrokerId
     *
     * @return void
     *
     * @throws SubscriptionException
     */
    public function updateSubscription($subscriptionId, $status, $subscriptionBrokerId)
    {
        if (!$this->almaInsuranceProductRepository->updateSubscription($subscriptionId, $status, $subscriptionBrokerId)) {
            throw new SubscriptionException('Error to update DB Alma Insurance Product');
        }
    }
}
