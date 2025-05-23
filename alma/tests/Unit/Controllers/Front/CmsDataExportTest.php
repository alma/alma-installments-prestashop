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

namespace Alma\PrestaShop\Tests\Unit\Controllers\Front;

use Alma\API\Entities\MerchantData\CmsFeatures;
use Alma\API\Entities\MerchantData\CmsInfo;
use Alma\API\Lib\PayloadFormatter;
use Alma\PrestaShop\Exceptions\ValidateException;
use Alma\PrestaShop\Helpers\CmsDataHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ValidateHelper;
use Mockery;
use PHPUnit\Framework\TestCase;

include_once __DIR__ . '/../../../../controllers/front/cmsdataexport.php';

class AlmaCmsDataExportTest extends TestCase
{
    /**
     * @var ValidateHelper
     */
    protected $validateHelper;
    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;
    /**
     * @var PayloadFormatter
     */
    protected $payloadFormatter;
    /**
     * @var CmsDataHelper
     */
    protected $cmsDataHelper;

    public function setUp()
    {
        $_SERVER['HTTP_X_ALMA_SIGNATURE'] = '1234';
        $this->validateHelper = $this->createMock(ValidateHelper::class);
        $this->settingsHelper = $this->createMock(SettingsHelper::class);
        $this->settingsHelper->method('getIdMerchant');
        $this->payloadFormatter = $this->createMock(PayloadFormatter::class);
        $this->cmsDataHelper = $this->createMock(CmsDataHelper::class);

        $this->cmsDataExportMock = Mockery::mock(\AlmaCmsDataExportModuleFrontController::class)->makePartial();
        $this->cmsDataExportMock->shouldAllowMockingProtectedMethods();
    }

    public function tearDown()
    {
        $this->cmsDataExportMock = null;
        $this->validateHelper = null;
        $this->settingsHelper = null;
        Mockery::close();
        unset($_SERVER['HTTP_X_ALMA_SIGNATURE']);
    }

    /**
     * @throws \PrestaShopException
     * @throws \Alma\PrestaShop\Exceptions\CmsDataException
     */
    public function testPostProcessNoSignature()
    {
        unset($_SERVER['HTTP_X_ALMA_SIGNATURE']);
        $this->validateHelper->method('checkSignature')->willThrowException(new ValidateException('Exception'));
        $this->cmsDataExportMock->setValidateHelper($this->validateHelper);
        $this->cmsDataExportMock->setSettingsHelper($this->settingsHelper);
        $this->cmsDataExportMock->shouldReceive('ajaxRenderAndExit')
            ->with(Mockery::any(), 403)->once();
        $this->cmsDataExportMock->postProcess();
    }

    /**
     * @throws \PrestaShopException
     * @throws \Alma\PrestaShop\Exceptions\CmsDataException
     */
    public function testPostProcessCheckSignatureThrowExceptionReturn403()
    {
        $this->validateHelper->method('checkSignature')->willThrowException(new ValidateException('Exception'));
        $this->cmsDataExportMock->setValidateHelper($this->validateHelper);
        $this->cmsDataExportMock->setSettingsHelper($this->settingsHelper);
        $this->cmsDataExportMock->shouldReceive('ajaxRenderAndExit')
            ->with(Mockery::any(), 403)->once();
        $this->cmsDataExportMock->postProcess();
    }

    public function testPostProcessWithRightData()
    {
        $cmsInfoArray = [
            'cms_name' => 'Prestashop',
            'cms_version' => '1.2.3',
            'third_parties_plugins' => ['moduleList'],
            'theme_name' => 'ThemeName',
            'theme_version' => 'ThemeVersion',
            'language_name' => 'PHP',
            'language_version' => phpversion(),
            'alma_plugin_version' => '4.3.2',
            'alma_sdk_name' => 'ALMA-PHP-CLIENT',
            'alma_sdk_version' => '2.3.4',
        ];
        $cmsFeatureArray = [
            'alma_enabled' => false,
            'widget_cart_activated' => false,
            'widget_product_activated' => false,
            'used_fee_plans' => ['general_1_0_0' => ['enabled' => '1']],
            'in_page_activated' => true,
            'log_activated' => true,
            'excluded_categories' => null,
            'specific_features' => [],
            'country_restriction' => [],
            'custom_widget_css' => true,
            'is_multisite' => true,
        ];

        $this->settingsHelper
            ->method('getIdMerchant')
            ->willReturn('merchant_id');
        $this->validateHelper->method('checkSignature')->willReturn(true);
        $this->cmsDataHelper->method('getCmsInfoArray')->willReturn($cmsInfoArray);
        $this->cmsDataHelper->method('getCmsFeatureArray')->willReturn($cmsFeatureArray);
        $this->payloadFormatter
            ->expects($this->once())
            ->method('formatConfigurationPayload')
            ->with(new CmsInfo($cmsInfoArray), new CmsFeatures($cmsFeatureArray))
            ->willReturn(['test' => 'test']);
        $this->cmsDataExportMock->setValidateHelper($this->validateHelper);
        $this->cmsDataExportMock->setSettingsHelper($this->settingsHelper);
        $this->cmsDataExportMock->setPayloadFormatter($this->payloadFormatter);
        $this->cmsDataExportMock->setCmsDataHelper($this->cmsDataHelper);
        $this->cmsDataExportMock->shouldReceive('ajaxRenderAndExit')
            ->with(json_encode(['test' => 'test']), 200)->once();
        // We can't test the return value because the method exit after echo the response
        $this->cmsDataExportMock->postProcess();
    }
}
