<?php
/**
 * 2018-2021 Alma SAS
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
 * @copyright 2018-2021 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Utils;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class PnxAdminFormBuilder
 *
 * @package Alma\PrestaShop\Utils
 */
class PnxAdminFormBuilder extends AbstractAlmaAdminFormBuilder
{
    protected function configForm() {
        $return = [];
        $pnxTabs = [];
        $activeTab = null;
        $installmentsPlans = $this->config['installmentsPlans'];

        foreach ($this->config['feePlans'] as $feePlan) {
            $key = Settings::keyForFeePlan($feePlan);
            if (1 == $feePlan->installments_count && !Settings::isDeferred($feePlan)) {
                continue;
            }

            // Disable and hide disallowed fee plans
            if (!$feePlan->allowed) {
                unset($installmentsPlans->$key);
                Settings::updateValue('ALMA_FEE_PLANS', json_encode($installmentsPlans));

                continue;
            }

            $tabId = $key;

            // PrestaShop won't detect the string if the call to `l` is multiline
            // phpcs:ignore
            $tabTitle = sprintf($this->module->l('%d-installment payments', 'GetContentHookController'), $feePlan->installments_count);
            $duration = Settings::getDuration($feePlan);
            $label = sprintf(
                $this->module->l('Enable %d-installment payments', 'GetContentHookController'),
                $feePlan->installments_count
            );
            if (Settings::isDeferred($feePlan)) {
                if ($feePlan->installments_count == 1) {
                    // PrestaShop won't detect the string if the call to `l` is multiline
                    // phpcs:ignore
                    $tabTitle = sprintf($this->module->l('Deferred payments + %d days', 'GetContentHookController'), $duration);
                    $label = sprintf(
                        $this->module->l('Enable deferred payments +%d days', 'GetContentHookController'),
                        $duration
                    );
                } else {
                    // PrestaShop won't detect the string if the call to `l` is multiline
                    // phpcs:ignore
                    $tabTitle = sprintf($this->module->l('%d-installment payments + %d-deferred days', 'GetContentHookController'), $feePlan->installments_count, $duration);
                    $label = sprintf(
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        // phpcs:ignore
                        $this->module->l('Enable %d-installment payments +%d-deferred days', 'GetContentHookController'),
                        $feePlan->installments_count,
                        $duration
                    );
                }
            }

            $enable = isset($installmentsPlans->$key->enabled) ? $installmentsPlans->$key->enabled : 0;
            if (1 == $enable) {
                $pnxTabs[$tabId] = '✅ ' . $tabTitle;
                $activeTab = $activeTab ?: $tabId;
            } else {
                $pnxTabs[$tabId] = '❌ ' . $tabTitle;
            }

            $minAmount = (int) almaPriceFromCents($feePlan->min_purchase_amount);
            $maxAmount = (int) almaPriceFromCents($feePlan->max_purchase_amount);

            $tpl = $this->context->smarty->createTemplate(
                "{$this->module->local_path}views/templates/hook/pnx_fees.tpl"
            );
            $tpl->assign(
                ['fee_plan' => (array) $feePlan,
                'min_amount' => $minAmount,
                'max_amount' => $maxAmount,
                'deferred' => $duration, ]
            );

            array_push($return,
                $this->inputHtml($tpl, null, "$tabId-content"),
                $this->inputSwitchForm(
                    "ALMA_${key}_ENABLED",
                    $label,
                    null,
                    null,
                    "$tabId-content"
                ),
                $this->inputNumberForm(
                    "ALMA_${key}_MIN_AMOUNT",
                    $this->module->l('Minimum amount (€)', 'GetContentHookController'),
                    $this->module->l('Minimum purchase amount to activate this plan', 'GetContentHookController'),
                    $minAmount,
                    $maxAmount,
                    "$tabId-content"
                ),
                $this->inputNumberForm(
                    "ALMA_${key}_MAX_AMOUNT",
                    $this->module->l('Maximum amount (€)', 'GetContentHookController'),
                    $this->module->l('Maximum purchase amount to activate this plan', 'GetContentHookController'),
                    $minAmount,
                    $maxAmount,
                    "$tabId-content"
                ),
                $this->inputNumberForm(
                    "ALMA_${key}_SORT_ORDER",
                    $this->module->l('Position', 'GetContentHookController'),
                    $this->module->l('Use relative values to set the order on the checkout page', 'GetContentHookController'),
                    null,
                    null,
                    "$tabId-content"
                ),
            );
        }

        $tpl = $this->context->smarty->createTemplate(
            "{$this->module->local_path}views/templates/hook/pnx_tabs.tpl"
        );
        $forceTabs = false;
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $forceTabs = true;
        }
        $tpl->assign(['tabs' => $pnxTabs, 'active' => $activeTab, 'forceTabs' => $forceTabs]);

        array_unshift(
            $return,
            [
                'name' => null,
                'label' => null,
                'type' => 'html',
                'html_content' => $tpl->fetch(),
            ]
        );

        // var_dump($return);

        return $return;
    }

    protected function getTitle()
    {
        return $this->module->l('Installments plans', 'GetContentHookController');
    }
}