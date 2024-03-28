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

use Alma\PrestaShop\Exceptions\InsurancePendingCancellationException;
use Alma\PrestaShop\Exceptions\InsuranceSubscriptionException;
use Alma\PrestaShop\Exceptions\SubscriptionException;
use Alma\PrestaShop\Exceptions\TokenException;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Services\InsuranceApiService;
use Alma\PrestaShop\Services\InsuranceSubscriptionService;

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
    /**
     * @var InsuranceApiService
     */
    protected $insuranceApiService;
    /**
     * @var TokenHelper
     */
    protected $tokenHelper;
    /**
     * @var InsuranceSubscriptionService
     */
    protected $insuranceSubscriptionService;

    public function __construct(
        $almaInsuranceProductRepository,
        $insuranceApiService,
        $tokenHelper,
        $insuranceSubscriptionService
    ) {
        $this->almaInsuranceProductRepository = $almaInsuranceProductRepository;
        $this->insuranceApiService = $insuranceApiService;
        $this->tokenHelper = $tokenHelper;
        $this->insuranceSubscriptionService = $insuranceSubscriptionService;
    }

    /**
     * @param $sid
     *
     * @return void
     *
     * @throws InsurancePendingCancellationException
     * @throws InsuranceSubscriptionException
     * @throws TokenException
     */
    public function cancelSubscriptionWithToken($sid)
    {
        if (!$this->tokenHelper->isAdminTokenValid(
            ConstantsHelper::BO_CONTROLLER_INSURANCE_ORDERS_DETAILS_CLASSNAME,
            'token'
        )) {
            throw new TokenException('Invalid Token', 401);
        }

        $this->insuranceApiService->cancelSubscription($sid);
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

        $subscriptionArray = $this->insuranceApiService->getSubscriptionById($sid);
        if (
            !$this->almaInsuranceProductRepository->updateSubscription(
                $sid,
                $subscriptionArray['state'],
                $subscriptionArray['broker_subscription_id'],
                $subscriptionArray['broker_subscription_reference']
            )
        ) {
            throw new SubscriptionException('Error to update DB Alma Insurance Product', 500);
        }

        return $subscriptionArray['state'];
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
