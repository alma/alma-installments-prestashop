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

namespace Alma\PrestaShop\Tests\Unit\Forms;

use Alma\PrestaShop\Forms\ShareOfCheckoutAdminFormBuilder;
use PHPUnit\Framework\TestCase;

class ShareOfCheckoutAdminFormBuilderTest extends TestCase
{
    public function setUp()
    {
        $this->moduleMock = $this->createMock(\Alma::class);
        $this->contextMock = $this->createMock(\Context::class);
        $this->smartyMock = $this->createMock(\Smarty::class);
        $this->contextMock->smarty = $this->smartyMock;
        $this->shareOfCheckoutAdminFormBuilder = new ShareOfCheckoutAdminFormBuilder(
            $this->moduleMock,
            $this->contextMock,
            'image',
            []
        );
    }

    public function testBuild()
    {
        $expected = [
            'form' => [
                'legend' => [
                    'title' => null,
                    'image' => 'image',
                ],
                'input' => [
                    [
                        'name' => null,
                        'label' => false,
                        'type' => 'html',
                        'form_group_class' => 'input_html',
                        'col' => 12,
                    ],
                    [
                        'name' => 'ALMA_SHARE_OF_CHECKOUT_STATE',
                        'label' => null,
                        'type' => 'alma_switch',
                        'readonly' => false,
                        'values' => [
                            'id' => 'id',
                            'name' => 'label',
                            'query' => [
                                [
                                    'id' => 'ON',
                                    'val' => true,
                                    'label' => '',
                                ],
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => null,
                    'class' => 'button btn btn-default pull-right',
                ],
            ],
        ];

        $this->assertEquals($expected, $this->shareOfCheckoutAdminFormBuilder->build());
    }
}
