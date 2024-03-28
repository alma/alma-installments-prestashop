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

use Alma\PrestaShop\Exceptions\InsurancePendingCancellationException;
use Alma\PrestaShop\Exceptions\InsuranceSubscriptionException;
use Alma\PrestaShop\Exceptions\SubscriptionException;
use Alma\PrestaShop\Exceptions\TokenException;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\SubscriptionHelper;
use Alma\PrestaShop\Helpers\TokenHelper;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Services\InsuranceApiService;
use Alma\PrestaShop\Services\InsuranceSubscriptionService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Adapter\Module\Module;

class SubscriptionHelperTest extends TestCase
{
    /**
     * @var SubscriptionHelper
     */
    protected $subscriptionHelper;
    /**
     * @var AlmaInsuranceProductRepository|(AlmaInsuranceProductRepository&MockObject)|MockObject
     */
    protected $almaInsuranceProductRepository;
    /**
     * @var MockObject|Module|(Module&MockObject)
     */
    protected $module;
    /**
     * @var TokenHelper
     */
    protected $tokenHelper;
    /**
     * @var InsuranceApiService|(InsuranceApiService&MockObject)|MockObject
     */
    protected $insuranceApiService;
    /**
     * @var InsuranceSubscriptionException|(InsuranceSubscriptionException&MockObject)|MockObject
     */
    protected $insuranceSubscriptionService;

    public function setUp()
    {
        $this->almaInsuranceProductRepository = $this->createMock(AlmaInsuranceProductRepository::class);
        $this->insuranceApiService = $this->createMock(InsuranceApiService::class);
        $this->module = $this->createMock(Module::class);
        $this->tokenHelper = $this->createMock(TokenHelper::class);
        $this->insuranceSubscriptionService = $this->createMock(InsuranceSubscriptionService::class);
        $this->subscriptionHelper = new SubscriptionHelper(
            $this->almaInsuranceProductRepository,
            $this->insuranceApiService,
            $this->tokenHelper,
            $this->insuranceSubscriptionService
        );
    }

    /**
     * Given a valid token, the method should pass in the method cancelSubscription once
     *
     * @return void
     *
     * @throws InsuranceSubscriptionException
     * @throws TokenException
     * @throws InsurancePendingCancellationException
     */
    public function testGetCancelSubscriptionWithValidToken()
    {
        $sid = 'subscription_39lGsF0UdBfpjQ8UXdYvkX';
        $this->insuranceApiService->expects($this->once())
            ->method('cancelSubscription')
            ->with($sid);

        $this->tokenHelper->expects($this->once())
            ->method('isAdminTokenValid')
            ->with(ConstantsHelper::BO_CONTROLLER_INSURANCE_ORDERS_DETAILS_CLASSNAME, 'token')
            ->willReturn(true);
        $this->subscriptionHelper->cancelSubscriptionWithToken($sid);
    }

    /**
     * Given a valid token and cancelSubscription throw exception
     *
     * @return void
     *
     * @throws InsurancePendingCancellationException
     * @throws InsuranceSubscriptionException
     * @throws TokenException
     */
    public function testGetCancelSubscriptionWithValidTokenAndThrowException()
    {
        $sid = 'subscription_39lGsF0UdBfpjQ8UXdYvkX';
        $this->insuranceApiService->expects($this->once())
            ->method('cancelSubscription')
            ->with($sid)
            ->willReturn(InsuranceSubscriptionException::class);

        $this->tokenHelper->expects($this->once())->method('isAdminTokenValid')
            ->with(ConstantsHelper::BO_CONTROLLER_INSURANCE_ORDERS_DETAILS_CLASSNAME, 'token')
            ->willReturn(true);
        $this->subscriptionHelper->cancelSubscriptionWithToken($sid);
    }

    /**
     * Given a wrong token, the method should return an error
     *
     * @return void
     *
     * @throws InsurancePendingCancellationException
     * @throws InsuranceSubscriptionException
     * @throws TokenException
     */
    public function testGetCancelSubscriptionReturnErrorIfTokenIsNotValid()
    {
        $sid = 'subscription_39lGsF0UdBfpjQ8UXdYvkX';
        $state = 'pending_cancellation';
        $reason = 'reason cancellation';

        $this->tokenHelper->expects($this->once())->method('isAdminTokenValid')
            ->with(ConstantsHelper::BO_CONTROLLER_INSURANCE_ORDERS_DETAILS_CLASSNAME, 'token')
            ->willReturn(false);
        $this->expectException(TokenException::class);
        $this->subscriptionHelper->cancelSubscriptionWithToken($sid, $state, $reason);
    }

    /**
     * Given a wrong trace and throw exception
     *
     * @dataProvider traceWrongDataProvider
     *
     * @param $trace
     *
     * @return void
     */
    public function testUpdateSubscriptionInvalidTrace($trace)
    {
        $sid = 'subscription_39lGsF0UdBfpjQ8UXdYvkX';

        $this->expectException(SubscriptionException::class);
        $this->insuranceApiService->expects($this->never())->method('getSubscriptionById');
        $this->subscriptionHelper->updateSubscriptionWithTrace($sid, $trace);
    }

    /**
     * Given a valid trace, the method should pass in the method getSubscriptionById and updateSubscription once
     *
     * @throws SubscriptionException
     */
    public function testUpdateSubscriptionWithValidTrace()
    {
        $trace = 'toto';
        $sid = 'subscription_39lGsF0UdBfpjQ8UXdYvkX';
        $subscriptionArray = [
            'state' => 'started',
            'broker_subscription_id' => 'broker_id',
        ];
        $this->almaInsuranceProductRepository->expects($this->once())
            ->method('updateSubscription')
            ->with($sid, $subscriptionArray['state'], $subscriptionArray['broker_subscription_id'])
            ->willReturn(true);
        $this->insuranceApiService->expects($this->once())
            ->method('getSubscriptionById')
            ->with($sid)
            ->willReturn($subscriptionArray);
        $this->subscriptionHelper->updateSubscriptionWithTrace($sid, $trace);
    }

    /**
     * Given a valid trace, the method should pass in the method getSubscriptionById and throw Exception and never update Subscription
     *
     * @return void
     *
     * @throws SubscriptionException
     */
    public function testUpdateSubscriptionWithValidTraceAndGetSubscriptionByIdThrowException()
    {
        $trace = 'toto';
        $sid = 'subscription_39lGsF0UdBfpjQ8UXdYvkX';

        $this->expectException(SubscriptionException::class);
        $this->almaInsuranceProductRepository->expects($this->never())
            ->method('updateSubscription');

        $this->insuranceApiService->expects($this->once())
            ->method('getSubscriptionById')
            ->with($sid)
            ->willThrowException(new SubscriptionException('Impossible to get subscription'));
        $this->subscriptionHelper->updateSubscriptionWithTrace($sid, $trace);
    }

    /**
     * Given a valid trace, the method should pass in the method getSubscriptionById and update Subscription with return false and throw exception
     *
     * @return void
     *
     * @throws SubscriptionException
     */
    public function testUpdateSubscriptionWithValidTraceAndGetSubscriptionByIdAndUpdateSubscriptionFalse()
    {
        $trace = 'toto';
        $sid = 'subscription_39lGsF0UdBfpjQ8UXdYvkX';
        $subscriptionArray = [
            'state' => 'started',
            'broker_subscription_id' => 'broker_id',
        ];
        $this->expectException(SubscriptionException::class);
        $this->almaInsuranceProductRepository->expects($this->once())
            ->method('updateSubscription')
            ->with($sid, $subscriptionArray['state'], $subscriptionArray['broker_subscription_id'])
            ->willReturn(false);
        $this->insuranceApiService->expects($this->once())
            ->method('getSubscriptionById')
            ->with($sid)
            ->willReturn($subscriptionArray);
        $this->subscriptionHelper->updateSubscriptionWithTrace($sid, $trace);
    }

    /**
     * @return array
     */
    public function traceWrongDataProvider()
    {
        return [
            'Test trace is empty' => [
                'trace' => '',
            ],
            'Test trace is not a string' => [
                'trace' => 123,
            ],
            'Test trace is an object' => [
                'trace' => new \stdClass(),
            ],
            'Test trace is an array' => [
                'trace' => ['toto'],
            ],
            'Test trace is an empty array' => [
                'trace' => [],
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function cancelSubscriptionErrorDataProvider()
    {
        return [
            'Test pending cancellation code 410' => [
                'returnedThrowException' => [
                    'message' => 'Pending cancellation',
                    'code' => 410,
                ],
                'expected' => [
                    'response' => [
                        'error' => true,
                        'message' => 'Pending cancellation',
                    ],
                    'code' => 410,
                ],
            ],
            'Test error with code 500' => [
                'returnedThrowException' => [
                    'message' => 'Impossible to cancel subscription',
                    'code' => 500,
                ],
                'expected' => [
                    'response' => [
                        'error' => true,
                        'message' => 'Impossible to cancel subscription',
                    ],
                    'code' => 500,
                ],
            ],
        ];
    }
}
