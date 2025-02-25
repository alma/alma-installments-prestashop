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

use Alma\API\Client;
use Alma\API\Entities\Insurance\Contract;
use Alma\API\Exceptions\AlmaException;
use Alma\API\Exceptions\InsuranceCancelPendingException;
use Alma\API\RequestError;
use Alma\PrestaShop\Builders\Helpers\CartHelperBuilder;
use Alma\PrestaShop\Exceptions\InsurancePendingCancellationException;
use Alma\PrestaShop\Exceptions\InsuranceSubscriptionException;
use Alma\PrestaShop\Exceptions\SubscriptionException;
use Alma\PrestaShop\Factories\LoggerFactory;
use Alma\PrestaShop\Helpers\CartHelper;
use Alma\PrestaShop\Helpers\ClientHelper;
use Alma\PrestaShop\Helpers\ProductHelper;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;

class InsuranceApiService
{
    /**
     * @var Client|mixed|null
     */
    protected $almaApiClient;

    /**
     * @var \ContextCore
     */
    protected $context;

    /**
     * @var CartHelper
     */
    protected $cartHelper;
    /**
     * @var ProductHelper
     */
    protected $productHelper;
    /**
     * @var AlmaInsuranceProductRepository
     */
    protected $insuranceProductRepository;

    public function __construct()
    {
        $this->almaApiClient = ClientHelper::defaultInstance();
        $this->context = \Context::getContext();

        $cartHelperBuilder = new CartHelperBuilder();
        $this->cartHelper = $cartHelperBuilder->getInstance();

        $this->productHelper = new ProductHelper();

        $this->insuranceProductRepository = new AlmaInsuranceProductRepository();
    }

    /**
     * Used for Unit Test
     *
     * @param $client
     *
     * @return void
     */
    public function setPhpClient($client)
    {
        $this->almaApiClient = $client;
    }

    /**
     * @param $insuranceContractId
     * @param $cmsReference
     * @param $productPrice
     *
     * @return array|null
     */
    public function getInsuranceContractFiles($insuranceContractId, $cmsReference, $productPrice)
    {
        try {
            $filesByType = [];
            $files = $this->almaApiClient->insurance->getInsuranceContract(
                $insuranceContractId,
                $cmsReference,
                $productPrice,
                $this->context->cookie->checksum,
                $this->cartHelper->getCartIdFromContext()
            )->getFiles();

            foreach ($files as $file) {
                $filesByType[$file->getType()] = $file->getPublicUrl();
            }

            return $filesByType;
        } catch (\Exception  $e) {
            LoggerFactory::instance()->error(
                sprintf(
                    '[Alma] Impossible to retrieve insurance contract files, message "%s", trace "%s"',
                    $e->getMessage(),
                    $e->getTraceAsString()
                )
            );
        }

        return null;
    }

    /**
     * @param int $insuranceContractId
     * @param string $cmsReference
     * @param int $productPrice
     *
     * @return Contract|null
     */
    public function getInsuranceContract($insuranceContractId, $cmsReference, $productPrice)
    {
        try {
            return $this->almaApiClient->insurance->getInsuranceContract(
                $insuranceContractId,
                $cmsReference,
                $productPrice,
                $this->context->cookie->checksum,
                $this->cartHelper->getCartIdFromContext()
            );
        } catch (\Exception $e) {
            LoggerFactory::instance()->error(
                sprintf(
                    '[Alma] Impossible to retrieve insurance contract, message "%s", trace "%s"',
                    $e->getMessage(),
                    $e->getTraceAsString()
                )
            );
        }

        return null;
    }

    /**
     * @param array $subscriptionData
     * @param \OrderCore $order
     * @param \OrderPayment|false $orderPayment
     *
     * @return array
     *
     * @throws InsuranceSubscriptionException
     */
    public function subscribeInsurance($subscriptionData, $order, $orderPayment = false)
    {
        $idTransaction = $orderPayment ? $orderPayment->transaction_id : null;

        try {
            $result = $this->almaApiClient->insurance->subscription(
                $subscriptionData,
                $order->id,
                $idTransaction,
                $this->context->cookie->checksum,
                $order->id_cart
            );

            if (isset($result['subscriptions'])) {
                return $result['subscriptions'];
            }
        } catch (\Exception  $e) {
            LoggerFactory::instance()->error(
                sprintf(
                    '[Alma] Error when subscribing insurance contract, message "%s", trace "%s", subscriptionData : "%s", idTransaction : "%s"',
                    $e->getMessage(),
                    $e->getTraceAsString(),
                    json_encode($subscriptionData),
                    $idTransaction
                )
            );
        }

        throw new InsuranceSubscriptionException();
    }

    /**
     * @param $cart
     *
     * @return false|void
     */
    public function sendCmsReferenceSubscribedForTracking($cart)
    {
        $cmsReferences = $this->productHelper->getCmsReferencesByCart($cart);

        if (empty($cmsReferences)) {
            LoggerFactory::instance()->warning(
                sprintf(
                    '[Alma] No cms reference returned, cart: "%s"',
                    json_encode($cart)
                )
            );

            return false;
        }

        try {
            $this->almaApiClient->insurance->sendCustomerCart($cmsReferences, $cart->id);
        } catch (RequestError $e) {
            LoggerFactory::instance()->error(
                sprintf(
                    '[Alma] Error while sending the cms_reference for tracking, message "%s", trace "%s", cmsReference : "%s", cartId: "%s"',
                    $e->getMessage(),
                    $e->getTraceAsString(),
                    json_encode($cmsReferences),
                    $cart->id
                )
            );
        }
    }

    /**
     * @param $sid
     *
     * @return array
     *
     * @throws SubscriptionException
     */
    public function getSubscriptionById($sid)
    {
        try {
            $subscriptionArray = $this->almaApiClient->insurance->getSubscription(['id' => $sid]);

            return $subscriptionArray['subscriptions'][0];
        } catch (AlmaException $e) {
            throw new SubscriptionException('Impossible to get subscription');
        }
    }

    /**
     * @param string $sid
     *
     * @return void
     *
     * @throws InsurancePendingCancellationException
     * @throws InsuranceSubscriptionException
     */
    public function cancelSubscription($sid)
    {
        try {
            $this->almaApiClient->insurance->cancelSubscription($sid);
        } catch (InsuranceCancelPendingException $e) {
            throw new InsurancePendingCancellationException('Pending cancellation', 410);
        } catch (AlmaException $e) {
            throw new InsuranceSubscriptionException('Impossible to cancel subscription', 500);
        }
    }
}
