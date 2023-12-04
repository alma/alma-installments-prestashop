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

namespace Alma\PrestaShop\Helpers;

use Alma\API\Entities\Merchant;
use Alma\PrestaShop\Exceptions\ActivationException;
use Alma\PrestaShop\Exceptions\ApiMerchantsException;
use Alma\PrestaShop\Exceptions\InsuranceInstallException;
use Alma\PrestaShop\Exceptions\WrongCredentialsException;
use Alma\PrestaShop\Forms\InpageAdminFormBuilder;
use Alma\PrestaShop\Helpers\Admin\InsuranceHelper;
use Alma\PrestaShop\Services\InsuranceService;
use Alma\PrestaShop\Logger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ApiHelper
{
    /**
     * @var InsuranceHelper
     */
    protected $insuranceHelper;
    /**
     * @var mixed
     */
    private $module;
    /**
     * @var InsuranceService
     */
    private $insuranceService;
    /**
     * @var ConfigurationHelper
     */
    private $configurationHelper;

    /**
     * @param $module
     */
    public function __construct($module)
    {
        $this->module = $module;
        $this->insuranceHelper = new InsuranceHelper($module);
        $this->insuranceService = new InsuranceService();
        $this->configurationHelper = new ConfigurationHelper();
    }

    /**
     * @param null $alma
     *
     * @return Merchant|null
     *
     * @throws ActivationException
     * @throws ApiMerchantsException
     * @throws WrongCredentialsException
     * @throws \PrestaShopException
     */
    public function getMerchant($alma = null)
    {
        if (!$alma) {
            $alma = ClientHelper::defaultInstance();
        }

        if (!$alma) {
            return null;
        }

        try {
            /**
             * @var Merchant $merchant
             */
            $merchant = $alma->merchants->me();
        } catch (\Exception $e) {
            if ($e->response && 401 === $e->response->responseCode) {
                throw new WrongCredentialsException($this->module);
            }

            throw new ApiMerchantsException($this->module->l('Alma encountered an error when fetching merchant status, please check your api keys or retry later.', 'GetContentHookController'), $e->getCode(), $e);
        }

        if (!$merchant->can_create_payments) {
            throw new ActivationException($this->module);
        }

        $this->saveFeatureFlag(
            $merchant,
            'cms_allow_inpage',
            ConstantsHelper::ALMA_ALLOW_INPAGE,
            InpageAdminFormBuilder::ALMA_ACTIVATE_INPAGE
        );

        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $this->handleInsuranceFlag($merchant);
        }

        return $merchant;
    }

    /**
     * @param Merchant $merchant
     * @return void
     * @throws \PrestaShopException
     */
    protected function handleInsuranceFlag($merchant)
    {
        try {
            $isAllowInsurance = $this->saveFeatureFlag(
                $merchant,
                'cms_insurance',
                ConstantsHelper::ALMA_ALLOW_INSURANCE,
                ConstantsHelper::ALMA_ACTIVATE_INSURANCE
            );

            if ($isAllowInsurance) {
                $this->insuranceService->installDefaultData();
            }

            $this->insuranceHelper->handleBOMenu($isAllowInsurance);
            $this->insuranceHelper->handleDefaultInsuranceFieldValues($isAllowInsurance);
        } catch (InsuranceInstallException $e) {
            Logger::instance()->error(
                sprintf(
                    '[Alma] Installation of exception has failed, message "%s", trace "%s"',
                    $e->getMessage(),
                    $e->getTraceAsString()
                )
            );
        }
    }

    /**
     * @param Merchant $merchant
     * @param string $merchantKey
     * @param string $configKey
     *
     * @return int
     */
    protected function saveFeatureFlag($merchant, $merchantKey, $configKey, $formSettingName)
    {
        $value = 1;

        if (property_exists($merchant, $merchantKey)) {
            $value = $merchant->$merchantKey;
        }

        $this->configurationHelper->updateValue($configKey, (int) $value);

        // If Inpage not allowed we need to ensure that inpage is deactivated in database
        if (0 === $value) {
            $this->configurationHelper->updateValue($formSettingName, $value);
        }

        return (int) $value;
    }
}
