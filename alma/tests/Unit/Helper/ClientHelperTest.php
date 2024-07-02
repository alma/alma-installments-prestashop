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

namespace Alma\PrestaShop\Tests\Unit\Helper;

use Alma\API\ClientContext;
use Alma\API\Endpoints\Orders;
use Alma\API\Entities\Payment;
use Alma\API\Exceptions\ParametersException;
use Alma\API\Exceptions\RequestException;
use Alma\PrestaShop\Exceptions\ClientException;
use Alma\PrestaShop\Helpers\ClientHelper;
use Mockery;
use PHPUnit\Framework\TestCase;

class ClientHelperTest extends TestCase
{
    /**
     * @var Mockery\Mock|(Mockery\MockInterface&ClientHelper)
     */
    protected $clientHelper;

    public function setUp()
    {
        $this->clientHelper = Mockery::mock(ClientHelper::class)->makePartial();
    }

    /**
     * When i call an api
     * And there is no alma client
     * Then I expect a client exception
     */
    public function testSendOrderEndpointNoAlmaClient()
    {
        $this->clientHelper->shouldReceive('getAlmaClient')->andThrow(ClientException::class);

        $this->expectException(ClientException::class);
        $this->clientHelper->getClientOrdersEndpoint();
    }

    /**
     * When i call an api
     * Then I expect a endpoint orders
     */
    public function testSendOrderEndpoint()
    {
        $almaClient = new \stdClass();
        $almaClient->orders = new Orders(Mockery::mock(ClientContext::class));

        $this->clientHelper->shouldReceive('getAlmaClient')->andReturn($almaClient);

        $this->assertInstanceOf(Orders::class, $this->clientHelper->getClientOrdersEndpoint());
    }

    /**
     * When i call the function with wrong parameter
     * Then I expect an Parameters exception
     */
    public function testSendOrderStatusWrongParameters()
    {
        $orderEndpoint = Mockery::mock(Orders::class)->makePartial();
        $orderEndpoint->shouldReceive('sendStatus')->andThrow(ParametersException::class);

        $clientHelper = Mockery::mock(ClientHelper::class)->makePartial();
        $clientHelper->shouldReceive('getClientOrdersEndpoint')->andReturn($orderEndpoint);

        $this->expectException(ParametersException::class);
        $clientHelper->sendOrderStatus('transactionId', []);
    }

    /**
     * When i call the function and the request fails
     * Then I expect a Request exception
     */
    public function testSendOrderStatusBadRequest()
    {
        $orderEndpoint = Mockery::mock(Orders::class)->makePartial();
        $orderEndpoint->shouldReceive('sendStatus')->andThrow(RequestException::class);

        $clientHelper = Mockery::mock(ClientHelper::class)->makePartial();
        $clientHelper->shouldReceive('getClientOrdersEndpoint')->andReturn($orderEndpoint);

        $this->expectException(RequestException::class);
        $clientHelper->sendOrderStatus('transactionId', []);
    }

    /**
     * When i call an api
     * Then I expect a endpoint paymentqs
     */
    public function testSendPaymentEndpoint()
    {
        $almaClient = new \stdClass();
        $almaClient->payments = Mockery::mock(Payment::class);

        $this->clientHelper->shouldReceive('getAlmaClient')->andReturn($almaClient);

        $this->assertInstanceOf(Payment::class, $this->clientHelper->getClientPaymentsEndpoint());
    }

    /**
     * When i call an api
     * And there is no alma client
     * Then I expect a client exception
     */
    public function testSendPaymentEndpointNoAlmaClient()
    {
        $this->clientHelper->shouldReceive('getAlmaClient')->andThrow(ClientException::class);

        $this->expectException(ClientException::class);
        $this->clientHelper->getClientPaymentsEndpoint();
    }

    /**
     * When i call the function and the request fails
     * Then I expect a Request exception
     */
    public function testGetPaymentByTransactionIdBadRequest()
    {
        $paymentEndoint = Mockery::mock(Payment::class)->makePartial();
        $paymentEndoint->shouldReceive('fetch')->andThrow(RequestException::class);

        $clientHelper = Mockery::mock(ClientHelper::class)->makePartial();
        $clientHelper->shouldReceive('getClientPaymentsEndpoint')->andReturn($paymentEndoint);

        $this->expectException(RequestException::class);
        $clientHelper->getPaymentByTransactionId('transactionId');
    }

    public function tearDown()
    {
        $this->clientHelper = null;
    }
}
