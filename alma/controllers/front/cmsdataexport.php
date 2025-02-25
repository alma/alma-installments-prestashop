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

use Alma\API\Entities\MerchantData\CmsFeatures;
use Alma\API\Entities\MerchantData\CmsInfo;
use Alma\API\Lib\PayloadFormatter;
use Alma\PrestaShop\Builders\Helpers\SettingsHelperBuilder;
use Alma\PrestaShop\Exceptions\ValidateException;
use Alma\PrestaShop\Helpers\CmsDataHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ValidateHelper;
use Alma\PrestaShop\Traits\AjaxTrait;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * AlmaCmsDataExportModuleFrontController
 */
class AlmaCmsDataExportModuleFrontController extends ModuleFrontController
{
    use AjaxTrait;

    /**
     * @var ValidateHelper
     */
    protected $validateHelper;
    /**
     * @var \Alma\PrestaShop\Helpers\SettingsHelper
     */
    protected $settingsHelper;
    /**
     * @var \Alma\API\Lib\PayloadFormatter
     */
    protected $payloadFormatter;
    /**
     * @var CmsDataHelper
     */
    protected $cmsDataHelper;

    public function __construct()
    {
        parent::__construct();
        $this->validateHelper = new ValidateHelper();
        $this->cmsDataHelper = new CmsDataHelper();
        $this->settingsHelper = (new SettingsHelperBuilder())->getInstance();
        $this->payloadFormatter = new PayloadFormatter();
    }

    public function postProcess()
    {
        parent::postProcess();
        try {
            $signature = isset($_SERVER['HTTP_X_ALMA_SIGNATURE']) ? $_SERVER['HTTP_X_ALMA_SIGNATURE'] : '';
            $this->validateHelper->checkSignature($this->settingsHelper->getIdMerchant(), SettingsHelper::getActiveAPIKey(), $signature);
        } catch (ValidateException $e) {
            $this->ajaxRenderAndExit($e->getMessage(), 403);
            // Return is call only in test;
            return;
        }

        $cmsInfo = new CmsInfo($this->cmsDataHelper->getCmsInfoArray());
        $cmsFeature = new CmsFeatures($this->cmsDataHelper->getCmsFeatureArray());

        $payload = $this->payloadFormatter->formatConfigurationPayload($cmsInfo, $cmsFeature);
        $this->ajaxRenderAndExit(json_encode($payload), 200);
    }

    /**
     * @param $validateHelper
     *
     * @return void
     */
    public function setValidateHelper($validateHelper)
    {
        $this->validateHelper = $validateHelper;
    }

    /**
     * @param $settingsHelper
     *
     * @return void
     */
    public function setSettingsHelper($settingsHelper)
    {
        $this->settingsHelper = $settingsHelper;
    }

    /**
     * @param $payloadFormatter
     *
     * @return void
     */
    public function setPayloadFormatter($payloadFormatter)
    {
        $this->payloadFormatter = $payloadFormatter;
    }

    /**
     * @param $cmsDataHelper
     *
     * @return void
     */
    public function setCmsDataHelper($cmsDataHelper)
    {
        $this->cmsDataHelper = $cmsDataHelper;
    }
}
