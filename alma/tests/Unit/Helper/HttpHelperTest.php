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

use Alma\PrestaShop\Helpers\HttpHelper;
use PHPUnit\Framework\TestCase;

class HttpHelperTest extends TestCase
{
    /**
     * @var HttpHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $httpHelper;

    public function setUp()
    {
        $this->httpHelper = $this->createPartialMock(HttpHelper::class, ['getAllHeaders']);
    }

    public function testGetHeaderFoundViaGetallheaders()
    {
        $this->httpHelper->method('getAllHeaders')->willReturn([
            'Content-Type' => 'application/json',
            'X-Alma-Signature' => 'abc123',
        ]);

        $result = $this->httpHelper->getHeader('X-Alma-Signature');

        $this->assertSame('abc123', $result);
    }

    public function testGetHeaderIsCaseInsensitive()
    {
        $this->httpHelper->method('getAllHeaders')->willReturn([
            'x-alma-signature' => 'abc123',
        ]);

        $result = $this->httpHelper->getHeader('X-Alma-Signature');

        $this->assertSame('abc123', $result);
    }

    public function testGetHeaderFallsBackToServerWhenNotInGetallheaders()
    {
        $this->httpHelper->method('getAllHeaders')->willReturn([]);
        $_SERVER['HTTP_X_ALMA_SIGNATURE'] = 'fallback_value';

        $result = $this->httpHelper->getHeader('X-Alma-Signature');

        $this->assertSame('fallback_value', $result);

        unset($_SERVER['HTTP_X_ALMA_SIGNATURE']);
    }

    public function testGetHeaderReturnsNullWhenNotFoundAnywhere()
    {
        $this->httpHelper->method('getAllHeaders')->willReturn([]);
        unset($_SERVER['HTTP_X_ALMA_SIGNATURE']);

        $result = $this->httpHelper->getHeader('X-Alma-Signature');

        $this->assertNull($result);
    }

    public function testGetHeaderPrefersGetallheadersOverServer()
    {
        $this->httpHelper->method('getAllHeaders')->willReturn([
            'X-Alma-Signature' => 'from_getallheaders',
        ]);
        $_SERVER['HTTP_X_ALMA_SIGNATURE'] = 'from_server';

        $result = $this->httpHelper->getHeader('X-Alma-Signature');

        $this->assertSame('from_getallheaders', $result);

        unset($_SERVER['HTTP_X_ALMA_SIGNATURE']);
    }
}
