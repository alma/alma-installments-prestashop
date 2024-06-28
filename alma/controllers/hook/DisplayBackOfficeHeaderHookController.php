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

use Alma\PrestaShop\Builders\Admin\InsuranceHelperBuilder as AdminInsuranceHelperBuilder;
use Alma\PrestaShop\Builders\Helpers\InsuranceHelperBuilder;
use Alma\PrestaShop\Builders\Helpers\ShareOfCheckoutHelperBuilder;
use Alma\PrestaShop\Helpers\Admin\InsuranceHelper as AdminInsuranceHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Helpers\ShareOfCheckoutHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;

class DisplayBackOfficeHeaderHookController extends FrontendHookController
{
    /**
     * @var ShareOfCheckoutHelper
     */
    protected $socHelper;
    /**
     * @var InsuranceHelper
     */
    protected $insuranceHelper;
    /**
     * @var AdminInsuranceHelper
     */
    protected $adminInsuranceHelper;

    public function __construct($module)
    {
        $shareOfCheckoutHelperBuilder = new ShareOfCheckoutHelperBuilder();
        $this->socHelper = $shareOfCheckoutHelperBuilder->getInstance();

        $insuranceHelperBuilder = new InsuranceHelperBuilder();
        $this->insuranceHelper = $insuranceHelperBuilder->getInstance();

        $adminInsuranceHelperBuilder = new AdminInsuranceHelperBuilder();
        $this->adminInsuranceHelper = $adminInsuranceHelperBuilder->getInstance();

        parent::__construct($module);
    }

    /**
     * Condition to run the Controller
     *
     * @return bool
     */
    public function canRun()
    {
        return true;
    }

    /**
     * Run Controller
     *
     * @param array $params
     *
     * @return void
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function run($params)
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $this->context->controller->setMedia();
        }
        $this->context->controller->addCSS($this->module->_path . 'views/css/admin/_configure/helpers/form/form.css', 'all');
        $this->context->controller->addCSS($this->module->_path . 'views/css/admin/almaPage.css', 'all');
        $this->context->controller->addJS($this->module->_path . 'views/js/admin/alma.js');

        if ($this->socHelper->isSocActivated()) {
            $this->socHelper->sendSocData();
        }

        if ($this->insuranceHelper->isInsuranceActivated()) {
            $this->context->controller->addJS($this->module->_path . 'views/js/admin/components/modal.js');
            $this->context->controller->addJS($this->module->_path . 'views/js/admin/alma-insurance-orders.js');
        }

        $this->context->controller->addJS($this->module->_path . 'views/js/admin/alma-insurance-configuration.js');
        $this->context->smarty->assign([
            'urlScriptInsuranceModal' => $this->adminInsuranceHelper->envUrl() . ConstantsHelper::SCRIPT_MODAL_WIDGET_INSURANCE_PATH,
        ]);

        return $this->module->display($this->module->file, 'DisplayBackOfficeHeader.tpl');
    }
}
