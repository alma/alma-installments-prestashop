<?php

namespace Alma\PrestaShop\Controllers\Hook;

use Alma\API\Exceptions\AlmaException;
use Alma\PrestaShop\Exceptions\ClientException;
use Alma\PrestaShop\Factories\CarrierFactory;
use Alma\PrestaShop\Factories\OrderFactory;
use Alma\PrestaShop\Helpers\ClientHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Logger;

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
     * @param $params
     * @return void
     */
    public function run($params)
    {
        Logger::instance()->info('[Alma] - Start Run');
        if (
            !isset($params['object']) ||
            !($params['object'] instanceof \OrderCarrierCore)
        ) {
            return;
        }
        Logger::instance()->info('[Alma] - it s OrderCarrierCore object');

        /** @var \OrderCarrier $orderCarrier */
        $orderCarrier = $params['object'];
        $idOrder = $orderCarrier->id_order;
        Logger::instance()->info('[Alma] - id order ' . $idOrder);

        /** @var \OrderCore $order */
        try {
            $order = $this->orderFactory->create($idOrder);
        } catch (\PrestaShopException $e) {
            Logger::instance()->info('[Alma] - PrestaShopException - Impossible to get Order with id :' . $idOrder);
            return;
        }
        if ($order->module != ConstantsHelper::ALMA_MODULE_NAME || empty($order->getOrderPayments())) {
            Logger::instance()->info('[Alma] - To Remove - Order is not Alma or payments are empty');
            Logger::instance()->info('[Alma] - order module' . $order->module);
            return;
        }

        foreach ($order->getOrderPayments() as $orderPayment) {
            /** @var \OrderPayment $orderPayment */
            if (isset($orderPayment->transaction_id)) {
                $almaPaymentExternalId = $orderPayment->transaction_id;
                break;
            }
        }
        if (!isset($almaPaymentExternalId)) {
            Logger::instance()->info('[Alma] - To Remove - No Alma Payment External Id');
            return;
        }

        try {
            $almaClient = $this->clientHelper->getAlmaClient();
        } catch (ClientException $e) {
            Logger::instance()->error('[Alma] - ClientException - ' . $e->getMessage());
            return;
        }

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
                        'merchant_reference' => $order->reference
                    ]
                );
                $orderExternalId = $almaOrder->getExternalId();
            }
            $carrier = $this->carrierFactory->create((int) $orderCarrier->id_carrier);
            $almaClient->orders->addTracking($orderExternalId, $carrier->name, $orderCarrier->tracking_number, $carrier->url);
        } catch (AlmaException $e) {
            Logger::instance()->error('[Alma] - AlmaException ' . $e->getMessage());
        }
    }
}