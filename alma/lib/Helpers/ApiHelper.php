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

use Alma\API\Endpoints\Results\Eligibility;
use Alma\API\Entities\Merchant;
use Alma\PrestaShop\Exceptions\ActivationException;
use Alma\PrestaShop\Exceptions\ApiMerchantsException;
use Alma\PrestaShop\Exceptions\InsuranceInstallException;
use Alma\PrestaShop\Exceptions\WrongCredentialsException;
use Alma\PrestaShop\Factories\ModuleFactory;
use Alma\PrestaShop\Helpers\Admin\InsuranceHelper;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Services\InsuranceService;

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
    protected $module;
    /**
     * @var InsuranceService
     */
    protected $insuranceService;
    /**
     * @var ConfigurationHelper
     */
    protected $configurationHelper;
    /**
     * @var ClientHelper
     */
    protected $clientHelper;

    /**
     * @param ModuleFactory $moduleFactory
     * @param ClientHelper $clientHelper
     * @codeCoverageIgnore
     */
    public function __construct($moduleFactory, $clientHelper)
    {
        $this->module = $moduleFactory->getModule();
        $this->insuranceHelper = new InsuranceHelper($this->module);
        $this->insuranceService = new InsuranceService();
        $this->configurationHelper = new ConfigurationHelper();
        $this->clientHelper = $clientHelper;
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

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $this->handleInsuranceFlag($merchant);
        }

        return $merchant;
    }

    /**
     * @param Merchant $merchant
     *
     * @return void
     *
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

    /**
     * @param array $paymentData
     *
     * @return Eligibility|Eligibility[]|array
     */
    public function getPaymentEligibility($paymentData)
    {
        try {
            return $this->clientHelper->getAlmaClient()->payments->eligibility($paymentData);
        } catch (\Exception $e) {
            Logger::instance()->error(
                sprintf(
                    'Error on check cart eligibility - payload : %s - message : %s',
                    json_encode($paymentData),
                    $e->getMessage()
                )
            );
        }

        return [];
    }
}
