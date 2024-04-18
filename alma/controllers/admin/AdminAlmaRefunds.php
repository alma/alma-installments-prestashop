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
use Alma\API\Entities\Payment;
use Alma\API\Exceptions\ParametersException;
use Alma\API\Exceptions\RequestException;
use Alma\API\RequestError;
use Alma\PrestaShop\Helpers\ClientHelper;
use Alma\PrestaShop\Helpers\CurrencyHelper;
use Alma\PrestaShop\Helpers\OrderHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\RefundHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Traits\AjaxTrait;
use PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminAlmaRefundsController extends ModuleAdminController
{
    use AjaxTrait;

    /**
     * If set to true, page content and messages will be encoded to JSON before responding to AJAX request.
     *
     * @var bool
     * @override
     */
    protected $json = true;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        parent::__construct();
        $this->priceHelper = new PriceHelper(new ToolsHelper(), new CurrencyHelper());
    }

    /**
     * Make refund over ajax request and display json on std output.
     *
     * @return void
     *
     * @throws LocalizationException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws RequestException
     * @throws \Alma\PrestaShop\Exceptions\OrderException
     */
    public function ajaxProcessRefund()
    {
        $refundType = Tools::getValue('refundType');
        $order = new Order(Tools::getValue('orderId'));
        $orderHelper = new OrderHelper();
        $orderPayment = $orderHelper->ajaxGetOrderPayment($order);
        $paymentId = $orderPayment->transaction_id;

        $isTotal = $this->isTotalRefund($refundType);
        $amount = $this->getRefundAmount($refundType, $order);

        $refundResult = false;
        try {
            $refundResult = $this->runRefund($paymentId, $amount, $isTotal);
        } catch (RequestError $e) {
            $msg = "[Alma] ERROR when creating refund for Order {$order->id}: {$e->getMessage()}";
            Logger::instance()->error($msg);
        }

        if (false === $refundResult) {
            $this->ajaxFailAndDie(
                $this->module->l('There was an error while processing the refund', 'AdminAlmaRefunds')
            );
        }
        $totalOrderAmount = $refundResult->purchase_amount;
        $idCurrency = (int) $order->id_currency;
        $totalOrderPrice = $this->priceHelper->formatPriceToCentsByCurrencyId($totalOrderAmount, $idCurrency);
        $totalRefundAmount = RefundHelper::buildTotalRefund($refundResult->refunds, $totalOrderAmount);
        $totalRefundPrice = $this->priceHelper->formatPriceToCentsByCurrencyId($totalRefundAmount, $idCurrency);
        $percentRefund = PriceHelper::calculatePercentage($totalRefundAmount, $totalOrderAmount);

        if ($isTotal) {
            $this->setOrdersAsRefund($order);
        }

        $this->ajaxRenderAndExit(json_encode([
            'success' => true,
            'message' => $this->module->l('Refund has been processed', 'AdminAlmaRefunds'),
            'paymentData' => $refundResult,
            'percentRefund' => $percentRefund,
            'totalRefundAmount' => $totalRefundAmount,
            'totalRefundPrice' => $totalRefundPrice,
            'totalOrderAmount' => $totalOrderAmount,
            'totalOrderPrice' => $totalOrderPrice,
        ]));
    }

    /**
     * @param string $paymentId
     * @param float $amount
     * @param bool $isTotal
     *
     * @return Payment|false
     *
     * @throws PrestaShopException
     * @throws RequestError
     * @throws RequestException
     */
    protected function runRefund($paymentId, $amount, $isTotal)
    {
        $alma = ClientHelper::defaultInstance();
        if (!$alma) {
            return false;
        }

        try {
            return $alma->payments->refund($paymentId, $isTotal, $this->priceHelper->convertPriceToCents($amount));
        } catch (ParametersException $e) {
            Logger::instance()->error(
                sprintf('Message :%s - Trace: %s', $e->getMessage(), $e->getTraceAsString())
            );
            $this->ajaxFailAndDie(
                $this->module->l($e->getMessage(), 'AdminAlmaRefunds')
            );
        }
    }

    /**
     * Change Order status if refund is total.
     *
     * @param Order $order
     *
     * @return void
     */
    private function setOrdersAsRefund($order)
    {
        $orders = Order::getByReference($order->reference);
        foreach ($orders as $o) {
            $currentOrderState = $o->getCurrentOrderState();
            if ($currentOrderState->id !== (int) Configuration::get('PS_OS_REFUND')) {
                $o->setCurrentState(Configuration::get('PS_OS_REFUND'));
            }
        }
    }

    /**
     * Bool if refund is total.
     *
     * @param string $refundType
     *
     * @return bool
     */
    private function isTotalRefund($refundType)
    {
        if ('total' === $refundType) {
            return true;
        }

        return false;
    }

    /**
     * Amount of refund.
     *
     * @param string $refundType
     * @param Order $order
     *
     * @return float
     *
     * @throws PrestaShopException
     */
    private function getRefundAmount($refundType, $order)
    {
        switch ($refundType) {
            case 'partial_multi':
                return $order->total_paid_tax_incl;
            case 'partial':
                return $this->amountPartialRefund($order);
            case 'total':
                return $order->getOrdersTotalPaid();
            default:
                $this->undefinedRefundType($refundType);
        }
    }

    /**
     * Get the amount for a partial refund.
     *
     * @param Order $order
     *
     * @return float
     *
     * @throws PrestaShopException
     */
    protected function amountPartialRefund($order)
    {
        $amount = (float) str_replace(',', '.', Tools::getValue('amount'));

        if ($amount > $order->getOrdersTotalPaid()) {
            $this->ajaxFailAndDie(
                $this->module->l('Error: Amount is higher than maximum refundable', 'AdminAlmaRefunds'),
                400
            );
        }

        return $amount;
    }

    /**
     * @param string $refundType
     *
     * @return void
     *
     * @throws PrestaShopException
     */
    protected function undefinedRefundType($refundType)
    {
        $msg = sprintf(
            $this->module->l('Error: unknown refund type (%s)', 'AdminAlmaRefunds'),
            $refundType
        );

        Logger::instance()->error($msg);
        $this->ajaxFailAndDie($msg, 400);
    }
}
