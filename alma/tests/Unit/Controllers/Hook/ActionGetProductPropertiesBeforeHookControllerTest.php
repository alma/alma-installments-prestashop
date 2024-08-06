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

use Alma\PrestaShop\Controllers\Hook\ActionGetProductPropertiesBeforeHookController;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use PHPUnit\Framework\TestCase;

class ActionGetProductPropertiesBeforeHookControllerTest extends TestCase
{
    /**
     * @var \Context
     */
    protected $context;
    /**
     * @var \Module
     */
    protected $module;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->context = $this->createMock(\Context::class);
        $this->context->smarty = $this->createMock(\Smarty::class);
        $this->module = $this->createMock(\Module::class);
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        $this->context->smarty = null;
        $this->module = null;
    }

    /**
     * @dataProvider paramsDataProvider
     *
     * @return void
     */
    public function testRunWithWrongParams($params)
    {
        $this->context->smarty->expects($this->never())
            ->method('assign');
        $controller = new ActionGetProductPropertiesBeforeHookController($this->module);
        $controller->run($params);
    }

    /**
     * @return void
     */
    public function testRunWithNotInsuranceProduct()
    {
        $this->context->smarty->expects($this->never())
            ->method('assign');
        $params = [
            'product' => [
                'reference' => 'reference-product',
            ],
            'context' => $this->context,
        ];

        $controller = new ActionGetProductPropertiesBeforeHookController($this->module);
        $controller->run($params);
    }

    /**
     * @return void
     */
    public function testRunWithCorrectParams()
    {
        $this->context->smarty->expects($this->once())
            ->method('assign')
            ->with([
                'configuration' => [
                    'is_catalog' => true,
                    'display_taxes_label' => null,
                ],
            ]);

        $params = [
            'product' => [
                'reference' => ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE,
            ],
            'context' => $this->context,
        ];

        $controller = new ActionGetProductPropertiesBeforeHookController($this->module);
        $controller->run($params);
    }

    /**
     * @return array
     */
    public function paramsDataProvider()
    {
        return [
            'params without product' => [
                [
                    'context' => $this->context,
                ],
            ],
            'params with product and no context' => [
                [
                    'product' => ['title' => 'product', 'reference' => '123456'],
                ],
            ],
        ];
    }
}
