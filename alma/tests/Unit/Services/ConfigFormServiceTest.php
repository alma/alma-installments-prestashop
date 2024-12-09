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
use Alma\PrestaShop\Model\AlmaApiKeyModel;
use Alma\PrestaShop\Model\ClientModel;
use Alma\PrestaShop\Model\FeePlanModel;
use Alma\PrestaShop\Proxy\ConfigurationProxy;
use Alma\PrestaShop\Proxy\HelperFormProxy;
use Alma\PrestaShop\Proxy\ToolsProxy;
use Alma\PrestaShop\Services\AdminFormBuilderService;
use Alma\PrestaShop\Services\ConfigFormService;
use Alma\PrestaShop\Services\CustomFieldsFormService;
use Alma\PrestaShop\Services\PnxFormService;
use Alma\PrestaShop\Services\ShareOfCheckoutService;
use PHPUnit\Framework\TestCase;

class ConfigFormServiceTest extends TestCase
{
    /**
     * @var \Alma\PrestaShop\Services\ConfigFormService
     */
    protected $configFormService;
    /**
     * @var \HelperForm
     */
    protected $helperFormMock;
    /**
     * @var \Module
     */
    protected $moduleMock;
    /**
     * @var \Context
     */
    protected $contextMock;
    /**
     * @var \Link
     */
    protected $linkMock;
    /**
     * @var \AdminController
     */
    protected $controllerMock;
    /**
     * @var \Alma\PrestaShop\Services\AdminFormBuilderService
     */
    protected $adminFormBuilderServiceMock;
    /**
     * @var \Alma\PrestaShop\Model\FeePlanModel
     */
    protected $feePlanModelMock;
    /**
     * @var \Alma\PrestaShop\Helpers\CustomFieldsHelper
     */
    protected $customFieldsHelperMock;
    /**
     * @var \Alma\PrestaShop\Helpers\SettingsHelper
     */
    protected $settingsHelperMock;
    /**
     * @var \Alma\PrestaShop\Proxy\HelperFormProxy
     */
    protected $helperFormProxyMock;
    /**
     * @var \Alma\PrestaShop\Proxy\ConfigurationProxy
     */
    protected $configurationProxyMock;
    /**
     * @var \Alma\PrestaShop\Proxy\ToolsProxy
     */
    protected $toolsProxyMock;
    /**
     * @var \Alma\PrestaShop\Model\ClientModel
     */
    protected $clientModelMock;
    /**
     * @var \Alma\PrestaShop\Model\AlmaApiKeyModel
     */
    protected $almaApiKeyModelMock;
    /**
     * @var \Alma\PrestaShop\Services\ShareOfCheckoutService
     */
    protected $shareOfCheckoutServiceMock;
    /**
     * @var \Alma\PrestaShop\Services\PnxFormService
     */
    protected $pnxFormServiceMock;
    /**
     * @var \Alma\PrestaShop\Services\CustomFieldsFormService
     */
    protected $customFieldsFormServiceMock;

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
        $this->clientModelMock = $this->createMock(ClientModel::class);
        $this->almaApiKeyModelMock = $this->createMock(AlmaApiKeyModel::class);
        $this->shareOfCheckoutServiceMock = $this->createMock(ShareOfCheckoutService::class);
        $this->pnxFormServiceMock = $this->createMock(PnxFormService::class);
        $this->customFieldsFormServiceMock = $this->createMock(CustomFieldsFormService::class);
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
            $this->clientModelMock,
            $this->almaApiKeyModelMock,
            $this->shareOfCheckoutServiceMock,
            $this->pnxFormServiceMock,
            $this->customFieldsFormServiceMock,
        ])->shouldAllowMockingProtectedMethods()->makePartial();
    }

    /**
     * @return void
     */
    public function testGetRenderHtmlWithoutFeePlans()
    {
        $this->clientModelMock->expects($this->once())
            ->method('getMerchantFeePlans')
            ->willReturn([]);
        $this->moduleMock->name = 'alma';
        $this->moduleMock->tab = 'payments_gateways';
        $this->configurationProxyMock->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['PS_LANG_DEFAULT'], ['PS_BO_ALLOW_EMPLOYEE_FORM_LANG'])
            ->willReturnOnConsecutiveCalls(1, null);
        $this->linkMock->expects($this->once())
            ->method('getAdminLink')
            ->with('AdminModules', false)
            ->willReturn('https://prestashop-a-1-7-8-7.local.test/almin/index.php?controller=AdminModules');
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
            ->with('https://prestashop-a-1-7-8-7.local.test/almin/index.php?controller=AdminModules&configure=alma&tab_module=payments_gateways&module_name=alma');
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

    /**
     * @throws \Alma\PrestaShop\Exceptions\PnxFormException
     * @throws \Alma\PrestaShop\Exceptions\AlmaApiKeyException
     * @throws \Alma\PrestaShop\Exceptions\ClientException
     * @throws \Alma\PrestaShop\Exceptions\ShareOfCheckoutException
     * @throws \Alma\PrestaShop\Exceptions\MissingParameterException
     */
    public function testSaveConfigurationsOnFirstInstallationWithoutSendUrlForGatherCmsData()
    {
        $apiKeys = [
            'test' => 'test_api__key',
            'live' => 'live_api_key',
        ];
        $today = time();
        $this->configurationProxyMock->expects($this->exactly(4))
            ->method('updateValue')
            ->withConsecutive(
                ['ALMA_FULLY_CONFIGURED', '0'],
                ['ALMA_MERCHANT_ID', 'merchant_id'],
                ['ALMA_API_MODE', 'mode'],
                ['ALMA_FULLY_CONFIGURED', '1']
            );
        $this->toolsProxyMock->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(['ALMA_API_MODE'], ['_api_only'])
            ->willReturnOnConsecutiveCalls('mode', true);
        $this->almaApiKeyModelMock->expects($this->once())
            ->method('getAllApiKeySend')
            ->willReturn($apiKeys);
        $this->almaApiKeyModelMock->expects($this->once())
            ->method('checkActiveApiKeySendIsEmpty');
        $this->almaApiKeyModelMock->expects($this->once())
            ->method('checkApiKeys')
            ->with($apiKeys);
        $this->almaApiKeyModelMock->expects($this->once())
            ->method('saveApiKeys')
            ->with($apiKeys);
        $this->clientModelMock->expects($this->once())
            ->method('getMerchantId')
            ->willReturn('merchant_id');
        $this->configFormService->shouldNotReceive('updateStaticConfigurations');
        $this->customFieldsFormServiceMock->expects($this->once())
            ->method('save');
        $this->pnxFormServiceMock->expects($this->once())
            ->method('save');
        $this->shareOfCheckoutServiceMock->expects($this->once())
            ->method('handleConsent');
        $this->settingsHelperMock->expects($this->once())
            ->method('getKey')
            ->with('ALMA_CMSDATA_DATE')
            ->willReturn($today);
        $this->settingsHelperMock->expects($this->never())
            ->method('updateKey')
            ->with('ALMA_CMSDATA_DATE', $today);
        $this->configFormService->saveConfigurations();
    }

    public function testSaveConfigurationsOnFirstInstallationAndSendUrlForGatherCmsData()
    {
        $apiKeys = [
            'test' => 'test_api__key',
            'live' => 'live_api_key',
        ];
        $today = time();
        $moreOneMonthInSec = 31 * 24 * 60 * 60;
        $timeMoreOneMonth = $today - $moreOneMonthInSec;
        $this->configurationProxyMock->expects($this->exactly(4))
            ->method('updateValue')
            ->withConsecutive(
                ['ALMA_FULLY_CONFIGURED', '0'],
                ['ALMA_MERCHANT_ID', 'merchant_id'],
                ['ALMA_API_MODE', 'mode'],
                ['ALMA_FULLY_CONFIGURED', '1']
            );
        $this->toolsProxyMock->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(['ALMA_API_MODE'], ['_api_only'])
            ->willReturnOnConsecutiveCalls('mode', true);
        $this->almaApiKeyModelMock->expects($this->once())
            ->method('getAllApiKeySend')
            ->willReturn($apiKeys);
        $this->almaApiKeyModelMock->expects($this->once())
            ->method('checkActiveApiKeySendIsEmpty');
        $this->almaApiKeyModelMock->expects($this->once())
            ->method('checkApiKeys')
            ->with($apiKeys);
        $this->almaApiKeyModelMock->expects($this->once())
            ->method('saveApiKeys')
            ->with($apiKeys);
        $this->clientModelMock->expects($this->once())
            ->method('getMerchantId')
            ->willReturn('merchant_id');
        $this->configFormService->shouldNotReceive('updateStaticConfigurations');
        $this->customFieldsFormServiceMock->expects($this->once())
            ->method('save');
        $this->pnxFormServiceMock->expects($this->once())
            ->method('save');
        $this->shareOfCheckoutServiceMock->expects($this->once())
            ->method('handleConsent');
        $this->settingsHelperMock->expects($this->once())
            ->method('getKey')
            ->with('ALMA_CMSDATA_DATE')
            ->willReturn($timeMoreOneMonth);
        $this->settingsHelperMock->expects($this->once())
            ->method('updateKey')
            ->with('ALMA_CMSDATA_DATE', $today);
        $this->configFormService->saveConfigurations();
    }
}
