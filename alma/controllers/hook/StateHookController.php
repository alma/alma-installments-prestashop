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

namespace Alma\PrestaShop\Controllers\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\API\Client;
use Alma\API\Exceptions\ParametersException;
use Alma\API\Exceptions\RequestException;
use Alma\API\RequestError;
use Alma\PrestaShop\Builders\Helpers\SettingsHelperBuilder;
use Alma\PrestaShop\Builders\Services\OrderServiceBuilder;
use Alma\PrestaShop\Exceptions\OrderException;
use Alma\PrestaShop\Helpers\ClientHelper;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Helpers\OrderHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Hooks\AdminHookController;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Services\InsuranceSubscriptionService;
use Alma\PrestaShop\Services\OrderService;

final class StateHookController extends AdminHookController
{
    /**
     * @var OrderHelper
     */
    protected $orderHelper;
    /**
     * @var Client|mixed|null
     */
    protected $alma;
    /**
     * @var InsuranceSubscriptionService
     */
    protected $insuranceSubscriptionService;

    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    /**
     * HookController constructor.
     *
     * @codeCoverageIgnore
     *
     * @param $module Alma
     *
     * @var InsuranceHelper
     */
    protected $insuranceHelper;

    /**
     * @var Logger
     */
    protected $almaLogger;
    /**
     * @var OrderService
     */
    protected $orderService;

    public function __construct($module)
    {
        parent::__construct($module);
        $this->alma = ClientHelper::defaultInstance();
        $this->orderHelper = new OrderHelper();
        $this->insuranceSubscriptionService = new InsuranceSubscriptionService();
        $this->insuranceHelper = new InsuranceHelper();
        $settingsHelperBuilder = new SettingsHelperBuilder();
        $this->settingsHelper = $settingsHelperBuilder->getInstance();
        $orderServiceBuilder = new OrderServiceBuilder();
        $this->orderService = $orderServiceBuilder->getInstance();
        $this->almaLogger = new Logger();
    }

    /**
     * Checks if user is logged in as Employee or is an API Webservice call
     *
     * When we check if is an API user, we assume that the API user has already
     * the good rights because when canRun is called, actions linked to the hook
     * were already well authenticated by PrestaShop.
     *
     * @return bool
     */
    public function canRun()
    {
        // Front controllers can run if the module is properly configured ...
        return SettingsHelper::isFullyConfigured();
    }

    /**
     * Execute some trigger on change state (refund, payment, insurance)
     *
     * @param array $params
     *
     * @return void
     *
     * @throws ParametersException
     * @throws RequestException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function run($params)
    {
        if (!$this->alma) {
            return;
        }

        $order = new \Order($params['id_order']);
        $newStatus = $params['newOrderStatus'];

        switch ($newStatus->id) {
            case SettingsHelper::getRefundState():
                if ($this->loggedAsEmployee() || $this->isKnownApiUser()) {
                    $this->refund($order);
                }
                break;
            case SettingsHelper::getPaymentTriggerState():
                if ($this->loggedAsEmployee() || $this->isKnownApiUser()) {
                    $this->triggerPayment($order);
                }
                break;
            case (int) \Configuration::get('PS_OS_PAYMENT'):
                if ($this->insuranceHelper->isInsuranceActivated()) {
                    $this->processInsurance($order);
                }
                break;
            default:
                break;
        }

        try {
            $this->orderService->manageStatusUpdate($order, $newStatus);
        } catch (\Exception $e) {
            $this->almaLogger->info(
                sprintf(
                    'Impossible to update order status: Error : %s, Code : %s, Type : %s',
                    $e->getMessage(),
                    $e->getCode(),
                    get_class($e)
                )
            );
        }
    }

    /**
     * @param \OrderCore $order
     *
     * @return void
     */
    protected function processInsurance($order)
    {
        try {
            if ($this->insuranceHelper->canInsuranceSubscriptionBeTriggered($order)) {
                $this->insuranceSubscriptionService->triggerInsuranceSubscription($order);
            }
        } catch (\Exception $e) {
            Logger::instance()->error($e->getMessage(), $e->getTrace());
        }
    }

    /**
     * Query Refund
     *
     * @param \Order $order
     *
     * @return void
     *
     * @throws ParametersException
     * @throws RequestException
     * @throws \PrestaShopException
     */
    private function refund($order)
    {
        if (SettingsHelper::isRefundEnabledByState()) {
            try {
                $orderPayment = $this->orderHelper->ajaxGetOrderPayment($order);
                $idPayment = $orderPayment->transaction_id;
                $this->orderHelper->checkIfIsOrderAlma($order);

                $this->alma->payments->refund($idPayment, true);
            } catch (RequestError $e) {
                $msg = "[Alma] ERROR when creating refund for Order {$order->id}: {$e->getMessage()}";
                Logger::instance()->error($msg);

                return;
            } catch (OrderException $e) {
                $msg = "[Alma] ERROR Refund Order {$order->id}: {$e->getMessage()}";
                Logger::instance()->error($msg);
            }
        }
    }

    /**
     * Query Trigger Payment
     *
     * @param \Order $order
     *
     * @return void
     *
     * @throws \PrestaShopException
     */
    private function triggerPayment($order)
    {
        if ($this->settingsHelper->isPaymentTriggerEnabledByState()) {
            try {
                $orderPayment = $this->orderHelper->ajaxGetOrderPayment($order);
                $idPayment = $orderPayment->transaction_id;
                $this->orderHelper->checkIfIsOrderAlma($order);

                $this->alma->payments->trigger($idPayment);
            } catch (RequestError $e) {
                $msg = "[Alma] ERROR when creating trigger for Order {$order->id}: {$e->getMessage()}";
                Logger::instance()->error($msg);

                return;
            } catch (OrderException $e) {
                $msg = "[Alma] ERROR Trigger Order {$order->id}: {$e->getMessage()}";
                Logger::instance()->error($msg);
            }
        }
    }
}
