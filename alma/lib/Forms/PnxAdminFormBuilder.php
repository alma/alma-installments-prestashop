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

namespace Alma\PrestaShop\Forms;

use Alma\API\Entities\FeePlan;
use Alma\PrestaShop\Builders\Helpers\PriceHelperBuilder;
use Alma\PrestaShop\Builders\Helpers\SettingsHelperBuilder;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class PnxAdminFormBuilder
 */
class PnxAdminFormBuilder extends AbstractAlmaAdminFormBuilder
{
    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @param $module
     * @param $context
     * @param $image
     * @param $config
     */
    public function __construct($module, $context, $image, $config = [])
    {
        parent::__construct($module, $context, $image, $config);

        $settingsHelperBuilder = new SettingsHelperBuilder();
        $this->settingsHelper = $settingsHelperBuilder->getInstance();

        $priceHelperBuilder = new PriceHelperBuilder();
        $this->priceHelper = $priceHelperBuilder->getInstance();
    }

    /**
     * @param $feePlan
     * @param int $duration
     *
     * @return array
     *
     * @throws \SmartyException
     */
    protected function buildPnxForm($feePlan, $duration)
    {
        /**
         * @var FeePlan $feePlan
         */
        $tabId = $key = $feePlan->getPlanKey();

        $minAmount = (int) $this->priceHelper->convertPriceFromCents($feePlan->min_purchase_amount);
        $maxAmount = (int) $this->priceHelper->convertPriceFromCents($feePlan->max_purchase_amount);

        $tpl = $this->context->smarty->createTemplate(
            "{$this->module->local_path}views/templates/hook/pnx_fees.tpl"
        );
        $tpl->assign(
            [
                'fee_plan' => (array) $feePlan,
                'min_amount' => $minAmount,
                'max_amount' => $maxAmount,
                'deferred' => $duration,
            ]
        );

        $readonly = false;
        if ($feePlan->isPayNow()) {
            $readonly = true;
        }

        return [
            $this->inputHtml($tpl, null, "$tabId-content"),
            $this->inputAlmaSwitchForm(
                "ALMA_{$key}_ENABLED",
                $this->getLabel($feePlan, $duration),
                null,
                null,
                "$tabId-content"
            ),
            $this->inputNumberForm(
                "ALMA_{$key}_MIN_AMOUNT",
                $this->module->l('Minimum amount (€)', 'PnxAdminFormBuilder'),
                $this->module->l('Minimum purchase amount to activate this plan', 'PnxAdminFormBuilder'),
                $minAmount,
                $maxAmount,
                "$tabId-content",
                $readonly
            ),
            $this->inputNumberForm(
                "ALMA_{$key}_MAX_AMOUNT",
                $this->module->l('Maximum amount (€)', 'PnxAdminFormBuilder'),
                $this->module->l('Maximum purchase amount to activate this plan', 'PnxAdminFormBuilder'),
                $minAmount,
                $maxAmount,
                "$tabId-content"
            ),
            $this->inputNumberForm(
                "ALMA_{$key}_SORT_ORDER",
                $this->module->l('Position', 'PnxAdminFormBuilder'),
                $this->module->l('Use relative values to set the order on the checkout page', 'PnxAdminFormBuilder'),
                null,
                null,
                "$tabId-content"
            ),
        ];
    }

    protected function configForm()
    {
        $return = [];
        $pnxTabs = [];
        $activeTab = null;
        $installmentsPlans = $this->config['installmentsPlans'];

        /** @var FeePlan $feePlan */
        foreach ($this->config['feePlans'] as $feePlan) {
            $tabId = $key = $feePlan->getPlanKey();

            if (!$feePlan->allowed) {
                $this->disableFeePlan($key, $installmentsPlans);
                continue;
            }
            $duration = $this->settingsHelper->getDuration($feePlan);

            $return = array_merge($return, $this->buildPnxForm($feePlan, $duration));

            $pnxTabs[$tabId] = '❌ ';
            if ($this->isEnabled($key, $installmentsPlans)) {
                $pnxTabs[$tabId] = '✅ ';
                $activeTab = $activeTab ?: $tabId;
            }
            $pnxTabs[$tabId] .= $this->getTabTitle($feePlan, $duration);
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

        return $return;
    }

    /**
     * @param $key
     * @param $installmentsPlans
     *
     * @return void
     */
    protected function disableFeePlan($key, $installmentsPlans)
    {
        unset($installmentsPlans->$key);
        SettingsHelper::updateValue('ALMA_FEE_PLANS', json_encode($installmentsPlans));
    }

    /**
     * @param int $duration
     *
     * @return string
     */
    private function getLabel($feePlan, $duration)
    {
        if ($feePlan->isPnXOnly()) {
            return sprintf(
                $this->module->l('Enable %d-installment payments', 'PnxAdminFormBuilder'),
                $feePlan->installments_count
            );
        }
        if ($feePlan->isPayLaterOnly()) {
            return sprintf(
                // PrestaShop won't detect the string if the call to `l` is multiline
                $this->module->l('Enable deferred payments +%d days', 'PnxAdminFormBuilder'),
                $duration
            );
        }
        if ($feePlan->isPayNow()) {
            return sprintf(
                $this->module->l('Enable pay now', 'PnxAdminFormBuilder'),
                $feePlan->installments_count
            );
        }

        return sprintf(
            // PrestaShop won't detect the string if the call to `l` is multiline
            $this->module->l('Enable %d-installment payments +%d-deferred days', 'PnxAdminFormBuilder'),
            $feePlan->installments_count,
            $duration
        );
    }

    /**
     * @param int $duration
     *
     * @return string
     */
    protected function getTabTitle($feePlan, $duration)
    {
        if ($feePlan->isPayNow()) {
            return sprintf(
                $this->module->l('Pay now', 'PnxAdminFormBuilder'),
                $feePlan->installments_count
            );
        }

        if ($feePlan->isPnXOnly()) {
            return sprintf(
                $this->module->l('%d-installment payments', 'PnxAdminFormBuilder'),
                $feePlan->installments_count
            );
        }

        if ($feePlan->isPayLaterOnly()) {
            // PrestaShop won't detect the string if the call to `l` is multiline
            return sprintf(
                $this->module->l('Deferred payments + %d days', 'PnxAdminFormBuilder'),
                $duration
            );
        }

        // PrestaShop won't detect the string if the call to `l` is multiline
        return sprintf(
            $this->module->l('%d-installment payments + %d-deferred days', 'PnxAdminFormBuilder'),
            $feePlan->installments_count,
            $duration
        );
    }

    protected function getTitle()
    {
        return $this->module->l('Installments plans', 'PnxAdminFormBuilder');
    }

    /**
     * @param $key
     * @param $installmentsPlans
     *
     * @return bool
     */
    protected function isEnabled($key, $installmentsPlans)
    {
        $enable = isset($installmentsPlans->$key->enabled) ? $installmentsPlans->$key->enabled : 0;

        return $enable == 1;
    }
}
