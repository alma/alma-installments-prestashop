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

namespace Alma\PrestaShop\Tests\Unit\Helper;

use Alma\PrestaShop\Exceptions\SubscriptionException;
use Alma\PrestaShop\Helpers\SubscriptionHelper;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use PHPUnit\Framework\TestCase;

class SubscriptionHelperTest extends TestCase
{
    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;
    /**
     * @var AlmaInsuranceProductRepository|(AlmaInsuranceProductRepository&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $almaInsuranceProductRepository;

    public function setUp()
    {
        $this->almaInsuranceProductRepository = $this->createMock(AlmaInsuranceProductRepository::class);
        $this->subscriptionHelper = new SubscriptionHelper($this->almaInsuranceProductRepository);
    }

    /**
     * @return void
     *
     * @throws SubscriptionException
     */
    public function testUpdatedSubscriptionExecutedOnceWithParam()
    {
        $subscriptionId = 'subscription_39lGsF0UdBfpjQ8UXdYvkX';
        $status = 'started';
        $subscriptionBrokerId = 'broker_id';
        $this->almaInsuranceProductRepository->expects($this->once())
            ->method('updateSubscription')
            ->with($subscriptionId, $status, $subscriptionBrokerId)
            ->willReturn(true);
        $this->subscriptionHelper->updateSubscription($subscriptionId, $status, $subscriptionBrokerId);
    }

    /**
     * @return void
     *
     * @throws SubscriptionException
     */
    public function testThrowExceptionIfRequestToUpdateSubscriptionWrong()
    {
        $subscriptionId = 'subscription_39lGsF0UdBfpjQ8UXdYvkX';
        $status = 'started';
        $subscriptionBrokerId = 'broker_id';
        $this->almaInsuranceProductRepository->expects($this->once())
            ->method('updateSubscription')
            ->with($subscriptionId, $status, $subscriptionBrokerId)
            ->willReturn(false);
        $this->expectException(SubscriptionException::class);
        $this->subscriptionHelper->updateSubscription($subscriptionId, $status, $subscriptionBrokerId);
    }
}
