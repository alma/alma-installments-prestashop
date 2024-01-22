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

use Alma\PrestaShop\Helpers\OrderHelper;
use Alma\PrestaShop\Model\InsuranceProduct;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class InsuranceSubscriptionService
{
    /**
     * @var AlmaInsuranceProductRepository
     */
    protected $almaInsuranceProductRepository;
    /**
     * @var InsuranceService
     */
    protected $insuranceService;
    /**
     * @var OrderHelper
     */
    protected $orderHelper;
    /**
     * @var InsuranceApiService
     */
    protected $insuranceApiService;

    /**
     * @var \ContextCore
     */
    protected $context;

    public function __construct()
    {
        $this->almaInsuranceProductRepository = new AlmaInsuranceProductRepository();
        $this->insuranceService = new InsuranceService();
        $this->orderHelper = new OrderHelper();
        $this->insuranceApiService = new InsuranceApiService();
    }


    /**
     * @param \OrderCore $order
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    public function triggerInsuranceSubscription($order)
    {
        $cart = new \Cart((int) $order->id_cart);

        $insuranceContracts = $this->almaInsuranceProductRepository->getContractsInfosByIdCartAndIdShop(
            $order->id_cart,
            $order->id_shop
        );

        $subscriptionData = $this->insuranceService->createSubscriptionData($insuranceContracts, $cart);

        if (!empty($subscriptionData)) {
            $orderPayment = $this->orderHelper->getCurrentOrderPayment($order, false);

            // @todo exception
            $subscriptions = $this->insuranceApiService->subscribeInsurance($subscriptionData, $orderPayment->transaction_id);
            $this->confirmSubscriptions($order->id_cart, $order->id_shop, $subscriptions);
        }
    }

    protected function confirmSubscriptions($orderId, $shopId, $subscriptions)
    {
        foreach($subscriptions as $subscriptionData){
            $this->confirmSubscription($orderId, $shopId, $subscriptionData);
        }
    }

    protected function confirmSubscription($orderId, $shopId, $subscription)
    {

        $almaInsuranceProduct = $this->almaInsuranceProductRepository->findSubscriptionToActivate(
            $orderId,
            $shopId,
            $subscription['contract_id'],
            $subscription['cms_reference']
        );
        if(!$almaInsuranceProduct) {
            // @todo exectpion
        }

        $insuranceProduct = new InsuranceProduct($almaInsuranceProduct['id_alma_insurance_product']);
        $insuranceProduct->subscription_id = $subscription['subscription_id'];
        $insuranceProduct->state = InsuranceProduct::STATE_ACTIVE;
        $insuranceProduct->save();
    }

}
