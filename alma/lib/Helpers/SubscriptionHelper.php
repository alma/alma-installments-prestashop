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
use Alma\PrestaShop\Services\InsuranceApiService;

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
     * @var mixed
     */
    protected $module;
    protected $tokenHelper;

    public function __construct(
        $module,
        $almaInsuranceProductRepository = null
    ) {
        if (!$almaInsuranceProductRepository) {
            $almaInsuranceProductRepository = new AlmaInsuranceProductRepository();
        }
        $this->almaInsuranceProductRepository = $almaInsuranceProductRepository;
        $this->insuranceApiService = new InsuranceApiService();
        $this->module = $module;
        $this->tokenHelper = new TokenHelper();
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

    /**
     * @param $action
     * @param $sid
     * @param $trace
     *
     * @return array
     */
    public function responseSubscriptionByAction($action, $sid, $trace)
    {
        $response = [
            'response' => json_encode(['error' => false, 'message' => '']),
            'code' => 200,
        ];

        switch ($action) {
            case 'update':
                if (!$trace) {
                    $response = [
                        'response' => json_encode([
                            'error' => true,
                            'message' => $this->module->l('Secutiry trace is missing', 'subscription'),
                        ]),
                        'code' => 500,
                    ];
                }
                try {
                    $subscriptionArray = $this->insuranceApiService->getSubscriptionById($sid);

                    $this->updateSubscription(
                        $sid,
                        $subscriptionArray['state'],
                        $subscriptionArray['broker_subscription_id']
                    );
                } catch (SubscriptionException $e) {
                    $response = [
                        'response' => json_encode(['error' => true, 'message' => $e->getMessage()]),
                        'code' => 500,
                    ];
                }
                break;
            // @TOTO : set notification order message with link to the order in the message
            case 'cancel':
                $response = [
                    'response' => json_encode(['error' => 'Invalid Token']),
                    'code' => 401,
                ];

                if ($this->tokenHelper->isAdminTokenValid(
                    ConstantsHelper::BO_CONTROLLER_INSURANCE_ORDERS_DETAILS_CLASSNAME,
                    'token'
                )) {
                    $response = $this->cancelSubscription($sid);
                }
                break;
            default:
                $response = [
                    'response' => json_encode([
                        'error' => true,
                        'message' => $this->module->l('Action is unknown', 'subscription'),
                        ]),
                    'code' => 500,
                ];
        }

        return $response;
    }

    /**
     * @param $sid
     *
     * @return array
     */
    private function cancelSubscription($sid)
    {
        try {
            $response = $this->insuranceApiService->cancelSubscription($sid);
            //@TODO : Service set database with (status, reason , date_cancel, request_cancel_date)
        } catch (SubscriptionException $e) {
            $response = [
                'response' => json_encode([
                    'error' => true,
                    'message' => $e->getMessage(),
                ]),
                'code' => 500,
            ];
        }

        return $response;
    }
}
