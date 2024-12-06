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

namespace Alma\PrestaShop\Services;

use Alma\PrestaShop\Builders\Helpers\FeePlanHelperBuilder;
use Alma\PrestaShop\Builders\Helpers\SettingsHelperBuilder;
use Alma\PrestaShop\Exceptions\PnxFormException;
use Alma\PrestaShop\Forms\PnxAdminFormBuilder;
use Alma\PrestaShop\Model\ClientModel;
use Alma\PrestaShop\Model\FeePlanModel;
use Alma\PrestaShop\Proxy\ConfigurationProxy;
use Alma\PrestaShop\Proxy\ToolsProxy;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PnxFormService
{
    /**
     * @var \Alma\PrestaShop\Model\ClientModel
     */
    private $clientModel;
    /**
     * @var \Alma\PrestaShop\Helpers\SettingsHelper
     */
    private $settingsHelper;
    /**
     * @var \Alma\PrestaShop\Proxy\ConfigurationProxy
     */
    private $configurationProxy;
    /**
     * @var \Alma\PrestaShop\Proxy\ToolsProxy
     */
    private $toolsProxy;
    /**
     * @var \Alma\PrestaShop\Helpers\FeePlanHelper
     */
    private $feePlanHelper;
    /**
     * @var \Alma\PrestaShop\Model\FeePlanModel
     */
    private $feePlanModel;

    /**
     * @param $clientModel
     * @param $settingsHelper
     * @param $configurationProxy
     * @param $toolsProxy
     * @param $feePlanHelper
     * @param $feePlanModel
     */
    public function __construct(
        $clientModel = null,
        $settingsHelper = null,
        $configurationProxy = null,
        $toolsProxy = null,
        $feePlanHelper = null,
        $feePlanModel = null
    ) {
        if (!$clientModel) {
            $clientModel = new ClientModel();
        }
        $this->clientModel = $clientModel;
        if (!$settingsHelper) {
            $settingsHelper = (new SettingsHelperBuilder())->getInstance();
        }
        $this->settingsHelper = $settingsHelper;
        if (!$configurationProxy) {
            $configurationProxy = new ConfigurationProxy();
        }
        $this->configurationProxy = $configurationProxy;
        if (!$toolsProxy) {
            $toolsProxy = new ToolsProxy();
        }
        $this->toolsProxy = $toolsProxy;
        if (!$feePlanHelper) {
            $feePlanHelper = (new FeePlanHelperBuilder())->getInstance();
        }
        $this->feePlanHelper = $feePlanHelper;
        if (!$feePlanModel) {
            $feePlanModel = new FeePlanModel();
        }
        $this->feePlanModel = $feePlanModel;
    }

    /**
     * Save the fee plans from the client or update it from the form
     *
     * @throws \Alma\PrestaShop\Exceptions\PnxFormException
     */
    public function save()
    {
        if ($this->toolsProxy->getValue(ConfigFormService::API_ONLY)) {
            $this->saveDefaultFeePlansFromClient();
        }
        if (!$this->toolsProxy->getValue(ConfigFormService::API_ONLY)) {
            $this->updateFeePlans();
        }
    }

    /**
     * Save default fee plan from the client
     *
     * @return void
     */
    private function saveDefaultFeePlansFromClient()
    {
        foreach ($this->clientModel->getMerchantFeePlans() as $feePlan) {
            $installment = $feePlan->installments_count;

            if (
                3 == $installment
                && !$this->settingsHelper->isDeferred($feePlan)
            ) {
                $key = $this->settingsHelper->keyForFeePlan($feePlan);
                $almaPlans = [];
                $almaPlans[$key]['enabled'] = 1;
                $almaPlans[$key]['min'] = $feePlan->min_purchase_amount;
                $almaPlans[$key]['max'] = $feePlan->max_purchase_amount;
                $almaPlans[$key]['deferred_trigger_limit_days'] = $feePlan->deferred_trigger_limit_days;
                $almaPlans[$key]['order'] = 1;
                $this->configurationProxy->updateValue(PnxAdminFormBuilder::ALMA_FEE_PLANS, json_encode($almaPlans));
                break;
            }
        }
    }

    /**
     * Update the fee plans from the form
     *
     * @throws \Alma\PrestaShop\Exceptions\PnxFormException
     */
    private function updateFeePlans()
    {
        $feePlans = $this->clientModel->getMerchantFeePlans();

        try {
            $this->feePlanHelper->checkLimitsSaveFeePlans($feePlans);
        } catch (PnxFormException $e) {
            throw new PnxFormException($e->getMessage());
        }

        $feePlan = $this->feePlanModel->getFeePlanForSave($feePlans);
        $this->configurationProxy->updateValue(PnxAdminFormBuilder::ALMA_FEE_PLANS, json_encode($feePlan));
    }
}
