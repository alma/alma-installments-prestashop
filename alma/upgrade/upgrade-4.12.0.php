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
 * @param \Alma $module
 *
 * @return bool
 */
function upgrade_module_4_12_0($module)
{
    require_once _PS_MODULE_DIR_ . 'alma/upgrade/autoload_upgrade.php';

    // Create the anti-duplication payment tracking table.
    // The UNIQUE KEY on (alma_payment_id, status) prevents duplicate order creation
    // at the SQL level, as a complement to the advisory lock (CartLockService).
    $almaPaymentRepository = new \Alma\PrestaShop\Repositories\AlmaPaymentRepository();
    $almaPaymentRepository->createTable();

    if (version_compare(_PS_VERSION_, ConstantsHelper::PRESTASHOP_VERSION_1_7_0_2, '>')) {
        Tools::clearAllCache();
        Tools::clearXMLCache();
    }

    return true;
}
