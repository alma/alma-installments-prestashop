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

use Alma\PrestaShop\Exceptions\SubscriptionException;
use Alma\PrestaShop\Helpers\SubscriptionHelper;
use Alma\PrestaShop\Logger;
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
     * @var \Alma\PrestaShop\Services\InsuranceApiService
     */
    protected $insuranceApiService;

    /**
     * IPN constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->context = Context::getContext();
        $this->subscriptionHelper = new SubscriptionHelper(new \Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository());
        $this->insuranceApiService = new \Alma\PrestaShop\Services\InsuranceApiService();
    }

    /**
     * @return void
     *
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        parent::postProcess();

        $action = Tools::getValue('action');
        $sid = Tools::getValue('sid');
        $trace = Tools::getValue('trace');

        if (!$sid) {
            $msg = $this->module->l('Subscription id is missing', 'subscription');
            Logger::instance()->error($msg);
            $this->ajaxRenderAndExit(json_encode(['error' => $msg]), 500);
        }

        if (!$trace) {
            $this->ajaxRenderAndExit(
                json_encode(
                    ['error' => $this->module->l('Secutiry token is missing', 'subscription')]
                ),
                500
            );
        }

        $response = ['error' => false, 'message' => ''];
        switch ($action) {
            case 'update':
                try {
                    $subscriptionArray = $this->insuranceApiService->getSubscriptionById($sid);

                    $this->subscriptionHelper->updateSubscription(
                        $sid,
                        $subscriptionArray['state'],
                        $subscriptionArray['broker_subscription_id']
                    );
                } catch (SubscriptionException $e) {
                    $response = ['error' => true, 'message' => $e->getMessage()];
                }
                break;
                // @TOTO : set notification order message with link to the order in the message
            default:
                $response = [
                    'error' => true,
                    'message' => $this->module->l('Action is unknown', 'subscription'),
                ];
        }

        if (!$response['error']) {
            $this->ajaxRenderAndExit(json_encode(['success' => true]), 200);
        }

        $this->ajaxRenderAndExit(json_encode(['error' => $response['message']]), 500);
    }
}
