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

namespace Alma\PrestaShop\Tests\Unit\Model;

use Alma\API\Client;
use Alma\API\Endpoints\Merchants;
use Alma\PrestaShop\Exceptions\AlmaApiKeyException;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Model\AlmaApiKeyModel;
use Alma\PrestaShop\Model\ClientModel;
use Alma\PrestaShop\Proxy\ConfigurationProxy;
use Alma\PrestaShop\Proxy\ToolsProxy;
use PHPUnit\Framework\TestCase;

class AlmaApiKeyModelTest extends TestCase
{
    /**
     * @var AlmaApiKeyModel
     */
    protected $almaApiKeyModel;
    /**
     * @var ToolsProxy
     */
    protected $toolsProxyMock;
    /**
     * @var \Alma\PrestaShop\Model\ClientModel
     */
    protected $clientModelMock;
    /**
     * @var \Alma\API\Endpoints\Merchants
     */
    protected $merchantMock;
    /**
     * @var \Alma\API\Client
     */
    protected $clientMockTest;
    /**
     * @var \Alma\API\Client
     */
    protected $clientMockLive;

    public function setUp()
    {
        $this->toolsProxyMock = $this->createMock(ToolsProxy::class);
        $this->configurationProxyMock = $this->createMock(ConfigurationProxy::class);
        $this->clientModelMock = $this->createMock(ClientModel::class);
        $this->clientMockTest = $this->createMock(Client::class);
        $this->clientMockLive = $this->createMock(Client::class);
        $this->merchantMockTest = $this->createMock(Merchants::class);
        $this->merchantMockLive = $this->createMock(Merchants::class);
        $this->clientMockTest->merchants = $this->merchantMockTest;
        $this->clientMockLive->merchants = $this->merchantMockLive;
        $this->almaApiKeyModel = new AlmaApiKeyModel(
            $this->toolsProxyMock,
            $this->configurationProxyMock,
            $this->clientModelMock
        );
    }

    public function tearDown()
    {
        $this->almaApiKeyModel = null;
        $this->toolsProxyMock = null;
    }

    /**
     * @throws \Alma\PrestaShop\Exceptions\AlmaApiKeyException
     */
    public function testCheckActiveApiKeySendIsEmptyWithEmptyKey()
    {
        $this->toolsProxyMock->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(['ALMA_API_MODE'], ['ALMA_TEST_API_KEY'])
            ->willReturnOnConsecutiveCalls('test', '');
        $this->expectException(AlmaApiKeyException::class);
        $this->almaApiKeyModel->checkActiveApiKeySendIsEmpty();
    }

    /**
     * @throws \Alma\PrestaShop\Exceptions\AlmaApiKeyException
     */
    public function testCheckActiveApiKeySendIsEmptyWithNotEmptyKey()
    {
        $this->toolsProxyMock->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(['ALMA_API_MODE'], ['ALMA_TEST_API_KEY'])
            ->willReturnOnConsecutiveCalls('test', 'notEmpty');
        $this->almaApiKeyModel->checkActiveApiKeySendIsEmpty();
    }

    /**
     * @throws \Alma\PrestaShop\Exceptions\AlmaApiKeyException
     */
    public function testApiKeysInvalidFormClientWithCannotCreatePayment()
    {
        $this->merchantMockTest->can_create_payments = false;
        $this->merchantMockLive->can_create_payments = true;
        $apiKeys = [
            'test' => 'notAllowedApiKey',
            'live' => 'ValidApiKey',
        ];
        $this->clientModelMock->expects($this->exactly(2))
            ->method('setApiKey')
            ->withConsecutive([$apiKeys['test']], [$apiKeys['live']]);
        $this->clientModelMock->expects($this->exactly(2))
            ->method('setMode')
            ->withConsecutive(['test'], ['live']);
        $this->clientModelMock->expects($this->exactly(2))
            ->method('getMerchantMe')
            ->willReturnOnConsecutiveCalls($this->merchantMockTest, $this->merchantMockLive);
        $this->expectException(AlmaApiKeyException::class);
        $this->almaApiKeyModel->checkApiKeys($apiKeys);
    }

    /**
     * @throws \Alma\PrestaShop\Exceptions\AlmaApiKeyException
     */
    public function testApiKeysInvalidFormClientWithInvalidApiKey()
    {
        $this->merchantMockTest = null;
        $this->merchantMockLive->can_create_payments = true;
        $apiKeys = [
            'test' => 'invalidApiKey',
            'live' => 'ValidApiKey',
        ];
        $this->clientModelMock->expects($this->exactly(2))
            ->method('setApiKey')
            ->withConsecutive([$apiKeys['test']], [$apiKeys['live']]);
        $this->clientModelMock->expects($this->exactly(2))
            ->method('setMode')
            ->withConsecutive(['test'], ['live']);
        $this->clientModelMock->expects($this->exactly(2))
            ->method('getMerchantMe')
            ->willReturnOnConsecutiveCalls($this->merchantMockTest, $this->merchantMockLive);
        $this->expectException(AlmaApiKeyException::class);
        $this->almaApiKeyModel->checkApiKeys($apiKeys);
    }

    /**
     * @throws \Alma\PrestaShop\Exceptions\AlmaApiKeyException
     */
    public function testApiKeysInvalidFormClientWithCanCreatePayment()
    {
        $this->merchantMockTest->can_create_payments = true;
        $this->merchantMockLive->can_create_payments = true;
        $apiKeys = [
            'test' => 'ValidApiKey',
            'live' => 'ValidApiKey',
        ];
        $this->clientModelMock->expects($this->exactly(2))
            ->method('setApiKey')
            ->withConsecutive([$apiKeys['test']], [$apiKeys['live']]);
        $this->clientModelMock->expects($this->exactly(2))
            ->method('setMode')
            ->withConsecutive(['test'], ['live']);
        $this->clientModelMock->expects($this->exactly(2))
            ->method('getMerchantMe')
            ->willReturnOnConsecutiveCalls($this->merchantMockTest, $this->merchantMockLive);
        $this->almaApiKeyModel->checkApiKeys($apiKeys);
    }

    /**
     * @throws \Alma\PrestaShop\Exceptions\AlmaApiKeyException
     */
    public function testApiKeysInvalidFormClientWithCanCreatePaymentAndObscure()
    {
        $this->merchantMockTest->can_create_payments = true;
        $this->merchantMockLive->can_create_payments = true;
        $apiKeys = [
            'test' => ConstantsHelper::OBSCURE_VALUE,
            'live' => ConstantsHelper::OBSCURE_VALUE,
        ];
        $this->clientModelMock->expects($this->never())
            ->method('setApiKey')
            ->withConsecutive([$apiKeys['test']], [$apiKeys['live']]);
        $this->clientModelMock->expects($this->never())
            ->method('setMode')
            ->withConsecutive(['test'], ['live']);
        $this->clientModelMock->expects($this->never())
            ->method('getMerchantMe')
            ->willReturnOnConsecutiveCalls($this->merchantMockTest, $this->merchantMockLive);
        $this->almaApiKeyModel->checkApiKeys($apiKeys);
    }
}
