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

use Alma\API\Client;
use Alma\API\Endpoints\Insurance;
use Alma\API\RequestError;
use Alma\PrestaShop\Exceptions\SubscriptionException;
use Alma\PrestaShop\Helpers\SubscriptionHelper;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Services\InsuranceApiService;
use PHPUnit\Framework\TestCase;

class InsuranceApiServiceTest extends TestCase
{
    /**
     * @var SubscriptionHelper
     */
    private $insuranceApiService;
    /**
     * @var AlmaInsuranceProductRepository|(AlmaInsuranceProductRepository&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $almaInsuranceProductRepository;

    public function setUp()
    {
        $this->almaInsuranceProductRepository = $this->createMock(AlmaInsuranceProductRepository::class);
        $this->insuranceApiService = new InsuranceApiService();
        $this->client = $this->createMock(Client::class);
    }

    /**
     * @return void
     */
    public function testPostProcessMethodExists()
    {
        $this->assertTrue(method_exists($this->insuranceApiService, 'getSubscriptionById'));
    }

    /**
     * @throws SubscriptionException
     */
    public function testGetRequestIsCalledAndThrowSubscriptionExceptionIfApiThrowException()
    {
        $sid = 'subscription_39lGsF0UdBfpjQ8UXdYvkX';

        $insuranceMock = $this->createMock(Insurance::class);
        $insuranceMock->expects($this->once())->method('getSubscription')->with(['id' => $sid])->willThrowException(new RequestError('Request Error'));
        $this->client->insurance = $insuranceMock;
        $this->expectException(SubscriptionException::class);
        $this->insuranceApiService->setPhpClient($this->client);
        $this->insuranceApiService->getSubscriptionById($sid);
    }

    /**
     * @throws SubscriptionException
     */
    public function testGetRequestIsCalledAndReturnSubscriptionIfNoError()
    {
        $sid = 'subscription_39lGsF0UdBfpjQ8UXdYvkX';
        $subscriptionArray = ['subscriptions' => [
            ['id' => 'sub1'],
        ]];
        $insuranceMock = $this->createMock(Insurance::class);
        $insuranceMock->expects($this->once())->method('getSubscription')->with(['id' => $sid])->willReturn($subscriptionArray);
        $this->client->insurance = $insuranceMock;

        $this->insuranceApiService->setPhpClient($this->client);
        $this->assertEquals(['id' => 'sub1'], $this->insuranceApiService->getSubscriptionById($sid));
    }
}
