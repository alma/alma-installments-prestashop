<?php

namespace Alma\PrestaShop\Controllers\Hook;

use Alma\API\Client;
use Alma\API\Exceptions\AlmaException;
use Alma\PrestaShop\Exceptions\AlmaActionObjectUpdateException;
use Alma\PrestaShop\Exceptions\ClientException;
use Alma\PrestaShop\Factories\CarrierFactory;
use Alma\PrestaShop\Factories\OrderFactory;
use Alma\PrestaShop\Helpers\ClientHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Logger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ActionObjectUpdateAfter
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var ClientHelper
     */
    private $clientHelper;

    /**
     * @var CarrierFactory
     */
    private $carrierFactory;

    public function __construct($orderFactory, $clientHelper, $carrierFactory)
    {
        $this->orderFactory = $orderFactory;
        $this->clientHelper = $clientHelper;
        $this->carrierFactory = $carrierFactory;
    }

    /**
     * Send tracking information to Alma only for Alma Orders
     *
     * @param $params
     *
     * @return void
     */
    public function run($params)
    {
        try {
            $orderCarrier = $this->checkParamsContainValidOrderCarrierObject($params);
            $carrier = $this->carrierFactory->create($orderCarrier->id_carrier);
            $order = $this->getAlmaOrderFromOrderCarrierOrderId($orderCarrier->id_order);
            $almaPaymentExternalId = $this->getAlmaPaymentExternalId($order);
            $almaClient = $this->getAlmaClient();
            $orderExternalId = $this->getOrCreateAlmaOrderExternalId($almaClient, $order, $almaPaymentExternalId);
            $almaClient->orders->addTracking($orderExternalId, $carrier->name, $orderCarrier->tracking_number, $carrier->url);
        } catch (AlmaActionObjectUpdateException $e) {
            return;
        } catch (AlmaException $e) {
            Logger::instance()->error('[Alma] - Add tracking error: ' . $e->getMessage());
        }
    }

    /**
     * Check if params Object is set and is an OrderCarrier
     *
     * @param $params
     *
     * @return \OrderCarrierCore $orderCarrier
     *
     * @throws AlmaActionObjectUpdateException
     */
    private function checkParamsContainValidOrderCarrierObject($params)
    {
        if (
            !isset($params['object']) ||
            !($params['object'] instanceof \OrderCarrierCore) ||
            $params['object']->tracking_number === ''
        ) {
            throw new AlmaActionObjectUpdateException('Object is not an OrderCarrier');
        }

        return $params['object'];
    }

    /**
     * Get Alma Order from OrderCarrier OrderId
     * Throw Exception for no Alma Order
     *
     * @param $orderId
     *
     * @return \Order
     *
     * @throws AlmaActionObjectUpdateException
     */
    private function getAlmaOrderFromOrderCarrierOrderId($orderId)
    {
        try {
            $order = $this->orderFactory->create($orderId);
        } catch (\PrestaShopException $e) {
            Logger::instance()->error('[Alma] - PrestaShopException - Impossible to get Order with id :' . $orderId);
            throw new AlmaActionObjectUpdateException('Impossible to get Order');
        }
        if ($order->module != ConstantsHelper::ALMA_MODULE_NAME) {
            throw new AlmaActionObjectUpdateException('Order is not an Alma Order');
        }

        return $order;
    }

    /**
     * Get Alma Payment External Id from Order
     * Throw Exception if no Payment or no Alma Payment External Id in Order
     *
     * @param \Order $order
     *
     * @return string
     *
     * @throws AlmaActionObjectUpdateException
     */
    private function getAlmaPaymentExternalId($order)
    {
        if (empty($order->getOrderPayments())) {
            throw new AlmaActionObjectUpdateException('Order is not an Alma Order');
        }
        foreach ($order->getOrderPayments() as $orderPayment) {
            /** @var \OrderPayment $orderPayment */
            if (isset($orderPayment->transaction_id)) {
                return $orderPayment->transaction_id;
            }
        }
        Logger::instance()->error('[Alma] - No Alma Payment External Id in order ' . $order->reference);
        throw new AlmaActionObjectUpdateException('No Alma Payment External Id');
    }

    /**
     * Get Alma Client or throw Exception
     *
     * @return Client
     *
     * @throws AlmaActionObjectUpdateException
     */
    private function getAlmaClient()
    {
        try {
            return $this->clientHelper->getAlmaClient();
        } catch (ClientException $e) {
            Logger::instance()->error('[Alma] - ClientException - ' . $e->getMessage());
            throw new AlmaActionObjectUpdateException('Impossible to get Alma Client');
        }
    }

    /**
     * Get or Create Alma Order External Id
     *
     * @param $almaClient
     * @param $order
     * @param $almaPaymentExternalId
     *
     * @return mixed|null
     *
     * @throws AlmaActionObjectUpdateException
     */
    private function getOrCreateAlmaOrderExternalId($almaClient, $order, $almaPaymentExternalId)
    {
        try {
            $almaPayment = $almaClient->payments->fetch($almaPaymentExternalId);

            $orderExternalId = null;
            foreach ($almaPayment->orders as $almaOrder) {
                if ($order->reference === $almaOrder->getMerchantReference()) {
                    $orderExternalId = $almaOrder->getExternalId();
                }
            }
            if (!isset($orderExternalId)) {
                $almaOrder = $almaClient->payments->addOrder($almaPaymentExternalId, [
                        'merchant_reference' => $order->reference,
                    ]
                );
                $orderExternalId = $almaOrder->getExternalId();
            }

            return $orderExternalId;
        } catch (AlmaException $e) {
            Logger::instance()->error('[Alma] - AlmaException ' . $e->getMessage());
            throw new AlmaActionObjectUpdateException('Impossible to get or create Alma Order');
        }
    }
}
