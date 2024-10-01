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
if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\Builders\Admin\InsuranceHelperBuilder;
use Alma\PrestaShop\Builders\Services\InsuranceProductServiceBuilder;
use Alma\PrestaShop\Exceptions\InsuranceProductException;
use Alma\PrestaShop\Exceptions\WrongParamsException;
use Alma\PrestaShop\Helpers\Admin\AdminInsuranceHelper;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Services\InsuranceProductService;
use Alma\PrestaShop\Traits\AjaxTrait;

class AdminAlmaInsuranceConfigurationController extends ModuleAdminController
{
    use AjaxTrait;

    /**
     * @var AdminInsuranceHelper
     */
    private $insuranceHelper;
    /**
     * @var ConfigurationHelper
     */
    private $configurationHelper;
    /**
     * @var InsuranceProductService
     */
    protected $insuranceProductService;

    public function __construct()
    {
        $this->bootstrap = true;
        $insuranceHelperBuilder = new InsuranceHelperBuilder();
        $this->insuranceHelper = $insuranceHelperBuilder->getInstance();
        $this->configurationHelper = new ConfigurationHelper();
        $insuranceProductServiceBuilder = new InsuranceProductServiceBuilder();
        $this->insuranceProductService = $insuranceProductServiceBuilder->getInstance();
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
            'iframeUrl' => $this->insuranceHelper->envUrl() . ConstantsHelper::BO_IFRAME_CONFIGURATION_INSURANCE_PATH,
            'domainInsuranceUrl' => $this->insuranceHelper->envUrl(),
            'token' => \Tools::getAdminTokenLite(ConstantsHelper::BO_CONTROLLER_INSURANCE_CONFIGURATION_CLASSNAME),
            'insuranceConfigurationController' => ConstantsHelper::BO_CONTROLLER_INSURANCE_CONFIGURATION_CLASSNAME,
            'insuranceConfigurationParams' => json_encode($this->insuranceHelper->mapDbFieldsWithIframeParams()),
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
            $savedInsuranceStatus = $config[AdminInsuranceHelper::$fieldsDbInsuranceToIframeParamNames[ConstantsHelper::ALMA_ACTIVATE_INSURANCE]];

            $this->insuranceHelper->saveConfigInsurance($config);
            $this->insuranceProductService->handleInsuranceProductState($savedInsuranceStatus);

            if ($savedInsuranceStatus === 'false') {
                $this->insuranceProductService->removeInsuranceProductsNotOrdered();
            }

            $this->ajaxRenderAndExit(json_encode([
                    'success' => true,
                    'message' => $this->module->l('Your configuration has been saved', 'AdminAlmaInsuranceConfiguration'),
                ])
            );
        } catch (InsuranceProductException $e) {
            Logger::instance()->error('[Alma] Error insurance product during change configuration: ' . $e->getMessage());
            $this->ajaxRenderAndExit(json_encode([
                    'error' => [
                        'msg' => sprintf(
                            $this->module->l('[Alma] Error insurance product during change configuration: %1$s', 'AdminAlmaInsuranceConfiguration'),
                            $e->getMessage()
                        ),
                        'code' => $e->getCode(),
                    ],
                ])
            );
        } catch (WrongParamsException $e) {
            Logger::instance()->error('[Alma] Error creating Alma configuration insurance: ' . $e->getMessage());
            $this->ajaxRenderAndExit(json_encode([
                    'error' => [
                        'msg' => sprintf(
                            $this->module->l('[Alma] Error creating configuration Alma insurance: %1$s', 'AdminAlmaInsuranceConfiguration'),
                            $e->getMessage()
                        ),
                        'code' => $e->getCode(),
                    ],
                ])
            );
        }
    }
}
