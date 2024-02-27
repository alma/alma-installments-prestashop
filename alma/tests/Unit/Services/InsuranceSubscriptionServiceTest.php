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

use Alma\PrestaShop\Exceptions\InsuranceSubscriptionException;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Services\InsuranceSubscriptionService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InsuranceSubscriptionServiceTest extends TestCase
{
    /**
     * @var InsuranceSubscriptionService
     */
    protected $insuranceSubscriptionService;
    /**
     * @var AlmaInsuranceProductRepository|(AlmaInsuranceProductRepository&MockObject)|MockObject
     */
    protected $almaInsuranceProductRepository;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->insuranceSubscriptionService = new InsuranceSubscriptionService();
        $this->almaInsuranceProductRepository = $this->createMock(AlmaInsuranceProductRepository::class);
    }

    /**
     * Given a true if method setCancellation exist
     *
     * @return void
     */
    public function testSetCancellationMethodExist()
    {
        $this->assertTrue(method_exists($this->insuranceSubscriptionService, 'setCancellation'));
    }

    /**
     * Given expect one method setSubscriptionForCancellation and return true
     *
     * @return void
     *
     * @throws InsuranceSubscriptionException
     */
    public function testExpectMethodOnceSetSubscriptionForCancellationAndReturnTrue()
    {
        $sid = 'subscription_39lGsF0UdBfpjQ8UXdYvkX';
        $state = 'pending_cancellation';
        $reason = 'reason cancellation unit test';
        $this->almaInsuranceProductRepository->expects($this->once())
            ->method('updateSubscriptionForCancellation')
            ->with($sid, $state, $reason)
            ->willReturn(true);
        $this->insuranceSubscriptionService->setCancellation($sid, $state, $reason);
    }

    /**
     * Given expect one method setSubscriptionForCancellation return false and throw exception
     *
     * @return void
     *
     * @throws InsuranceSubscriptionException
     */
    public function testExpectMethodOnceSetSubscriptionForCancellationReturnFalseAndThrowException()
    {
        $sid = 'subscription_39lGsF0UdBfpjQ8UXdYvkX';
        $state = 'pending_cancellation';
        $reason = 'reason cancellation unit test';
        $this->expectException(InsuranceSubscriptionException::class);
        $this->almaInsuranceProductRepository->expects($this->once())
            ->method('updateSubscriptionForCancellation')
            ->with($sid, $state, $reason)
            ->willReturn(false);
        $this->insuranceSubscriptionService->setCancellation($sid, $state, $reason);
    }
}
