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

namespace Alma\PrestaShop\Tests\Unit\Services;

use Alma\PrestaShop\Model\FeePlanModel;
use Alma\PrestaShop\Proxy\HelperFormProxy;
use Alma\PrestaShop\Services\AdminFormBuilderService;
use Alma\PrestaShop\Services\ConfigFormService;
use PHPUnit\Framework\TestCase;

class ConfigFormServiceTest extends TestCase
{
    /**
     * @var \Alma\PrestaShop\Services\ConfigFormService
     */
    protected $configFormService;

    public function setUp()
    {
        $this->helperFormMock = $this->createMock(\HelperForm::class);
        $this->moduleMock = $this->createMock(\Module::class);
        $this->contextMock = $this->createMock(\Context::class);
        $this->linkMock = $this->createMock(\Link::class);
        $this->contextMock->link = $this->linkMock;
        $this->adminFormBuilderServiceMock = $this->createMock(AdminFormBuilderService::class);
        $this->feePlanModelMock = $this->createMock(FeePlanModel::class);
        $this->helperFormProxyMock = $this->createMock(HelperFormProxy::class);
        $this->configFormService = new ConfigFormService(
            $this->moduleMock,
            $this->contextMock,
            $this->adminFormBuilderServiceMock,
            $this->feePlanModelMock,
            $this->helperFormProxyMock
        );
    }

    public function testGetRenderHtmlWithoutFeePlans()
    {
        $this->moduleMock->name = 'alma';
        $this->helperFormProxyMock->expects($this->once())
            ->method('setModule')
            ->with($this->moduleMock);
        $this->helperFormProxyMock->expects($this->once())
            ->method('setTable')
            ->with('alma_config');
        $this->helperFormProxyMock->expects($this->once())
            ->method('setDefaultFormLanguage')
            ->with(1);
        $this->helperFormProxyMock->expects($this->once())
            ->method('setAllowEmployeeFormLang')
            ->with(null);
        $this->helperFormProxyMock->expects($this->once())
            ->method('setSubmitAction')
            ->with('alma_config_form');
        $this->helperFormProxyMock->expects($this->once())
            ->method('setCurrentIndex')
            ->with('http://prestashop-a-1-7-8-7.local.test/almin/index.php?controller=AdminModules&configure=alma&tab_module=payments_gateways&module_name=alma');
        $this->helperFormProxyMock->expects($this->once())
            ->method('setToken')
            ->with('token');
        $this->helperFormProxyMock->expects($this->once())
            ->method('setFieldsValue')
            ->with([
                'ALMA_LIVE_API_KEY' => 'live_api_key',
                'ALMA_TEST_API_KEY' => 'test_api_key',
                'ALMA_API_MODE' => 'api_mode',
            ]);
        $this->helperFormProxyMock->expects($this->once())
            ->method('setLanguages')
            ->with([
                [
                    'id_lang' => 1,
                    'iso_code' => 'en',
                ],
            ]);

        $formFields = [
            [
                'form' => [
                    'legend' => [
                        'title' => 'Alma Configuration',
                    ],
                    'input' => [
                        [
                            'type' => 'text',
                            'label' => 'Live API Key',
                            'name' => 'ALMA_LIVE_API_KEY',
                            'required' => true,
                        ],
                        [
                            'type' => 'text',
                            'label' => 'Test API Key',
                            'name' => 'ALMA_TEST_API_KEY',
                            'required' => true,
                        ],
                        [
                            'type' => 'select',
                            'label' => 'API Mode',
                            'name' => 'ALMA_API_MODE',
                            'required' => true,
                            'options' => [
                                'query' => [
                                    [
                                        'id' => 'live',
                                        'name' => 'Live',
                                    ],
                                    [
                                        'id' => 'test',
                                        'name' => 'Test',
                                    ],
                                ],
                                'id' => 'id',
                                'name' => 'name',
                            ],
                        ],
                    ],
                    'submit' => [
                        'title' => 'Save',
                    ],
                ],
            ],
        ];
        $expected = $this->helperFormMock->generateForm($formFields);
        $this->helperFormProxyMock->expects($this->once())
            ->method('getHelperForm')
            ->willReturn($this->helperFormMock);
        $this->adminFormBuilderServiceMock->expects($this->once())
            ->method('getFormFields')
            ->willReturn($formFields);
        $this->assertEquals($expected, $this->configFormService->getRenderPaymentFormHtml());
    }
}
