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

use Alma\API\RequestError;
use Alma\PrestaShop\Factories\LoggerFactory;
use Alma\PrestaShop\Forms\ShareOfCheckoutAdminFormBuilder;
use Alma\PrestaShop\Helpers\Admin\TabsHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_10_0($module)
{
    require_once _PS_MODULE_DIR_ . 'alma/upgrade/autoload_upgrade.php';

    $tabsHelper = new TabsHelper();
    /* @var \Alma $module */
    if (SettingsHelper::isFullyConfigured()) {
        $date = new DateTime();

        try {
            SettingsHelper::updateValue(ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_STATE, SettingsHelper::getShareOfCheckoutStatus());
            SettingsHelper::updateValue(ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_DATE, SettingsHelper::getCurrentTimestamp());
            SettingsHelper::updateValue('ALMA_SOC_CRON_TASK', $date->getTimestamp());
        } catch (RequestError $e) {
            return false;
        }
    }

    $module->registerHook('displayAdminAfterHeader');

    try {
        return $tabsHelper->uninstallTabs($module->dataTabs()) && $tabsHelper->installTabs($module->dataTabs());
    } catch (PrestaShopException $e) {
        LoggerFactory::instance()->error("[Alma] ERROR upgrade v2.10.0: {$e->getMessage()}");

        return false;
    }
}
