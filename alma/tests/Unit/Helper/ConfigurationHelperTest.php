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

use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use PHPUnit\Framework\TestCase;

class ConfigurationHelperTest extends TestCase
{
    public function setUp()
    {
        $this->configurationHelper = new ConfigurationHelper();
    }

    public function testIsPayNow()
    {
        $this->assertFalse($this->configurationHelper->isPayNow('test'));
        $this->assertTrue($this->configurationHelper->isPayNow('general_1_0_0'));
    }

    /**
     * @dataProvider provideIsInPageEnabled
     *
     * @return void
     */
    public function testIsInPageEnabled($expected, $isInPageEnabled, $installments)
    {
        $settingsHelper = \Mockery::mock(SettingsHelper::class);
        $settingsHelper->shouldReceive('isInPageEnabled')->andReturn($isInPageEnabled);
        $this->assertEquals($expected, $this->configurationHelper->isInPageEnabled($installments, $settingsHelper));
    }

    public function provideIsInPageEnabled()
    {
        return [
            'test no inpage enable, installment 1' => [
                'expected' => false,
                'isInpageEnabled' => false,
                'installments' => 1,
            ],
            'test no inpage enable, installment 4' => [
                'expected' => false,
                'isInpageEnabled' => false,
                'installments' => 4,
            ],
            'test no inpage enable, installment 6' => [
                'expected' => false,
                'isInpageEnabled' => false,
                'installments' => 6,
            ],
            'test inpage enable, installment 1' => [
                'expected' => true,
                'isInpageEnabled' => true,
                'installments' => 1,
            ],
            'test inpage enable, installment 4' => [
                'expected' => true,
                'isInpageEnabled' => true,
                'installments' => 4,
            ],
            'test inpage enable, installment 6' => [
                'expected' => false,
                'isInpageEnabled' => true,
                'installments' => 6,
            ],
        ];
    }
}
