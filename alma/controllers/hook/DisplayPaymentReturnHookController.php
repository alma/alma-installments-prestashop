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

use Alma\API\RequestError;
use Alma\PrestaShop\Exceptions\ClientException;
use Alma\PrestaShop\Exceptions\OrderException;
use Alma\PrestaShop\Factories\LoggerFactory;
use Alma\PrestaShop\Helpers\OrderHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Model\ClientModel;

class DisplayPaymentReturnHookController extends FrontendHookController
{
    /** @var \Alma */
    public $module;
    /**
     * @var \Alma\PrestaShop\Helpers\OrderHelper
     */
    protected $orderHelper;
    /**
     * @var \Alma\PrestaShop\Model\ClientModel
     */
    protected $clientModel;

    /**
     * DisplayPaymentReturnHookController constructor.
     *
     * @param $module
     */
    public function __construct($module)
    {
        parent::__construct($module);
        $this->orderHelper = new OrderHelper();
        $this->clientModel = new ClientModel();
    }

    /**
     * @param $params
     *
     * @return false|string
     */
    public function run($params)
    {
        $this->context->controller->addCSS($this->module->_path . 'views/css/alma.css', 'all');
        $displayPaymentReturn = false;
        $almaPaymentId = null;

        try {
            $order = array_key_exists('objOrder', $params) ? $params['objOrder'] : $params['order'];
            /** @var \OrderPayment $orderPayment */
            $orderPayment = $this->orderHelper->getOrderPayment($order);
            $almaPaymentId = $orderPayment->transaction_id;
            if (!$almaPaymentId) {
                $msg = '[Alma] Payment_id not found';
                LoggerFactory::instance()->warning($msg);
                throw new OrderException($msg);
            }
            $payment = $this->clientModel->getClient()->payments->fetch($almaPaymentId);
            $this->context->smarty->assign([
                'order_reference' => $order->reference,
                'payment_order' => $orderPayment,
                'payment' => $payment,
            ]);

            $displayPaymentReturn = $this->module->display($this->module->file, 'displayPaymentReturn.tpl');
        } catch (OrderException $e) {
            LoggerFactory::instance()->warning(sprintf('[Alma] DisplayPaymentReturn Error fetching payment for order %s: %s', json_encode($order), $e->getMessage()));
        } catch (ClientException $e) {
            LoggerFactory::instance()->warning("[Alma] DisplayPaymentReturn Error fetching client with payment ID {$almaPaymentId}: {$e->getMessage()}");
        } catch (RequestError $e) {
            LoggerFactory::instance()->warning("[Alma] DisplayPaymentReturn Error fetching payment with ID {$almaPaymentId}: {$e->getMessage()}");
        }

        return $displayPaymentReturn;
    }
}
