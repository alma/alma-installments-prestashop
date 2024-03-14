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

use Alma\PrestaShop\Helpers\DateHelper;
use Alma\PrestaShop\Helpers\OrderHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ShareOfCheckoutHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Logger;

class DisplayBackOfficeHeaderHookController extends FrontendHookController
{
    /**
     * @var ShareOfCheckoutHelper
     */
    protected $socHelper;

    /**
     *
     */
    public function __construct()
    {
        $orderHelper = new OrderHelper();
        $this->socHelper = new ShareOfCheckoutHelper($orderHelper);

        parent::__construct();
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
        $this->context->controller->setMedia();
        $this->context->controller->addCSS($this->module->_path . 'views/css/admin/_configure/helpers/form/form.css', 'all');
        $this->context->controller->addCSS($this->module->_path . 'views/css/admin/almaPage.css', 'all');
        $this->context->controller->addJS($this->module->_path . 'views/js/admin/alma.js');


        if ($this->socHelper->isSocActivated()) {
            $this->socHelper->sendSocData();
        }
    }
}
