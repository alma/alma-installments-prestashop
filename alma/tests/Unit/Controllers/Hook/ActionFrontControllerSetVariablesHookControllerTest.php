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

namespace Alma\PrestaShop\Tests\Unit\Controllers\Hook;

use Alma\PrestaShop\Controllers\Hook\ActionFrontControllerSetVariablesHookController;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Repositories\ProductRepository;
use PHPUnit\Framework\TestCase;

class ActionFrontControllerSetVariablesHookControllerTest extends TestCase
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;
    /**
     * @var \Module
     */
    protected $module;

    public function setUp()
    {
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->module = $this->createMock(\Module::class);
        $this->insuranceProductId = '22';
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        $this->productRepository = null;
        $this->module = null;
        $this->insuranceProductId = null;
    }

    /**
     * @dataProvider paramsDataProvider
     *
     * @return void
     */
    public function testRunWithWrongParams($params)
    {
        $controller = new ActionFrontControllerSetVariablesHookController($this->module);
        $this->assertFalse($controller->run($params));
    }

    /**
     * @return void
     */
    public function testRunWithCorrectParamsAndNoInsuranceProduct()
    {
        $idProduct = '12';
        $this->productRepository->expects($this->once())
            ->method('getProductIdByReference')
            ->with(ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE)
            ->willReturn($this->insuranceProductId);
        $params = [
            'templateVars' => [
                'page' => [
                    'page_name' => 'product',
                    'body_classes' => [
                        "product-id-${idProduct}",
                    ],
                ],
            ],
        ];
        $controller = new ActionFrontControllerSetVariablesHookController($this->module, $this->productRepository);
        $this->assertFalse($controller->run($params));
    }

    /**
     * @return void
     */
    public function testRunWithCorrectParamsAndInsuranceProduct()
    {
        $idProduct = '22';
        $this->productRepository->expects($this->once())
            ->method('getProductIdByReference')
            ->with(ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE)
            ->willReturn($this->insuranceProductId);
        $params = [
            'templateVars' => [
                'page' => [
                    'page_name' => 'product',
                    'body_classes' => [
                        "product-id-${idProduct}" => true,
                    ],
                ],
            ],
        ];
        $controller = new ActionFrontControllerSetVariablesHookController($this->module, $this->productRepository);
        $this->assertTrue($controller->run($params));
    }

    /**
     * @return array
     */
    public function paramsDataProvider()
    {
        return [
            'params without page key' => [
                [
                    'templateVars' => [
                    ],
                ],
            ],
            'params without configuration key' => [
                [
                    'templateVars' => [
                        'page' => [
                            'page_name' => 'toto',
                        ],
                    ],
                ],
            ],
        ];
    }
}
