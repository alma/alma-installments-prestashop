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

use Alma\PrestaShop\Helpers\ConstantsHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @throws \Alma\PrestaShop\Exceptions\AlmaException
 */
function upgrade_module_4_7_0($module)
{
    /* @var \Alma $module */
    require_once _PS_MODULE_DIR_ . 'alma/upgrade/autoload_upgrade.php';

    // Retrieve the current version of the module before migration
    $sql = 'SELECT version FROM ' . _DB_PREFIX_ . 'module WHERE name = "' . pSQL($module->name) . '"';
    $currentVersion = Db::getInstance()->getValue($sql);

    if (
        Module::isEnabled($module->name)
        && $currentVersion
        && version_compare($currentVersion, '1.4.3', '>=')
        && version_compare($currentVersion, '2.0.0', '<=')
    ) {
        return false;
    }

    //Start migration here

    // Create table alma_business_data
    $almaBusinessDataRepository = new \Alma\PrestaShop\Repositories\AlmaBusinessDataRepository();
    $almaBusinessDataRepository->createTable();

    if (version_compare(_PS_VERSION_, ConstantsHelper::PRESTASHOP_VERSION_1_7_0_2, '>')) {
        Tools::clearAllCache();
        Tools::clearXMLCache();
    }

    return true;
}
