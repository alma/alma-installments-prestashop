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

use Alma\PrestaShop\Exceptions\AlmaApiKeyException;
use Alma\PrestaShop\Model\AlmaApiKeyModel;
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

    public function setUp()
    {
        $this->toolsProxyMock = $this->createMock(ToolsProxy::class);
        $this->almaApiKeyModel = new AlmaApiKeyModel(
            $this->toolsProxyMock
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
    public function testCheckActiveApiKeyWithoutApiKey()
    {
        $mode = 'test';
        $this->toolsProxyMock->expects($this->once())
            ->method('getValue')
            ->with('ALMA_TEST_API_KEY')
            ->willReturn('');
        $this->expectException(AlmaApiKeyException::class);
        $this->almaApiKeyModel->checkActiveApiKey($mode);
    }

    /**
     * @throws \Alma\PrestaShop\Exceptions\AlmaApiKeyException
     */
    public function testCheckActiveApiKeyWithApiKey()
    {
        $mode = 'test';
        $this->toolsProxyMock->expects($this->once())
            ->method('getValue')
            ->with('ALMA_TEST_API_KEY')
            ->willReturn('api_key');
        $this->almaApiKeyModel->checkActiveApiKey($mode);
        $this->assertTrue(true);
    }
}
