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

namespace Alma\PrestaShop\Services;

use Alma\API\Entities\Payment;
use Alma\PrestaShop\Exceptions\AlmaException;
use Alma\PrestaShop\Helpers\ClientHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderService
{
    /**
     * @var ClientHelper
     */
    protected $clientHelper;


    /**
     * @param ClientHelper $clientHelper
     */
    public function __construct($clientHelper)
    {
        $this->clientHelper = $clientHelper;
    }


    /**
     * @param \Order $order
     * @param \OrderState $orderState
     * @return void
     * @throws AlmaException
     * @throws \Alma\API\Exceptions\ParametersException
     * @throws \Alma\API\Exceptions\RequestException
     * @throws \Alma\API\RequestError
     * @throws \Alma\PrestaShop\Exceptions\ClientException
     */
   public function manageStatusUpdate($order, $orderState)
   {
       $paymentTransactionId = $this->getPaymentTransactionId($order);
       $almaPayment = $this->getAlmaPayment($paymentTransactionId, $order->reference);

       $this->clientHelper->sendOrderStatus($almaPayment->orders[0]->id, [
           'status' => $orderState->name,
           'is_shipped' => (bool)$orderState->shipped
       ]);
   }

    /**
     * @param \Order $order
     * @return string
     * @throws AlmaException
     */
   public function getPaymentTransactionId($order)
   {
       // Retrieve the order Status from Alma
       $payments = $order->getOrderPayments();

       if (count($payments) === 0) {
           throw new AlmaException(sprintf('No payment found for order "%s"', $order->id));
       }

       return $payments[0]->transaction_id;
   }

    /**
     * @param string $paymentTransactionId
     * @return Payment
     * @throws AlmaException
     * @throws \Alma\API\RequestError
     * @throws \Alma\PrestaShop\Exceptions\ClientException
     */

   public function getAlmaPayment($paymentTransactionId, $orderReference)
   {
       /**
        * @var Payment $almaPayment
        */
       $almaPayment = $this->clientHelper->getPaymentByTransactionId($paymentTransactionId);

       if (
           !$almaPayment
           || count($almaPayment->orders) == 0
           || !isset($almaPayment->orders[0]->id)
           || !isset($almaPayment->orders[0]->merchant_reference)
       ) {
           throw new AlmaException(sprintf('No alma payments found for transaction id "%s"', $paymentTransactionId));
       }

       $this->checkOrderReference($almaPayment->orders[0]->merchant_reference, $orderReference);

       return $almaPayment;
   }


    /**
     * @param string $merchantReference
     * @param string $orderReference
     * @return void
     * @throws AlmaException
     */
   public function checkOrderReference($merchantReference, $orderReference)
   {
        if($merchantReference !== $orderReference) {
            throw new AlmaException(
                sprintf(
                    'Merchant reference from Alma order "%s" does not match order reference "%s"',
                    $merchantReference,
                    $orderReference
                )
            );
        }
   }
}
