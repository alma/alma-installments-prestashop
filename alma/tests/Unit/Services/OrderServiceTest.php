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

namespace Alma\PrestaShop\Tests\Unit\Services;

use Alma\API\Entities\Order;
use Alma\API\Entities\Payment;
use Alma\API\Exceptions\ParametersException;
use Alma\API\Exceptions\RequestException;
use Alma\PrestaShop\Builders\Services\OrderServiceBuilder;
use Alma\PrestaShop\Exceptions\AlmaException;
use Alma\PrestaShop\Helpers\ClientHelper;
use Alma\PrestaShop\Services\OrderService;
use PHPUnit\Framework\TestCase;

class OrderServiceTest extends TestCase
{
    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|\Order|(\Order&\Mockery\LegacyMockInterface)|(\Order&\Mockery\MockInterface)
     */
    protected $orderMock;

    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|\OrderState|(\OrderState&\Mockery\LegacyMockInterface)|(\OrderState&\Mockery\MockInterface)
     */
    protected $orderStateMock;

    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|\OrderState|(\OrderState&\Mockery\LegacyMockInterface)|(\OrderState&\Mockery\MockInterface)
     */
    protected $psPaymentMock;

    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|\OrderState|(\OrderState&\Mockery\LegacyMockInterface)|(\OrderState&\Mockery\MockInterface)
     */
    protected $clientHelperMock;

    /**
     * @var string
     */
    protected $orderReference;

    /**
     * @var OrderService
     */
    protected $orderService;

    public function setUp()
    {
        $this->orderMock = \Mockery::mock(\Order::class);
        $this->orderStateMock = \Mockery::mock(\OrderState::class)->makePartial();
        $this->orderStateMock->shouldReceive('getFieldByLang')->andReturn('orderState');

        $this->psPaymentMock = \Mockery::mock(Payment::class);
        $this->psPaymentMock->transaction_id = 'transactionId';

        $this->clientHelperMock = \Mockery::mock(ClientHelper::class);
        $this->orderReference = '123456';

        $orderServiceBuilder = new OrderServiceBuilder();
        $this->orderService = $orderServiceBuilder->getInstance();
    }

    public function testOrderNotAlma()
    {
        $this->orderMock->module = 'notAlma';

        $this->assertNull($this->orderService->manageStatusUpdate($this->orderMock, $this->orderStateMock));
    }

    /**
     * When I have no order payments
     * Then I except an AlmaException
     */
    public function testGetPaymentTransactionIdNotFound()
    {
        $this->orderMock->shouldReceive('getOrderPayments')->andReturn([]);
        $this->orderMock->id = 1;
        $this->orderMock->module = 'alma';

        $this->expectException(AlmaException::class);
        $this->expectExceptionMessage('No payment found for order "1"');
        $this->orderService->getPaymentTransactionId($this->orderMock);
    }

    /**
     * When I have order payments
     * Then I expect the transaction id
     */
    public function testGetPaymentTransactionIdFound()
    {
        $payment = new \stdClass();
        $payment->transaction_id = 'transactionId';

        $this->orderMock->shouldReceive('getOrderPayments')->andReturn([
            $payment,
        ]);
        $this->orderMock->id = 1;
        $this->orderMock->module = 'alma';

        $this->orderService->getPaymentTransactionId($this->orderMock);
        $this->assertEquals('transactionId', $this->orderService->getPaymentTransactionId($this->orderMock));
    }

    /**
     * When i want to retrieve alma payments with a wrong transaction id
     * Then i expect an Alma Exception
     */
    public function testGetAlmaPaymentNotFound()
    {
        $this->clientHelperMock->shouldReceive('getPaymentByTransactionId')->andReturn(null);

        $orderService = \Mockery::mock(OrderService::class, [$this->clientHelperMock])->makePartial();

        $this->expectException(AlmaException::class);
        $this->expectExceptionMessage('No alma payments found for transaction id "transactionId"');
        $orderService->getAlmaPayment('transactionId', $this->orderReference);
    }

    /**
     * When i want to retrieve alma payments
     * And The alma payment does not contains orders
     * Then i expect an Alma Exception
     */
    public function testGetAlmaPaymentNoOrders()
    {
        $almaPayment = new \stdClass();
        $almaPayment->orders = [];

        $this->clientHelperMock->shouldReceive('getPaymentByTransactionId')->andReturn($almaPayment);

        $orderService = \Mockery::mock(OrderService::class, [$this->clientHelperMock])->makePartial();

        $this->expectException(AlmaException::class);
        $this->expectExceptionMessage('No alma payments found for transaction id "transactionId"');
        $orderService->getAlmaPayment('transactionId', $this->orderReference);
    }

    /**
     * When i want to retrieve alma payments
     * And The alma payment does contains order with no id
     * Then i expect an Alma Exception
     */
    public function testGetAlmaPaymentNoIdOrders()
    {
        $order = new \stdClass();

        $almaPayment = new \stdClass();
        $almaPayment->orders = [$order];

        $this->clientHelperMock->shouldReceive('getPaymentByTransactionId')->andReturn($almaPayment);

        $orderService = \Mockery::mock(OrderService::class, [$this->clientHelperMock])->makePartial();

        $this->expectException(AlmaException::class);
        $this->expectExceptionMessage('No alma payments found for transaction id "transactionId"');
        $orderService->getAlmaPayment('transactionId', $this->orderReference);
    }

    /**
     * When i want to retrieve alma payments
     * And The alma payment does contains order with no order reference
     * Then i expect an Alma Exception
     */
    public function testGetAlmaPaymentNoReferenceOrders()
    {
        $order = new \stdClass();
        $order->id = '987654';

        $almaPayment = new \stdClass();
        $almaPayment->orders = [$order];

        $this->clientHelperMock->shouldReceive('getPaymentByTransactionId')->andReturn($almaPayment);

        $orderService = \Mockery::mock(OrderService::class, [$this->clientHelperMock])->makePartial();

        $this->expectException(AlmaException::class);
        $this->expectExceptionMessage('No alma payments found for transaction id "transactionId"');
        $orderService->getAlmaPayment('transactionId', $this->orderReference);
    }

    /**
     * When i want to retrieve alma payments
     * And The alma payment order has a wrong reference
     * Then i expect an Alma Exception
     */
    public function testGetAlmaPaymentWrongReferenceOrders()
    {
        $order = new \stdClass();
        $order->id = '987654';
        $order->merchant_reference = '987654';

        $almaPayment = new \stdClass();
        $almaPayment->orders = [$order];

        $this->clientHelperMock->shouldReceive('getPaymentByTransactionId')->andReturn($almaPayment);

        $orderService = \Mockery::mock(OrderService::class, [$this->clientHelperMock])->makePartial();
        $this->expectException(AlmaException::class);
        $this->expectExceptionMessage('Merchant reference from Alma order "987654" does not match order reference "123456"');
        $orderService->getAlmaPayment('transactionId', $this->orderReference);
    }

    /**
     * When i call the send status api with wrong data
     * Then i expect a Parameters exception
     */
    public function testManageStatusUpdateWithWrongData()
    {
        $order = new \stdClass();
        $order->id = 'OrderId';

        $almaPayment = new \stdClass();
        $almaPayment->orders = [$order];

        $this->clientHelperMock->shouldReceive('sendOrderStatus')->andThrow(new ParametersException());

        $orderService = \Mockery::mock(OrderService::class, [$this->clientHelperMock])->makePartial();
        $orderService->shouldReceive('getPaymentTransactionId')->andReturn('transactionId');
        $orderService->shouldReceive('getAlmaPayment')->andReturn($almaPayment);

        $this->orderMock->module = 'alma';

        $this->expectException(ParametersException::class);
        $orderService->manageStatusUpdate($this->orderMock, $this->orderStateMock);
    }

    /**
     * When i call the send status api and the request fails
     * Then i expect a Request exception
     */
    public function testManageStatusUpdateWithWrongQuery()
    {
        $order = new \stdClass();
        $order->id = 'OrderId';

        $almaPayment = new \stdClass();
        $almaPayment->orders = [$order];

        $this->clientHelperMock->shouldReceive('sendOrderStatus')->andThrow(new RequestException());

        $orderService = \Mockery::mock(OrderService::class, [$this->clientHelperMock])->makePartial();
        $orderService->shouldReceive('getPaymentTransactionId')->andReturn('transactionId');
        $orderService->shouldReceive('getAlmaPayment')->andReturn($almaPayment);

        $this->orderMock->module = 'alma';

        $this->expectException(RequestException::class);
        $orderService->manageStatusUpdate($this->orderMock, $this->orderStateMock);
    }

    public function testManageStatusUpdateWithFailure()
    {
        $this->clientHelperMock->shouldReceive('sendOrderStatus')->andReturn(new RequestException());
    }

    public function testManageStatusUpdateWithNoParamOrderState()
    {
        $this->orderMock = \Mockery::mock(\Order::class)->makePartial();
        $this->orderMock->shouldReceive('getCurrentOrderState')->andReturn($this->orderStateMock);

        $this->psPaymentMock = \Mockery::mock(Payment::class);
        $this->psPaymentMock->transaction_id = 'transactionId';

        $this->clientHelperMock = \Mockery::mock(ClientHelper::class);
        $this->orderReference = '123456';

        $orderServiceBuilder = new OrderServiceBuilder();
        $this->orderService = $orderServiceBuilder->getInstance();

        $this->assertNull($this->orderService->manageStatusUpdate($this->orderMock));
    }

    public function tearDown()
    {
        $this->orderMock = null;
        $this->orderStateMock = null;
        $this->psPaymentMock = null;
        $this->clientHelperMock = null;
        $this->orderService = null;
    }
}
