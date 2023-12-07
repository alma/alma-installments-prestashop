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

namespace Alma\PrestaShop\Controllers\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\Helpers\Admin\InsuranceHelper as AdminInsuranceHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;

class DisplayProductActionsHookController extends FrontendHookController
{
    /** @var Alma */
    protected $module;

    /**
     * @var InsuranceHelper
     */
    protected $insuranceHelper;
    /**
     * @var AdminInsuranceHelper
     */
    private $adminInsuranceHelper;

    /**
     * @param $module
     */
    public function __construct($module)
    {
        $this->insuranceHelper = new InsuranceHelper();
        $this->adminInsuranceHelper = new AdminInsuranceHelper($module);
        parent::__construct($module);
    }

    /**
     * @return bool
     */
    public function canRun()
    {
        return parent::canRun()
            && \Tools::strtolower($this->currentControllerName()) == 'product'
            && $this->insuranceHelper->isInsuranceAllowedInProductPage();
    }

    /**
     * @param $params
     *
     * @return mixed
     * @throws \PrestaShopException
     */
    public function run($params)
    {
        $addToCartLink = '';
        $oldPSVersion = false;
        $settings = json_encode($this->adminInsuranceHelper->mapDbFieldsWithIframeParams());

        if (version_compare(_PS_VERSION_, '1.7', '<')) {

            /**
             * @var \LinkCore $link
             */
            $link = new \Link;
            $ajaxAddToCart = $link->getModuleLink('alma', 'insurance', ["action" => "addToCartPS16"]);
            $addToCartLink = ' data-link16="' . $ajaxAddToCart . '" data-token="'.\Tools::getToken(false).'" ';
            $oldPSVersion = true;

        }

        $this->context->smarty->assign([
            'addToCartLink' => $addToCartLink,
            'oldPSVersion' => $oldPSVersion,
            'settingsInsurance' => $settings,
            'iframeUrl' => $this->adminInsuranceHelper->envUrl() . ConstantsHelper::FO_IFRAME_WIDGET_INSURANCE_PATH,
            'scriptModalUrl' => $this->adminInsuranceHelper->envUrl() . ConstantsHelper::SCRIPT_MODAL_WIDGET_INSURANCE_PATH,
        ]);

        return $this->module->display($this->module->file, 'displayProductActions.tpl');
    }
}
