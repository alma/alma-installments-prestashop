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

use Alma\PrestaShop\Builders\Helpers\SettingsHelperBuilder;
use Alma\PrestaShop\Forms\InpageAdminFormBuilder;
use Alma\PrestaShop\Helpers\ConstantsHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_4_5_0()
{
    require_once _PS_MODULE_DIR_ . 'alma/upgrade/autoload_upgrade.php';

    $settingsHelperBuilder = new SettingsHelperBuilder();
    $settingsHelper = $settingsHelperBuilder->getInstance();

    // Add default selector for In-Page
    $settingsHelper->updateKey(
        InpageAdminFormBuilder::ALMA_INPAGE_PAYMENT_BUTTON_SELECTOR,
        InpageAdminFormBuilder::ALMA_INPAGE_DEFAULT_VALUE_PAYMENT_BUTTON_SELECTOR
    );
    $settingsHelper->updateKey(
        InpageAdminFormBuilder::ALMA_INPAGE_PLACE_ORDER_BUTTON_SELECTOR,
        InpageAdminFormBuilder::ALMA_INPAGE_DEFAULT_VALUE_PLACE_ORDER_BUTTON_SELECTOR
    );

    if (version_compare(_PS_VERSION_, ConstantsHelper::PRESTASHOP_VERSION_1_7_0_2, '>')) {
        Tools::clearAllCache();
        Tools::clearXMLCache();
    }

    return true;
}
