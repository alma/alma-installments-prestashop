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

use Alma\API\Endpoints\Results\Eligibility;
use Alma\API\Entities\DTO\MerchantBusinessEvent\CartInitiatedBusinessEvent;
use Alma\API\Entities\DTO\MerchantBusinessEvent\OrderConfirmedBusinessEvent;
use Alma\API\Exceptions\ParametersException;
use Alma\PrestaShop\Exceptions\ClientException;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Model\AlmaBusinessDataModel;
use Alma\PrestaShop\Model\ClientModel;
use Alma\PrestaShop\Repositories\AlmaBusinessDataRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AlmaBusinessDataService
{
    /**
     * @var \Alma\PrestaShop\Model\AlmaBusinessDataModel
     */
    private $almaBusinessDataModel;
    /**
     * @var \Alma\PrestaShop\Model\ClientModel
     */
    private $clientModel;
    /**
     * @var \Alma\PrestaShop\Logger|mixed
     */
    private $logger;
    /**
     * @var \Alma\PrestaShop\Repositories\AlmaBusinessDataRepository|mixed|null
     */
    private $almaBusinessDataRepository;

    public function __construct(
        $clientModel = null,
        $logger = null,
        $almaBusinessDataModel = null,
        $almaBusinessDataRepository = null
    ) {
        if (!$clientModel) {
            $clientModel = new ClientModel();
        }
        $this->clientModel = $clientModel;
        if (!$logger) {
            $logger = Logger::instance();
        }
        $this->logger = $logger;
        if (!$almaBusinessDataModel) {
            $almaBusinessDataModel = new AlmaBusinessDataModel();
        }
        $this->almaBusinessDataModel = $almaBusinessDataModel;
        if (!$almaBusinessDataRepository) {
            $almaBusinessDataRepository = new AlmaBusinessDataRepository();
        }
        $this->almaBusinessDataRepository = $almaBusinessDataRepository;
    }

    /**
     * Update order id in alma_business_data table
     * Send OrderConfirmedBusinessEvent to Alma
     *
     * @param int $orderId
     * @param int $cartId
     *
     * @return void
     */
    public function runOrderConfirmedBusinessEvent($orderId, $cartId)
    {
        $this->updateOrderId($orderId, $cartId);
        $almaBusinessData = $this->almaBusinessDataModel->getByCartId($cartId);
        $isPayNow = ConfigurationHelper::isPayNowStatic($almaBusinessData['plan_key']);
        $isBNPL = !empty($almaBusinessData['plan_key']) && !$isPayNow;

        try {
            $orderConfirmedBusinessEvent = new OrderConfirmedBusinessEvent(
                $isPayNow,
                $isBNPL,
                (bool) $almaBusinessData['is_bnpl_eligible'],
                !empty($orderId) ? (string) $orderId : null,
                !empty($cartId) ? (string) $cartId : null,
                $almaBusinessData['alma_payment_id'] ?: null
            );
            $this->clientModel->sendOrderConfirmedBusinessEvent($orderConfirmedBusinessEvent);
        } catch (ParametersException $e) {
            $this->logger->error('[Alma] Error in OrderConfirmedBusinessEvent constructor: ' . $e->getMessage());
        } catch (ClientException $e) {
            $this->logger->error('[Alma] Error Alma Client: ' . $e->getMessage());
        }
    }

    /**
     * Send CartInitiatedBusinessEvent to Alma
     *
     * @param $cartId
     *
     * @return void
     */
    public function runCartInitiatedBusinessEvent($cartId)
    {
        try {
            $cartInitiatedBusinessEvent = new CartInitiatedBusinessEvent($cartId);
            $this->clientModel->sendCartInitiatedBusinessEvent($cartInitiatedBusinessEvent);
            $this->almaBusinessDataModel->id_cart = $cartId;
            $this->almaBusinessDataModel->add();
        } catch (ParametersException $e) {
            $this->logger->error('[Alma] Error in CartInitiatedBusinessEvent constructor: ' . $e->getMessage());
        } catch (ClientException $e) {
            $this->logger->error('[Alma] Error Alma Client: ' . $e->getMessage());
        } catch (\PrestaShopDatabaseException $e) {
            $this->logger->error('[Alma] Error in PrestaShopDatabaseException : ' . $e->getMessage());
        } catch (\PrestaShopException $e) {
            $this->logger->error('[Alma] Error in PrestaShopException : ' . $e->getMessage());
        }
    }

    /**
     * @param $cartId
     *
     * @return bool
     */
    public function isAlmaBusinessDataExistByCart($cartId)
    {
        return !empty($this->almaBusinessDataModel->getByCartId($cartId));
    }

    /**
     * Return bool if the plans are eligible for BNPL without Pay Now
     *
     * @param Eligibility $plans
     *
     * @return void
     */
    public function saveIsBnplEligible($plans, $cartId)
    {
        $planKeys = [];
        $isEligible = false;

        foreach ($plans as $plan) {
            /** @var Eligibility $plan */
            if ($plan->isEligible) {
                $planKeys[] = SettingsHelper::keyForInstallmentPlanStatic($plan);
            }
        }
        $planKeysWithoutPayNow = array_filter($planKeys, function ($key) {
            return !ConfigurationHelper::isPayNowStatic($key);
        });

        if (count($planKeysWithoutPayNow) > 0) {
            $isEligible = true;
        }

        $this->updateIsBnplEligible($isEligible, $cartId);
    }

    /**
     * @param bool $isEligible
     * @param int $cartId
     *
     * @return void
     */
    public function updateIsBnplEligible($isEligible, $cartId)
    {
        $this->almaBusinessDataRepository->update('is_bnpl_eligible', $isEligible, 'id_cart = ' . $cartId);
    }

    /**
     * @param $planKey
     * @param $cartId
     *
     * @return void
     */
    public function updatePlanKey($planKey, $cartId)
    {
        $this->almaBusinessDataRepository->update('plan_key', $planKey, 'id_cart = ' . $cartId);
    }

    /**
     * @param $orderId
     * @param $cartId
     *
     * @return void
     */
    public function updateOrderId($orderId, $cartId)
    {
        $this->almaBusinessDataRepository->update('id_order', $orderId, 'id_cart = ' . $cartId);
    }

    /**
     * @param $almaPaymentId
     * @param $cartId
     *
     * @return void
     */
    public function updateAlmaPaymentId($almaPaymentId, $cartId)
    {
        $this->almaBusinessDataRepository->update('alma_payment_id', $almaPaymentId, 'id_cart = ' . $cartId);
    }
}
