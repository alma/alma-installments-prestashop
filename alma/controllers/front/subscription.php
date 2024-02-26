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
use Alma\PrestaShop\Exceptions\SubscriptionException;
use Alma\PrestaShop\Exceptions\TokenException;
use Alma\PrestaShop\Helpers\SubscriptionHelper;
use Alma\PrestaShop\Helpers\TokenHelper;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Services\InsuranceApiService;
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
     * IPN constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->context = Context::getContext();
        $this->subscriptionHelper = new SubscriptionHelper(
          new AlmaInsuranceProductRepository(),
          new InsuranceApiService(),
          new TokenHelper()
        );
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
        $reason = Tools::getValue('reason');

        if (!$sid) {
            $msg = $this->module->l('Subscription id is missing', 'subscription');
            Logger::instance()->error($msg);
            $this->ajaxRenderAndExit(json_encode(['error' => $msg]), 500);
        }

        try {
            $this->responseSubscriptionByAction($action, $sid, $trace);
        } catch (AlmaException $e) {
            Logger::instance()->error(json_encode($e));
            $this->ajaxRenderAndExit(json_encode(['error' => $e->getMessage()]), $e->getCode());
        } catch (PrestaShopException $e) {
        }
    }

    /**
     * @param $action
     * @param $sid
     * @param $trace
     *
     * @throws PrestaShopException
     * @throws SubscriptionException
     * @throws TokenException
     * @throws \Alma\PrestaShop\Exceptions\InsuranceSubscriptionException
     */
    public function responseSubscriptionByAction($action, $sid, $trace)
    {
        switch ($action) {
            case 'update':
                $this->update($trace, $sid);
                break;
            case 'cancel':
                $this->cancel($sid);
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
     * @param $trace
     * @param $sid
     *
     * @throws PrestaShopException
     * @throws SubscriptionException
     */
    private function update($trace, $sid)
    {
        $this->subscriptionHelper->updateSubscriptionWithTrace($trace, $sid);
        $this->ajaxRenderAndExit(json_encode(['error' => false, 'message' => '']), 200);
    }

    /**
     * @param $sid
     *
     * @return void
     *
     * @throws PrestaShopException
     * @throws TokenException
     * @throws \Alma\PrestaShop\Exceptions\InsuranceSubscriptionException
     */
    private function cancel($sid)
    {
        $this->subscriptionHelper->cancelSubscriptionWithToken($sid);
        // @TOTO : set notification order message with link to the order in the message
        $this->ajaxRenderAndExit(json_encode(['error' => false, 'message' => '']), 200);
    }
}
