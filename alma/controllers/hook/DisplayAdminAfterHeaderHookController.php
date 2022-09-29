<?php
/**
 * 2018-2022 Alma SAS
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
 * @copyright 2018-2022 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Controllers\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\ShareOfCheckout\OrderHelper;
use Alma\PrestaShop\ShareOfCheckout\ShareOfCheckoutHelper;
use Alma\PrestaShop\Utils\DateHelper;
use Alma\PrestaShop\Utils\Logger;
use Alma\PrestaShop\Utils\Settings;
use Configuration;
use DateTime;
use Tools;

class DisplayAdminAfterHeaderHookController extends FrontendHookController
{
    public function canRun()
    {
        return parent::canRun() &&
            (Tools::strtolower($this->currentControllerName()) == 'admindashboard' ||
            Tools::strtolower($this->currentControllerName()) == 'adminpsmbomodule' ||
            Tools::strtolower($this->currentControllerName()) == 'adminmodulesnotifications' ||
            Tools::strtolower($this->currentControllerName()) == 'adminmodulesupdates' ||
            Tools::strtolower($this->currentControllerName()) == 'adminpsmboaddons' ||
            Tools::strtolower($this->currentControllerName()) == 'adminmodulesmanage' ||
            Tools::strtolower($this->currentControllerName()) == 'adminalmacategories' ||
            Tools::getValue('configure') == 'alma' ||
            Tools::getValue('module_name') == 'alma') &&
            Settings::getMerchantId() != null;
    }

    public function run($params)
    {
        $date = new DateTime();
        $timestamp = $date->getTimestamp();

        if (!DateHelper::isSameDay($timestamp, Configuration::get('ALMA_CRONTASK'))) {
            Logger::instance()->info('Pseudo Cron Task exec to ' . $timestamp);
            $orderHelper  = new OrderHelper();
            $shareOfCheckoutHelper = new ShareOfCheckoutHelper($orderHelper);
            $shareOfCheckoutHelper->shareDays();
            Settings::updateValue('ALMA_CRONTASK', $timestamp);
        }

        if (Settings::isShareOfCheckoutSetting() === false) {
            $this->context->smarty->assign([
                'token' => Tools::getAdminTokenLite('AdminAlmaShareOfCheckout'),
            ]);

            return $this->module->display($this->module->file, 'notificationShareOfCheckout.tpl');
        }
    }
}
