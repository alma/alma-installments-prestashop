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

namespace Alma\PrestaShop\Services;

use Alma\API\Exceptions\ParametersException;
use Alma\API\Exceptions\RequestException;
use Alma\API\RequestError;
use Alma\PrestaShop\Exceptions\ClientException;
use Alma\PrestaShop\Exceptions\OrderServiceException;
use Alma\PrestaShop\Factories\LoggerFactory;
use Alma\PrestaShop\Helpers\ClientHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderService
{
    /**
     * @var ClientHelper
     */
    private $clientHelper;
    /**
     * @var LoggerFactory|mixed
     */
    private $logger;

    /**
     * @param ClientHelper $clientHelper
     */
    public function __construct($clientHelper)
    {
        $this->clientHelper = $clientHelper;
        $this->logger = LoggerFactory::instance();
    }

    /**
     * Manage send at order status update
     *
     * @param \Order $order
     * @param \OrderState $orderState
     *
     * @return void
     */
    public function manageStatusUpdate($order, $orderState)
    {
        if ($order->module !== 'alma') {
            return;
        }

        try {
            $stateName = $this->getStateName($orderState);
            $paymentTransactionId = $this->getPaymentTransactionId($order);
            $this->sendStatus(
                $paymentTransactionId,
                $order->reference,
                $stateName,
                $orderState->shipped
            );
        } catch (OrderServiceException $e) {
            $this->logger->warning('Impossible to update order status: ' . $e->getMessage());

            return;
        }
    }

    /**
     * Call the clientHelper to send the order status and handle the exceptions
     *
     * @param $paymentTransactionId
     * @param $reference
     * @param $stateName
     * @param $shipped
     *
     * @return void
     *
     * @throws OrderServiceException
     */
    private function sendStatus($paymentTransactionId, $reference, $stateName, $shipped)
    {
        try {
            $this->clientHelper->sendOrderStatus($paymentTransactionId, $reference, $stateName, $shipped);

            return;
        } catch (ClientException $e) {
            $errorMessage = $e->getMessage();
        } catch (ParametersException $e) {
            $errorMessage = $e->getMessage();
        } catch (RequestException $e) {
            $errorMessage = $e->getMessage();
        } catch (RequestError $e) {
            $errorMessage = $e->getMessage();
        }
        $this->logger->warning('Error while sending order status: ' . $errorMessage);
        throw new OrderServiceException('Error while sending order status');
    }

    /**
     * Get the order state name
     *
     * @param \OrderState $orderState
     *
     * @return mixed
     *
     * @throws OrderServiceException
     */
    private function getStateName($orderState)
    {
        try {
            return $orderState->getFieldByLang('name');
        } catch (\PrestaShopException $e) {
            $this->logger->warning('Error while getting order state name: ' . $e->getMessage());
            throw new OrderServiceException('Error while getting order state name');
        }
    }

    /**
     * Extract Alma payment Id from Order transaction_id property
     *
     * @param \Order $order
     *
     * @return string
     *
     * @throws OrderServiceException
     */
    private function getPaymentTransactionId($order)
    {
        /** @var \OrderPayment $payments */
        $payments = $order->getOrderPayments();

        if (count($payments) === 0) {
            throw new OrderServiceException(sprintf('No payment found for order "%s"', $order->id));
        }
        if (empty($payments[0]->transaction_id)) {
            throw new OrderServiceException(sprintf('No transaction ID found for order "%s"', $order->id));
        }

        return $payments[0]->transaction_id;
    }

    /**
     * You can set a custom logger generally use for testing purpose
     *
     * @param LoggerFactory $logger
     *
     * @return void
     */
    public function setCustomLogger($logger)
    {
        $this->logger = $logger;
    }
}
