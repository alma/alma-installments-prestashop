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

use Alma\PrestaShop\Exceptions\AlmaException;
use Alma\PrestaShop\Exceptions\InsurancePendingCancellationException;
use Alma\PrestaShop\Exceptions\InsuranceSubscriptionException;
use Alma\PrestaShop\Exceptions\SubscriptionException;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Helpers\SubscriptionHelper;
use Alma\PrestaShop\Helpers\TokenHelper;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Services\InsuranceApiService;
use Alma\PrestaShop\Services\InsuranceSubscriptionService;
use Alma\PrestaShop\Traits\AjaxTrait;

if (!defined('_PS_VERSION_')) {
    exit;
}
class AlmaSubscriptionModuleFrontController extends ModuleFrontController
{
    use AjaxTrait;

    public $ssl = true;
    /**
     * @var SubscriptionHelper
     */
    protected $subscriptionHelper;
    /**
     * @var InsuranceSubscriptionService
     */
    protected $insuranceSubscriptionService;

    /**
     * IPN constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->context = Context::getContext();
        $almaInsuranceProductRepository = new AlmaInsuranceProductRepository();
        $this->insuranceSubscriptionService = new InsuranceSubscriptionService(
            $almaInsuranceProductRepository
        );
        $this->subscriptionHelper = new SubscriptionHelper(
            $almaInsuranceProductRepository,
            new InsuranceApiService(),
            new TokenHelper(),
            $this->insuranceSubscriptionService
        );
    }

    /**
     * @return void
     *
     * @throws InsuranceSubscriptionException
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        parent::postProcess();

        $action = Tools::getValue('action');
        $sid = Tools::getValue('sid');
        $trace = Tools::getValue('trace');
        $reason = Tools::getValue('reason');

        if (!$sid) {
            $msg = $this->module->l('Subscription id is missing', 'subscription');
            Logger::instance()->error($msg);
            $this->ajaxRenderAndExit(json_encode(['error' => $msg]), 500);
        }

        $this->responseSubscriptionByAction($action, $sid, $trace, $reason);
    }

    /**
     * @param $action
     * @param $sid
     * @param $trace
     * @param string $reason
     *
     * @throws InsuranceSubscriptionException
     * @throws PrestaShopException
     */
    public function responseSubscriptionByAction($action, $sid, $trace, $reason = '')
    {
        switch ($action) {
            case 'update':
                $this->update($sid, $trace);
                break;
            case 'cancel':
                $this->cancel($sid, $reason);
                break;
            default:
                $this->ajaxRenderAndExit(
                    json_encode([
                        'error' => true,
                        'message' => $this->module->l('Action is unknown', 'subscription'),
                    ]),
                    500
                );
            break;
        }
    }

    /**
     * @param $sid
     * @param $trace
     *
     * @throws PrestaShopException
     */
    private function update($sid, $trace)
    {
        $response = [
            'success' => true,
            'code' => 200,
        ];

        try {
            $this->subscriptionHelper->updateSubscriptionWithTrace($sid, $trace);
        } catch (SubscriptionException $e) {
            $response = [
                'error' => true,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
            Logger::instance()->error(json_encode($e->getMessage()));
        }
        $this->ajaxRenderAndExit(json_encode($response), $response['code']);
    }

    /**
     * @param $sid
     * @param $reason
     *
     * @return void
     *
     * @throws InsuranceSubscriptionException
     * @throws PrestaShopException
     */
    private function cancel($sid, $reason)
    {
        $response = [
            'success' => true,
            'state' => InsuranceHelper::ALMA_INSURANCE_STATUS_CANCELED,
            'code' => 200,
        ];

        try {
            $this->subscriptionHelper->cancelSubscriptionWithToken($sid);
        } catch (InsurancePendingCancellationException $e) {
            Logger::instance()->error($e->getMessage(), $e->getTrace());
            $response['state'] = InsuranceHelper::ALMA_INSURANCE_STATUS_PENDING_CANCELLATION;
        } catch (AlmaException $e) {
            Logger::instance()->error($e->getMessage(), $e->getTrace());
            $response = [
                'error' => true,
                'message' => $this->module->l('Error to cancel subscription', 'subscription'),
                'state' => InsuranceHelper::ALMA_INSURANCE_STATUS_FAILED,
                'code' => $e->getCode(),
            ];
        }
        // @TODO : set notification order message with link to the order in the message
        $this->insuranceSubscriptionService->setCancellation(
            $sid,
            $response['state'],
            $reason
        );

        $this->ajaxRenderAndExit(json_encode($response), $response['code']);
    }
}
