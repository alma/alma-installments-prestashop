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

use Alma\PrestaShop\Helpers\CustomFieldsHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Model\FeePlanModel;
use Alma\Prestashop\Proxy\ConfigurationProxy;
use Alma\PrestaShop\Proxy\HelperFormProxy;
use Alma\PrestaShop\Proxy\ToolsProxy;
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
        $this->controllerMock = $this->createMock(\AdminController::class);
        $this->contextMock->link = $this->linkMock;
        $this->contextMock->controller = $this->controllerMock;
        $this->adminFormBuilderServiceMock = $this->createMock(AdminFormBuilderService::class);
        $this->feePlanModelMock = $this->createMock(FeePlanModel::class);
        $this->customFieldsHelperMock = $this->createMock(CustomFieldsHelper::class);
        $this->settingsHelperMock = $this->createMock(SettingsHelper::class);
        $this->helperFormProxyMock = $this->createMock(HelperFormProxy::class);
        $this->configurationProxyMock = $this->createMock(ConfigurationProxy::class);
        $this->toolsProxyMock = $this->createMock(ToolsProxy::class);
        $this->configFormService = \Mockery::mock(ConfigFormService::class, [
            $this->moduleMock,
            $this->contextMock,
            $this->adminFormBuilderServiceMock,
            $this->feePlanModelMock,
            $this->customFieldsHelperMock,
            $this->settingsHelperMock,
            $this->helperFormProxyMock,
            $this->configurationProxyMock,
            $this->toolsProxyMock,
        ])->shouldAllowMockingProtectedMethods()->makePartial();
    }

    public function testGetRenderHtmlWithoutFeePlans()
    {
        $this->moduleMock->name = 'alma';
        $this->moduleMock->tab = 'payments_gateways';
        $this->configurationProxyMock->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['PS_LANG_DEFAULT'], ['PS_BO_ALLOW_EMPLOYEE_FORM_LANG'])
            ->willReturnOnConsecutiveCalls(1, null);
        $this->linkMock->expects($this->once())
            ->method('getAdminLink')
            ->with('AdminModules', false)
            ->willReturn('http://prestashop-a-1-7-8-7.local.test/almin/index.php?controller=AdminModules');
        $this->toolsProxyMock->expects($this->once())
            ->method('getAdminTokenLite')
            ->with('AdminModules')
            ->willReturn('token');
        $this->controllerMock->expects($this->once())
            ->method('getLanguages')
            ->willReturn([
                [
                    'id_lang' => 1,
                    'iso_code' => 'en',
                ],
            ]);
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
        $this->configFormService->shouldReceive('getFieldsValueForPaymentForm')
            ->andReturn([
                'ALMA_LIVE_API_KEY' => 'live_api_key',
                'ALMA_TEST_API_KEY' => 'test_api_key',
                'ALMA_API_MODE' => 'api_mode',
            ]);
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
            'form_field' => true,
        ];
        $this->adminFormBuilderServiceMock->expects($this->once())
            ->method('getFormFields')
            ->willReturn($formFields);
        $this->helperFormProxyMock->expects($this->once())
            ->method('getHelperForm')
            ->with($formFields)
            ->willReturn($this->helperFormMock);

        $this->assertEquals($this->helperFormMock, $this->configFormService->getRenderPaymentFormHtml());
    }
}
