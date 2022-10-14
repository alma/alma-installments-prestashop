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
use Alma\PrestaShop\Utils\Settings;
use Tools;

class DisplayFooterHookController extends FrontendHookController
{
    public function canRun()
    {
        return parent::canRun() &&
            (Tools::strtolower($this->currentControllerName()) == 'product' || Tools::strtolower($this->currentControllerName()) == 'category') &&
            Settings::notifyPopinCustomer() &&
            Settings::getMerchantId() != null;
    }

    public function run($params)
    {
        $psVersion = getPsVersion();
        $feePlans = json_decode(Settings::getFeePlans(), true);
        $installmentPlans = [];
        $enablePlans = array_filter($feePlans, function ($plan) {
            return $plan['enabled'] == '1';
        });
        foreach ($enablePlans as $key => $plan) {
            $installmentPlans[] = $this->textInstallmentPlans($key);
        }
        $this->context->smarty->assign([
            'installmentPlans' => $installmentPlans,
            'psVersion' => $psVersion,
        ]);

        return $this->module->display($this->module->file, 'popin_payment_option.tpl');
    }

    private function textInstallmentPlans($key)
    {
        $installmentPlan = Settings::getDataFromKey($key);

        if ($installmentPlan['installmentsCount'] == '1' && $installmentPlan['deferredDays'] > 0) {
            return sprintf(
                $this->module->l('D+%1$s', 'DisplayFooterHookController'),
                $installmentPlan['deferredDays']
            );
        }
        if ($installmentPlan['installmentsCount'] == '1' && $installmentPlan['deferredMonths'] > 0) {
            return sprintf(
                $this->module->l('M+%1$s', 'DisplayFooterHookController'),
                $installmentPlan['deferredMonths']
            );
        }

        return $installmentPlan['installmentsCount'] . 'x';
    }
}
