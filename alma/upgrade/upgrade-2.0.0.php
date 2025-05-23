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
if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\API\RequestError;
use Alma\PrestaShop\Factories\LoggerFactory;
use Alma\PrestaShop\Helpers\SettingsHelper;

function upgrade_module_2_0_0()
{
    require_once _PS_MODULE_DIR_ . 'alma/upgrade/autoload_upgrade.php';

    if (SettingsHelper::isFullyConfigured()) {
        $configKeys = [
            'ALMA_P2X_ENABLED',
            'ALMA_P3X_ENABLED',
            'ALMA_P4X_ENABLED',
            'ALMA_P2X_MIN_AMOUNT',
            'ALMA_P3X_MIN_AMOUNT',
            'ALMA_P4X_MIN_AMOUNT',
            'ALMA_P2X_MAX_AMOUNT',
            'ALMA_P3X_MAX_AMOUNT',
            'ALMA_P4X_MAX_AMOUNT',
            'ALMA_P2X_SORT_ORDER',
            'ALMA_P3X_SORT_ORDER',
            'ALMA_P4X_SORT_ORDER',
            'ALMA_PNX_MAX_N',
            'ALMA_IS_ELIGIBLE_MESSAGE',
            'ALMA_NOT_ELIGIBLE_MESSAGE',
        ];

        try {
            $almaPlans = [];
            for ($i = 2; $i <= 4; ++$i) {
                $key = "general_{$i}_0_0";
                $almaPlans[$key]['enabled'] = SettingsHelper::get("ALMA_P{$i}X_ENABLED", 0);
                $almaPlans[$key]['min'] = SettingsHelper::get("ALMA_P{$i}X_MIN_AMOUNT", 100);
                $almaPlans[$key]['max'] = SettingsHelper::get("ALMA_P{$i}X_MAX_AMOUNT", 500);
                $almaPlans[$key]['order'] = SettingsHelper::get("ALMA_P{$i}X_SORT_ORDER", $i);
            }
            SettingsHelper::updateValue('ALMA_FEE_PLANS', json_encode($almaPlans));

            foreach ($configKeys as $configKey) {
                Configuration::deleteByName($configKey);
            }

            SettingsHelper::updateValue(
                'ALMA_NOT_ELIGIBLE_CATEGORIES',
                Configuration::get('ALMA_IS_ELIGIBLE_MESSAGE')
            );

            Tools::clearCache();
        } catch (RequestError $e) {
            LoggerFactory::instance()->error("[Alma] ERROR upgrade v2.0.0: {$e->getMessage()}");

            return false;
        }
    }

    return true;
}
