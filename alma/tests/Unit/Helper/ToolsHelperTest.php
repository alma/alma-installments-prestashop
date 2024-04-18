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
        $this->assertEquals($this->toolsHelper->psRound('200.236'), 200);
        $this->assertEquals($this->toolsHelper->psRound('200.236', 2), 200.24);
        $this->assertEquals($this->toolsHelper->psRound('200.236', 2, PS_ROUND_UP), 200.24);
        $this->assertEquals($this->toolsHelper->psRound('200.236', 2, PS_ROUND_DOWN), 200.23);
        $this->assertEquals($this->toolsHelper->psRound('200.236', 2, PS_ROUND_HALF_DOWN), 200.24);
        $this->assertEquals($this->toolsHelper->psRound('200.236', 2, PS_ROUND_HALF_EVEN), 200.24);
        $this->assertEquals($this->toolsHelper->psRound('200.236', 2, PS_ROUND_HALF_ODD), 200.24);
        $this->assertEquals($this->toolsHelper->psRound('200.236', 2, PS_ROUND_HALF_UP), 200.24);
    }

    public function testPsVersionCompare()
    {
        $this->assertEquals($this->toolsHelper->psVersionCompare('1.5', '<'), false);
        $this->assertEquals($this->toolsHelper->psVersionCompare('8', '<'), true);
        $this->assertEquals($this->toolsHelper->psVersionCompare('8', '<', '9'), false);
        $this->assertEquals($this->toolsHelper->psVersionCompare('1.6.5', '>=', '1.5.3'), false);
        $this->assertEquals($this->toolsHelper->psVersionCompare('1.5.3', '>=', '1.6.5'), true);
    }

    public function testStrlen()
    {
        $this->assertEquals($this->toolsHelper->strlen(''), 0);
        $this->assertEquals($this->toolsHelper->strlen('test'), 4);
    }

    public function testSubstr()
    {
        $this->assertEquals($this->toolsHelper->substr('testHello', 0, '4'), 'test');
        $this->assertEquals($this->toolsHelper->substr('testHello', 4, '5'), 'Hello');
    }
}
