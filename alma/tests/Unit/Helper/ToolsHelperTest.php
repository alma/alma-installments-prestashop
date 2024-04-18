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

use Alma\PrestaShop\Helpers\ToolsHelper;
use PHPUnit\Framework\TestCase;

class ToolsHelperTest extends TestCase
{
    /**
     * @var ToolsHelper
     */
    protected $toolsHelper;

    public function setUp() {
        $this->toolsHelper = new ToolsHelper();
    }

    public function testPsRound()
    {
        $this->assertEquals(200, $this->toolsHelper->psRound('200.236'));
        $this->assertEquals(200.24, $this->toolsHelper->psRound('200.236', 2));
        $this->assertEquals(200.24, $this->toolsHelper->psRound('200.236', 2, PS_ROUND_UP));
        $this->assertEquals(200.23, $this->toolsHelper->psRound('200.236', 2, PS_ROUND_DOWN));
        $this->assertEquals(200.24, $this->toolsHelper->psRound('200.236', 2, PS_ROUND_HALF_DOWN));
        $this->assertEquals(200.24, $this->toolsHelper->psRound('200.236', 2, PS_ROUND_HALF_EVEN));
        $this->assertEquals(200.24, $this->toolsHelper->psRound('200.236', 2, PS_ROUND_HALF_ODD));
        $this->assertEquals(200.24, $this->toolsHelper->psRound('200.236', 2, PS_ROUND_HALF_UP));
    }

    public function testPsVersionCompare()
    {
        $this->assertEquals(false, $this->toolsHelper->psVersionCompare('1.5', '<'));
        $this->assertEquals(true, $this->toolsHelper->psVersionCompare('8', '<'));
        $this->assertEquals(false, $this->toolsHelper->psVersionCompare('8', '<', '9'));
        $this->assertEquals(false, $this->toolsHelper->psVersionCompare('1.6.5', '>=', '1.5.3'));
        $this->assertEquals(true, $this->toolsHelper->psVersionCompare('1.5.3', '>=', '1.6.5'));
    }

    public function testStrlen()
    {
        $this->assertEquals(0, $this->toolsHelper->strlen(''));
        $this->assertEquals(4, $this->toolsHelper->strlen('test'));
    }

    public function testSubstr()
    {
        $this->assertEquals('test', $this->toolsHelper->substr('testHello', 0, '4'));
        $this->assertEquals('Hello', $this->toolsHelper->substr('testHello', 4, '5'));
    }
}
