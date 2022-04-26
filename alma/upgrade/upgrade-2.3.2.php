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

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_ . 'alma/vendor/autoload.php';

use Alma\API\RequestError;
use Alma\PrestaShop\API\ClientHelper;
use Alma\PrestaShop\Forms\ExcludedCategoryAdminFormBuilder;
use Alma\PrestaShop\Forms\PaymentButtonAdminFormBuilder;
use Alma\PrestaShop\Utils\CustomFieldsHelper;
use Alma\PrestaShop\Utils\Settings;

function upgrade_module_2_3_2()
{
    if (Settings::isFullyConfigured()) {
        $alma = ClientHelper::defaultInstance();

        if (!$alma) {
            return true;
        }

        $configKeys = [
            PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE,
            PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC,
            PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE,
            PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC,
            ExcludedCategoryAdminFormBuilder::ALMA_NOT_ELIGIBLE_CATEGORIES,
        ];

        try {
            foreach ($configKeys as $configKey) {
                Configuration::deleteByName($configKey);
            }

            CustomFieldsHelper::initCustomFields();
        } catch (RequestError $e) {
            return true;
        }
    }

    return true;
}
