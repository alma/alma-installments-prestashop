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

namespace Alma\PrestaShop\Tests\Unit\Services;

use Alma\PrestaShop\Factories\LoggerFactory;
use Alma\PrestaShop\Helpers\ClientHelper;
use Alma\PrestaShop\Services\OrderService;
use PHPUnit\Framework\TestCase;

class OrderServiceTest extends TestCase
{
    private $clientHelperMock;
    private $loggerMock;
    private $orderStateMock;
    private $orderService;

    public function setUp()
    {
        $this->clientHelperMock = $this->createMock(ClientHelper::class);
        $this->loggerMock = $this->createMock(LoggerFactory::class);
        $this->orderStateMock = $this->createMock(\OrderStateCore::class);

        $this->orderService = new OrderService($this->clientHelperMock);
        $this->orderService->setCustomLogger($this->loggerMock);
    }

    public function tearDown()
    {
        $this->clientHelperMock = null;
        $this->orderService = null;
    }

    /**
     * Given no alma module
     * When manageStatusUpdate is called
     * Then direct return void
     */
    public function testNoAlmaModuleReturnVoid()
    {
        $order = new \OrderCore();
        $this->assertNull($this->orderService->manageStatusUpdate($order, $this->orderStateMock));
    }

    /**
     * Given an alma module
     * and no order state
     * When manageStatusUpdate is called
     * Then log a warning
     * and return void
     */
    public function testNoOrderStateReturnVoid()
    {
        $order = new \OrderCore();
        $order->module = 'alma';
        $this->loggerMock->expects($this->atLeast(1))->method('warning');
        $this->orderStateMock
            ->expects($this->once())
            ->method('getFieldByLang')
            ->willThrowException(new \PrestaShopException());
        $this->assertNull($this->orderService->manageStatusUpdate($order, $this->orderStateMock));
    }

    /**
     * Given an alma module and an order state
     * and no payment
     * When manageStatusUpdate is called
     * and return void
     */
    public function testNoPaymentReturnVoid()
    {
        $order = $this->createMock(\Order::class);
        $order->module = 'alma';
        $order->method('getOrderPayments')->willReturn([]);
        $this->orderStateWithStatus();
        $this->assertNull($this->orderService->manageStatusUpdate($order, $this->orderStateMock));
    }

    /**
     * Given an alma module and an order state
     * and a payment without transaction ID
     * When manageStatusUpdate is called
     * and return void
     */
    public function testNoTransactionIdReturnVoid()
    {
        $orderPayment = $this->createMock(\OrderPayment::class);
        $orderPayment->transaction_id = '';

        $order = $this->createMock(\Order::class);
        $order->module = 'alma';
        $order->method('getOrderPayments')->willReturn([$orderPayment]);
        $this->orderStateWithStatus();
        $this->assertNull($this->orderService->manageStatusUpdate($order, $this->orderStateMock));
    }

    /**
     * Given an alma module and an order state
     * and a payment with a transaction ID
     * When manageStatusUpdate is called
     * Then Call Client Helper SendOrderStatus with params
     */
    public function testSendOrderStatusIsCalledWithParams()
    {
        $orderPayment = $this->createMock(\OrderPayment::class);
        $orderPayment->transaction_id = 'payment_123456';

        $order = $this->createMock(\Order::class);
        $order->module = 'alma';
        $order->reference = 'my_reference';
        $order->method('getOrderPayments')->willReturn([$orderPayment]);

        $this->orderStateWithStatus();
        $this->orderStateMock->shipped = false;

        $this->clientHelperMock
            ->expects($this->once())
            ->method('sendOrderStatus')
            ->with('payment_123456', 'my_reference', 'My Status', false);

        $this->assertNull($this->orderService->manageStatusUpdate($order, $this->orderStateMock));
    }

    /**
     * Mock order state with status
     *
     * @return void
     */
    private function orderStateWithStatus()
    {
        $this->orderStateMock
            ->method('getFieldByLang')
            ->willReturn('My Status');
    }
}
