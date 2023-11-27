<?php
/**
 * 2018-2023 Alma SAS.
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
 * @copyright 2018-2023 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\Helpers\Admin\InsuranceHelper;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Traits\AjaxTrait;

class AdminAlmaInsuranceConfigurationController extends ModuleAdminController
{
    use AjaxTrait;

    /**
     * @var InsuranceHelper
     */
    private $insuranceHelper;
    /**
     * @var ConfigurationHelper
     */
    private $configurationHelper;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->insuranceHelper = new InsuranceHelper($this->module);
        $this->configurationHelper = new ConfigurationHelper();
        parent::__construct();
    }

    /**
     * @return void
     *
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign([
            'iframeUrl' => $this->insuranceHelper->constructIframeUrlWithParams(),
            'token' => \Tools::getAdminTokenLite(ConstantsHelper::BO_CONTROLLER_INSURANCE_CONFIGURATION_CLASSNAME),
            'domainInsuranceUrl' => ConstantsHelper::DOMAIN_URL_INSURANCE,
            'insuranceConfigurationController' => ConstantsHelper::BO_CONTROLLER_INSURANCE_CONFIGURATION_CLASSNAME

        ]);

        $content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'alma/views/templates/admin/insurance.tpl');

        $this->context->smarty->assign([
            'content' => $this->content . $content,
        ]);
    }

    /**
     * @return void
     *
     * @throws PrestaShopException
     */
    public function ajaxProcessSaveConfigInsurance()
    {
        try {
            $config = Tools::getValue('config');

            $this->insuranceHelper->saveConfigInsurance($config);

            $this->ajaxRenderAndExit(json_encode([
                    'success' => true,
                    'message' => $this->module->l('Your configuration has been saved'),
                ])
            );
        } catch (\Exception $e) {
            Logger::instance()->error('Error creating Alma configuration insurance: ' . $e->getMessage());
            $this->ajaxRenderAndExit(json_encode([
                    'error' => [
                        'msg' => $this->module->l('Error creating Alma configuration insurance: ' . $e->getMessage()),
                        'code' => $e->getCode(),
                    ],
                ])
            );
        }
    }
}
