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
use Alma\PrestaShop\Forms\InpageAdminFormBuilder;
use Alma\PrestaShop\Helpers\ApiHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_0_0($module)
{
    $module->registerHooks();

    try {
        ApiHelper::getMerchant($module);
    } catch (\Exception $e) {
    }

    // Migration value option of In-Page v1 to In-Page v2
    SettingsHelper::updateValue(
        InpageAdminFormBuilder::ALMA_ACTIVATE_INPAGE,
        Configuration::get('ALMA_ACTIVATE_FRAGMENT')
    );

    if (version_compare(_PS_VERSION_, '1.5.5.0', '<')) {
        Tools::clearCache();

        return $module->uninstallTabs() && $module->installTabs();
    }

    if (version_compare(_PS_VERSION_, ConstantsHelper::PRESTASHOP_VERSION_1_7_0_2, '<=')) {
        Tools::clearSmartyCache();
        if (version_compare(_PS_VERSION_, '1.6.0.2', '>')) {
            Tools::clearXMLCache();
        }

        return $module->uninstallTabs() && $module->installTabs();
    }

    Tools::clearAllCache();
    Tools::clearXMLCache();

    return $module->uninstallTabs() && $module->installTabs();
}
