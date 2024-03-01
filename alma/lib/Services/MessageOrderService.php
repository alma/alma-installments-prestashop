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

use Alma\PrestaShop\Repositories\CustomerThreadRepository;
use Customer;
use CustomerMessage;
use CustomerThread;

if (!defined('_PS_VERSION_')) {
    exit;
}
class MessageOrderService
{
    /**
     * @var CustomerThreadRepository
     */
    protected $customerThreadRepository;
    /**
     * @var Customer
     */
    protected $customer;
    /**
     * @var CustomerThread
     */
    protected $customerThread;
    /**
     * @var CustomerMessage
     */
    protected $customerMessage;
    /**
     * @var \Context
     */
    protected $context;
    /**
     * @var \Alma
     */
    protected $module;
    /**
     * @var MessageOrderHelper
     */
    protected $messageOrderHelper;

    public function __construct(
        $idCustomer,
        $context,
        $module,
        $customerThread,
        $customerMessage,
        $customerThreadRepository
    ) {
        $this->customer = new Customer($idCustomer);
        $this->context = $context;
        $this->module = $module;
        $this->customerThread = $customerThread;
        $this->customerMessage = $customerMessage;
        $this->customerThreadRepository = $customerThreadRepository;
        $this->messageOrderHelper = new MessageOrderHelper(
            $this->context,
            $this->module,
            new InsuranceApiService()
        );
    }

    /**
     * @param $order
     * @param $almaInsuranceProduct
     *
     * @return void
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function insuranceCancelSubscription($order, $almaInsuranceProduct)
    {
        $idCustomerThread = $this->customerThreadRepository->getIdCustomerThreadByOrderId($order->id);
        $messageText = $this->messageOrderHelper->getMessageForRefundInsurance($almaInsuranceProduct);

        if (!$idCustomerThread) {
            $this->customerThread->id_contact = 0;
            $this->customerThread->id_customer = (int) $order->id_customer;
            $this->customerThread->id_shop = (int) $this->context->shop->id;
            $this->customerThread->id_product = $almaInsuranceProduct['id_product_insurance'];
            $this->customerThread->id_order = (int) $order->id;
            $this->customerThread->id_lang = (int) $this->context->language->id;
            $this->customerThread->email = $this->customer->email;
            $this->customerThread->status = 'open';
            $this->customerThread->token = \Tools::passwdGen(12);
            $this->customerThread->add();
        } else {
            $this->customerThread->id = (int) $idCustomerThread;
            $this->customerThread->status = 'open';
            $this->customerThread->update();
        }

        $this->customerMessage->id_customer_thread = $this->customerThread->id;
        $this->customerMessage->message = $messageText;
        $clientIpAddress = \Tools::getRemoteAddr();
        $this->customerMessage->ip_address = (int) ip2long($clientIpAddress);
        $this->customerMessage->add();
    }
}
