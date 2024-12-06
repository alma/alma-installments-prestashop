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
use Alma\API\Endpoints\Configuration;
use Alma\API\Endpoints\Merchants;
use Alma\API\Exceptions\RequestException;
use Alma\API\RequestError;
use Alma\PrestaShop\Exceptions\ClientException;
use Alma\PrestaShop\Factories\ClientFactory;
use Alma\PrestaShop\Model\ClientModel;
use PHPUnit\Framework\TestCase;

class ClientModelTest extends TestCase
{
    /**
     * @var ClientModel
     */
    protected $clientModel;
    /**
     * @var \Alma\API\Endpoints\Merchants
     */
    protected $merchantMock;
    /**
     * @var \Alma\API\Endpoints\Configuration
     */
    protected $configurationMock;

    public function setUp()
    {
        $this->merchantMock = $this->createMock(Merchants::class);
        $this->configurationMock = $this->createMock(Configuration::class);
        $this->almaClientMock = $this->createMock(Client::class);
        $this->clientFactoryMock = $this->createMock(ClientFactory::class);
        $this->clientFactoryMock->method('get')->willReturn($this->almaClientMock);
        $this->almaClientMock->merchants = $this->merchantMock;
        $this->almaClientMock->configuration = $this->configurationMock;
        $this->clientModel = new ClientModel($this->almaClientMock);
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        $this->merchantMock = null;
        $this->almaClientMock = null;
        $this->clientFactoryMock = null;
        $this->clientModel = null;
    }

    /**
     * @return void
     */
    public function testGetMerchantMeWithRequestError()
    {
        $this->merchantMock->method('me')->willThrowException(new RequestError('error'));
        $this->assertNull($this->clientModel->getMerchantMe());
    }

    /**
     * @return void
     */
    public function testMerchantMeWithoutApiKey()
    {
        $this->clientModel->setClient(null);
        $this->assertNull($this->clientModel->getMerchantMe());
    }

    /**
     * @return void
     */
    public function testGetMerchantFeePlanWithRequestError()
    {
        $this->merchantMock->method('feePlans')->willThrowException(new RequestError('error'));
        $this->assertEquals([], $this->clientModel->getMerchantFeePlans());
    }

    /**
     * @return void
     */
    public function testMerchantFeePlansWithoutApiKey()
    {
        $this->clientModel->setClient(null);
        $this->assertEquals([], $this->clientModel->getMerchantFeePlans());
    }

    /**
     * @dataProvider exceptionSendUrlForGatherCmsDataDataProvider
     *
     * @throws \Alma\PrestaShop\Exceptions\ClientException
     */
    public function testSendUrlForGatherCmsDataThrowRequestException($exceptions)
    {
        $this->configurationMock->method('sendIntegrationsConfigurationsUrl')->willThrowException($exceptions);
        $this->expectException(ClientException::class);
        $this->clientModel->sendUrlForGatherCmsData('url');
    }

    /**
     * @return array
     */
    public function exceptionSendUrlForGatherCmsDataDataProvider()
    {
        return [
            'RequestException' => [new RequestException('error')],
            'RequestError' => [new RequestError('error')],
            'ClientException' => [new ClientException('error')],
        ];
    }
}
