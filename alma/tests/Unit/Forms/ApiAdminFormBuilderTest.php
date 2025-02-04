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

use Alma\PrestaShop\Forms\ApiAdminFormBuilder;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Model\AlmaApiKeyModel;
use PHPUnit\Framework\TestCase;

class ApiAdminFormBuilderTest extends TestCase
{
    public function setUp()
    {
        $this->moduleMock = $this->createMock(\Module::class);
        $this->contextMock = $this->createMock(\Context::class);
        $this->almaApiKeyModelMock = $this->createMock(AlmaApiKeyModel::class);
        $this->apiAdminFormBuilder = new ApiAdminFormBuilder(
            $this->moduleMock,
            $this->contextMock,
            'image',
            $this->almaApiKeyModelMock
        );
    }

    /**
     * @dataProvider expectedArrayFormDataProvider
     *
     * @return void
     */
    public function testBuild($arrayForm, $needApiKey)
    {
        $this->almaApiKeyModelMock->expects($this->once())
            ->method('needApiKey')
            ->willReturn($needApiKey);

        $this->assertEquals($arrayForm, $this->apiAdminFormBuilder->build());
    }

    /**
     * @return array[]
     */
    public function expectedArrayFormDataProvider()
    {
        $commonLegend = [
            'title' => null,
            'image' => 'image',
        ];
        $commonSubmit = [
            'title' => null,
            'class' => 'button btn btn-default pull-right',
        ];
        $apiModeInput = [
            [
                'name' => 'ALMA_API_MODE',
                'label' => null,
                'desc' => null,
                'type' => 'select',
                'required' => true,
                'options' => [
                    'query' => [
                        [
                            'api_mode' => 'live',
                            'name' => 'Live',
                        ],
                        [
                            'api_mode' => 'test',
                            'name' => 'Test',
                        ],
                    ],
                    'id' => 'api_mode',
                    'name' => 'name',
                ],
            ],
        ];
        $apiKeyInputs = [
            [
                'name' => 'ALMA_LIVE_API_KEY',
                'label' => null,
                'type' => 'secret',
                'size' => 75,
                'required' => false,
                'desc' => ' – ',
                'placeholder' => ConstantsHelper::OBSCURE_VALUE,
            ],
            [
                'name' => 'ALMA_TEST_API_KEY',
                'label' => null,
                'type' => 'secret',
                'size' => 75,
                'required' => false,
                'desc' => ' – ',
                'placeholder' => ConstantsHelper::OBSCURE_VALUE,
            ],
        ];
        $hiddenInputs = [
            [
                'name' => '_api_only',
                'label' => null,
                'type' => 'hidden',
            ],
        ];

        return [
            'withoutApiKey' => [
                'arrayForm' => [
                    'form' => [
                        'legend' => $commonLegend,
                        'input' => array_merge(
                            $apiModeInput,
                            $apiKeyInputs,
                            $hiddenInputs
                        ),
                        'submit' => $commonSubmit,
                    ],
                ],
                'needApiKey' => true,
            ],
            'withApiKey' => [
                'arrayForm' => [
                    'form' => [
                        'legend' => $commonLegend,
                        'input' => array_merge(
                            $apiModeInput,
                            $apiKeyInputs
                        ),
                        'submit' => $commonSubmit,
                    ],
                ],
                'needApiKey' => false,
            ],
        ];
    }
}
