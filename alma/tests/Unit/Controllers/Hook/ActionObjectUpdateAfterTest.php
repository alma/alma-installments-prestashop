<?php

namespace Alma\PrestaShop\Tests\Unit\Controllers\Hook;

use Alma\API\Client;
use Alma\API\Endpoints\Orders as OrdersEndpoint;
use Alma\API\Endpoints\Payments;
use Alma\API\Entities\Order;
use Alma\API\Entities\Payment;
use Alma\API\Exceptions\AlmaException;
use Alma\PrestaShop\Controllers\Hook\ActionObjectUpdateAfter;
use Alma\PrestaShop\Factories\CarrierFactory;
use Alma\PrestaShop\Factories\OrderFactory;
use Alma\PrestaShop\Helpers\ClientHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use PHPUnit\Framework\TestCase;

class ActionObjectUpdateAfterTest extends TestCase
{
    protected $ActionObjectUpdateAfter;
    protected $orderFactory;
    protected $carrierFactory;

    protected $clientHelper;
    protected $paymentEndpoint;
    protected $orderEndpoint;


    public function setUp()
    {
        $this->orderFactory = $this->createMock(OrderFactory::class);
        $this->carrierFactory = $this->createMock(CarrierFactory::class);
        $carrier = $this->createMock(\Carrier::class);
        $carrier->name = 'MyCarrierName';
        $carrier->url = 'myurl';
        $this->carrierFactory->method('create')->willReturn($carrier);
        $client = $this->createMock(Client::class);
        $this->paymentEndpoint = $this->createMock(Payments::class);
        $this->orderEndpoint = $this->createMock(OrdersEndpoint::class);
        $client->orders = $this->orderEndpoint;
        $client->payments = $this->paymentEndpoint;

        $this->clientHelper = $this->createMock(ClientHelper::class);
        $this->clientHelper->method('getAlmaClient')->willReturn($client);

        $this->ActionObjectUpdateAfter = new ActionObjectUpdateAfter($this->orderFactory, $this->clientHelper, $this->carrierFactory);
    }

    /**
     * @dataProvider badObjectProvider
     * @return void
     */
    public function testRunWithoutOrderCarrierObjectReturn($params)
    {
        $this->orderFactory->expects($this->never())->method('create');
        $this->assertNull($this->ActionObjectUpdateAfter->run($params));
    }

    public function testRunWithNonAlmaOrderReturn()
    {
        $this->orderFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn(new \Order());

        $this->assertNull($this->ActionObjectUpdateAfter->run($this->paramsWithOrderCarrierObject()));
    }

    public function testRunWithAlmaOrderWithoutPaymentsReturn()
    {
        $almaOrder = new \Order();
        $almaOrder->module = ConstantsHelper::ALMA_MODULE_NAME;
        $this->orderFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($almaOrder);

        $this->assertNull($this->ActionObjectUpdateAfter->run($this->paramsWithOrderCarrierObject()));
    }

    public function testRunWithAlmaOrderWithPaymentsWithoutAlmaExternalIdReturn()
    {
        $almaOrder = $this->createMock(\Order::class);
        $almaOrder->method('getOrderPayments')->willReturn([new \OrderPayment(), new \OrderPayment()]);
        $almaOrder->module = ConstantsHelper::ALMA_MODULE_NAME;
        $this->orderFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($almaOrder);
        $this->orderEndpoint->expects($this->never())->method('addTracking');
        $this->assertNull($this->ActionObjectUpdateAfter->run($this->paramsWithOrderCarrierObject()));
    }

    public function testRunWithAlmaPaymentNotFound()
    {
        $almaOrder = $this->createMock(\Order::class);
        $almaPayment = new \OrderPayment();
        $almaPayment->transaction_id = 'payment_123456';
        $almaOrder->method('getOrderPayments')->willReturn([new \OrderPayment(), $almaPayment]);
        $almaOrder->module = ConstantsHelper::ALMA_MODULE_NAME;
        $this->orderFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($almaOrder);

        $this->paymentEndpoint->method('fetch')->willThrowException(new AlmaException('Payment not found'));

        $this->orderEndpoint->expects($this->never())->method('addTracking');
        $this->assertNull($this->ActionObjectUpdateAfter->run($this->paramsWithOrderCarrierObject()));
    }

    public function testRunWithAlmaPaymentWithoutOrderCreateOrderAndSendShipping()
    {
        $prestashopOrder = $this->createMock(\Order::class);
        $prestashopOrder->reference = 'AZERTY';
        $almaPayment = new \OrderPayment();
        $almaPayment->transaction_id = 'payment_123456';
        $prestashopOrder->method('getOrderPayments')->willReturn([new \OrderPayment(), $almaPayment]);
        $prestashopOrder->module = ConstantsHelper::ALMA_MODULE_NAME;
        $this->orderFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($prestashopOrder);

        $this->paymentEndpoint->method('fetch')->willReturn($this->almaPaymentWithoutOrders());

        $almaOrder = $this->createMock(Order::class);
        $almaOrder->method('getExternalId')->willReturn('order_123');
        $this->paymentEndpoint
            ->expects($this->once())
            ->method('addOrder')
            ->with('payment_123456', ['merchant_reference' => 'AZERTY'])
            ->willReturn($almaOrder);
        $this->orderEndpoint
            ->expects($this->once())
            ->method('addTracking')
            ->with('order_123');
        $this->assertNull($this->ActionObjectUpdateAfter->run($this->paramsWithOrderCarrierObject()));
    }

    public function testRunWithAlmaPaymentWithOrderWithBadMerchantReferenceCreateOrderAndSendShipping()
    {
        $prestashopOrder = $this->createMock(\Order::class);
        $prestashopOrder->reference = 'AZERTY';
        $almaPayment = new \OrderPayment();
        $almaPayment->transaction_id = 'payment_123456';
        $prestashopOrder->method('getOrderPayments')->willReturn([new \OrderPayment(), $almaPayment]);
        $prestashopOrder->module = ConstantsHelper::ALMA_MODULE_NAME;
        $this->orderFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($prestashopOrder);

        $almaOrder = $this->createMock(Order::class);
        $almaOrder->method('getExternalId')->willReturn('order_123');
        $almaOrder->method('getMerchantReference')->willReturn('YTREZA');
        $almaPayment = $this->almaPaymentWithoutOrders();
        $almaPayment->orders = [$almaOrder];
        $this->paymentEndpoint->method('fetch')->willReturn($almaPayment);

        $this->paymentEndpoint
            ->expects($this->once())
            ->method('addOrder')
            ->with('payment_123456', ['merchant_reference' => 'AZERTY'])
            ->willReturn($almaOrder);
        $this->orderEndpoint
            ->expects($this->once())
            ->method('addTracking')
            ->with('order_123');
        $this->assertNull($this->ActionObjectUpdateAfter->run($this->paramsWithOrderCarrierObject()));
    }

    public function testRunWithAlmaPaymentWithOrdersWithMerchantReferenceNoCreateOrderAndSendShipping()
    {
        $prestashopOrder = $this->createMock(\Order::class);
        $prestashopOrder->reference = 'AZERTY';
        $almaPayment = new \OrderPayment();
        $almaPayment->transaction_id = 'payment_123456';
        $prestashopOrder->method('getOrderPayments')->willReturn([new \OrderPayment(), $almaPayment]);
        $prestashopOrder->module = ConstantsHelper::ALMA_MODULE_NAME;
        $this->orderFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($prestashopOrder);

        $almaOrder = $this->createMock(Order::class);
        $almaOrder->method('getExternalId')->willReturn('order_123');
        $almaOrder->method('getMerchantReference')->willReturn('YTREZA');

        $almaOrder2 = $this->createMock(Order::class);
        $almaOrder2->method('getExternalId')->willReturn('order_321');
        $almaOrder2->method('getMerchantReference')->willReturn('AZERTY');

        $almaPayment = $this->almaPaymentWithoutOrders();
        $almaPayment->orders = [$almaOrder,$almaOrder2];
        $this->paymentEndpoint->method('fetch')->willReturn($almaPayment);

        $this->paymentEndpoint
            ->expects($this->never())
            ->method('addOrder');
        $this->orderEndpoint
            ->expects($this->once())
            ->method('addTracking')
            ->with('order_321', 'MyCarrierName', 'track_1232', 'myurl');
        $this->assertNull($this->ActionObjectUpdateAfter->run($this->paramsWithOrderCarrierObject()));
    }

    /**
     * @return array
     */
    public function badObjectProvider()
    {
        return [
            'Null object' => [[]],
            'StdClass' => [['object' => new \stdClass()]],
            'Order' => [['object' => new \Order()]],
        ];
    }

    private function paramsWithOrderCarrierObject()
    {
        $orderCarrier = $this->createMock(\OrderCarrier::class);
        $orderCarrier->tracking_number = 'track_1232';
        return [
            'object' => $orderCarrier,
        ];
    }

    private function almaPaymentWithoutOrders()
    {
        $almaPayment = $this->createMock(Payment::class);
        $almaPayment->orders = [];
        return $almaPayment;
    }
}