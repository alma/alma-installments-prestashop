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

use Alma\API\Entities\Merchant;
use Alma\API\RequestError;
use Alma\PrestaShop\Builders\Helpers\ApiHelperBuilder;
use Alma\PrestaShop\Exceptions\ActivationException;
use Alma\PrestaShop\Exceptions\ApiMerchantsException;
use Alma\PrestaShop\Exceptions\WrongCredentialsException;
use Alma\PrestaShop\Factories\ModuleFactory;
use Alma\PrestaShop\Helpers\Admin\AdminInsuranceHelper;
use Alma\PrestaShop\Helpers\ApiHelper;
use Alma\PrestaShop\Helpers\ClientHelper;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use Mockery;
use PHPUnit\Framework\TestCase;

class ApiHelperTest extends TestCase
{
    /**
     * @var \Alma\PrestaShop\Helpers\ApiHelper
     */
    protected $apiHelper;
    /**
     * @var \Alma\PrestaShop\Helpers\ClientHelper
     */
    protected $clientHelperMock;

    public function setUp()
    {
        $this->clientHelperMock = $this->createMock(ClientHelper::class);
        $this->apiHelper = new ApiHelper(
            $this->createMock(ModuleFactory::class),
            $this->clientHelperMock,
            $this->createMock(ToolsHelper::class),
            $this->createMock(ConfigurationHelper::class),
            $this->createMock(AdminInsuranceHelper::class)
        );
    }

    public function testGetMerchant()
    {
        $responseCode = new \stdClass();
        $responseCode->responseCode = 401;

        $exception = new RequestError('', null, $responseCode);
        $clientHelper = Mockery::mock(ClientHelper::class)->makePartial();
        $clientHelper->shouldReceive('getMerchantsMe')->andThrow($exception);

        $apiHelperBuilder = Mockery::mock(ApiHelperBuilder::class)->makePartial();
        $apiHelperBuilder->shouldReceive('getClientHelper')->andReturn($clientHelper);

        $apiHelper = $apiHelperBuilder->getInstance();

        $this->expectException(WrongCredentialsException::class);
        $apiHelper->getMerchant();

        $clientHelper = Mockery::mock(ClientHelper::class)->makePartial();
        $clientHelper->shouldReceive('getMerchantsMe')->andThrow(new \Exception());

        $apiHelperBuilder = Mockery::mock(ApiHelperBuilder::class)->makePartial();
        $apiHelperBuilder->shouldReceive('getClientHelper')->andReturn($clientHelper);

        $apiHelper = $apiHelperBuilder->getInstance();

        $this->expectException(ApiMerchantsException::class);
        $apiHelper->getMerchant();

        $merchant = new Merchant(['can_create_payments' => false]);

        $clientHelper = Mockery::mock(ClientHelper::class)->makePartial();
        $clientHelper->shouldReceive('getMerchantsMe')->andReturn($merchant);

        $apiHelperBuilder = Mockery::mock(ApiHelperBuilder::class)->makePartial();
        $apiHelperBuilder->shouldReceive('getClientHelper')->andReturn($clientHelper);

        $apiHelper = $apiHelperBuilder->getInstance();

        $this->expectException(ActivationException::class);
        $apiHelper->getMerchant();

        $merchant = new Merchant(['can_create_payments' => true]);

        $clientHelper = Mockery::mock(ClientHelper::class)->makePartial();
        $clientHelper->shouldReceive('getMerchantsMe')->andReturn($merchant);

        $toolsHelper = Mockery::mock(ToolsHelper::class)->makePartial();
        $toolsHelper->psVersionCompare('psVersionCompare', [1, '>=', 2])->andReturn(true);

        $apiHelperBuilder = Mockery::mock(ApiHelperBuilder::class)->makePartial();
        $apiHelperBuilder->shouldReceive('getClientHelper')->andReturn($clientHelper);

        $apiHelper = $apiHelperBuilder->getInstance();

        $this->assertInstanceOf(Merchant::class, $apiHelper->getMerchant());

        $toolsHelper = Mockery::mock(ToolsHelper::class)->makePartial();
        $toolsHelper->psVersionCompare('psVersionCompare', [1, '>=', 2])->andReturn(false);

        $apiHelperBuilder = Mockery::mock(ApiHelperBuilder::class)->makePartial();
        $apiHelperBuilder->shouldReceive('getClientHelper')->andReturn($clientHelper);

        $apiHelper = $apiHelperBuilder->getInstance();

        $this->assertInstanceOf(Merchant::class, $apiHelper->getMerchant());
    }

    public function testSaveFeatureFlag()
    {
        $merchant = new Merchant(['cms_insurance' => 1]);

        $configurationHelper = Mockery::mock(ConfigurationHelper::class)->makePartial();
        $configurationHelper->shouldReceive('updateValue')->andReturn('');

        $apiHelperBuilder = Mockery::mock(ApiHelperBuilder::class)->makePartial();
        $apiHelperBuilder->shouldReceive('getConfigurationHelper')->andReturn($configurationHelper);

        $apiHelper = $apiHelperBuilder->getInstance();

        $reflection = new \ReflectionClass($apiHelper);
        $method = $reflection->getMethod('saveFeatureFlag');
        $method->setAccessible(true);

        $this->assertEquals('1', $method->invokeArgs($apiHelper, [
            $merchant,
            'cms_insurance',
            ConstantsHelper::ALMA_ALLOW_INSURANCE,
            ConstantsHelper::ALMA_ACTIVATE_INSURANCE,
        ]));

        $merchant = new Merchant(['cms_insurance' => 0]);

        $configurationHelper = Mockery::mock(ConfigurationHelper::class)->makePartial();
        $configurationHelper->shouldReceive('updateValue')->andReturn('');

        $apiHelperBuilder = Mockery::mock(ApiHelperBuilder::class)->makePartial();
        $apiHelperBuilder->shouldReceive('getConfigurationHelper')->andReturn($configurationHelper);

        $apiHelper = $apiHelperBuilder->getInstance();

        $reflection = new \ReflectionClass($apiHelper);
        $method = $reflection->getMethod('saveFeatureFlag');
        $method->setAccessible(true);

        $this->assertEquals('0', $method->invokeArgs($apiHelper, [
            $merchant,
            'cms_insurance',
            ConstantsHelper::ALMA_ALLOW_INSURANCE,
            ConstantsHelper::ALMA_ACTIVATE_INSURANCE,
        ]));
    }

    public function testGetPaymentEligibility()
    {
        $paymentData = ['paymentData'];
        $paymentEligibility = ['paymentEligibility'];

        $clientHelper = Mockery::mock(ClientHelper::class)->makePartial();
        $clientHelper->shouldReceive('getPaymentEligibility', [$paymentData])->andReturn($paymentEligibility);

        $apiHelperBuilder = Mockery::mock(ApiHelperBuilder::class)->makePartial();
        $apiHelperBuilder->shouldReceive('getClientHelper')->andReturn($clientHelper);

        $apiHelper = $apiHelperBuilder->getInstance();
        $this->assertEquals($paymentEligibility, $apiHelper->getPaymentEligibility($paymentData));

        $clientHelper = Mockery::mock(ClientHelper::class)->makePartial();
        $clientHelper->shouldReceive('getPaymentEligibility', [$paymentData])->andThrow(new \Exception());

        $apiHelperBuilder = Mockery::mock(ApiHelperBuilder::class)->makePartial();
        $apiHelperBuilder->shouldReceive('getClientHelper')->andReturn($clientHelper);

        $apiHelper = $apiHelperBuilder->getInstance();
        $this->assertEquals([], $apiHelper->getPaymentEligibility($paymentData));
    }

    /**
     * @return void
     */
    public function testSendUrlForGatherCmsData()
    {
        $url = 'url';
        $this->clientHelperMock->expects($this->once())
            ->method('sendUrlForGatherCmsData')
            ->with($url);
        $this->apiHelper->sendUrlForGatherCmsData($url);
    }
}
