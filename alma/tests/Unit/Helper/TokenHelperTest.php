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

use Alma\PrestaShop\Helpers\TokenHelper;
use PHPUnit\Framework\TestCase;

class TokenHelperTest extends TestCase
{
    /**
     * @var TokenHelper
     */
    protected $tokenHelper;

    public function setUp()
    {
        $this->tokenHelper = new TokenHelper();
    }

    /**
     * Given the TokenHelper class contain method isAdminTokenValid
     *
     * @return void
     */
    public function testIsAdminTokenValidMethodExists()
    {
        $this->assertTrue(method_exists($this->tokenHelper, 'isAdminTokenValid'));
    }

    /**
     * @dataProvider dataTokenDataProvider
     *
     * @param $string
     * @param $keyValue
     *
     * @return void
     */
    public function testIfParamsArentStringReturnFalse($string, $keyValue)
    {
        $this->assertFalse($this->tokenHelper->isAdminTokenValid($string, $keyValue));
    }

    /**
     * @return array[]
     */
    public function dataTokenDataProvider()
    {
        return [
            'test both data are object' => [
                'string' => new \stdClass(),
                'keyValue' => new \stdClass(),
            ],
            'test first data is string and second data is object' => [
                'string' => 'test',
                'keyValue' => new \stdClass(),
            ],
        ];
    }
}
