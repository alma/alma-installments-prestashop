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

namespace Alma\PrestaShop\Helpers;

use Alma\API\Endpoints\Results\Eligibility;
use Alma\API\Entities\Merchant;
use Alma\PrestaShop\Exceptions\ActivationException;
use Alma\PrestaShop\Exceptions\ApiMerchantsException;
use Alma\PrestaShop\Exceptions\ClientException;
use Alma\PrestaShop\Exceptions\WrongCredentialsException;
use Alma\PrestaShop\Factories\LoggerFactory;
use Alma\PrestaShop\Factories\ModuleFactory;
use Alma\PrestaShop\Helpers\Admin\AdminInsuranceHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ApiHelper
{
    /**
     * @var AdminInsuranceHelper
     */
    protected $insuranceHelper;
    /**
     * @var ModuleFactory
     */
    protected $moduleFactory;
    /**
     * @var ConfigurationHelper
     */
    protected $configurationHelper;
    /**
     * @var ClientHelper
     */
    protected $clientHelper;

    /**
     * @var ToolsHelper
     */
    protected $toolsHelper;

    /**
     * @param ModuleFactory $moduleFactory
     * @param ClientHelper $clientHelper
     * @param ToolsHelper $toolsHelper
     * @param ConfigurationHelper $configurationHelper
     * @param AdminInsuranceHelper $insuranceHelper
     */
    public function __construct(
        $moduleFactory,
        $clientHelper,
        $toolsHelper,
        $configurationHelper,
        $insuranceHelper
    ) {
        $this->moduleFactory = $moduleFactory;
        $this->clientHelper = $clientHelper;
        $this->toolsHelper = $toolsHelper;
        $this->configurationHelper = $configurationHelper;
        $this->insuranceHelper = $insuranceHelper;
    }

    /**
     * @deprecated
     *
     * @param $alma
     *
     * @return Merchant
     *
     * @throws ActivationException
     * @throws ApiMerchantsException
     * @throws WrongCredentialsException
     */
    public function getMerchant($alma = null)
    {
        try {
            /**
             * @param Merchant $merchant
             */
            $merchant = $this->clientHelper->getMerchantsMe($alma);
        } catch (\Exception $e) {
            if (401 === $e->getCode()) {
                throw new WrongCredentialsException($this->moduleFactory);
            }

            throw new ApiMerchantsException($this->moduleFactory->l('Alma encountered an error when fetching merchant status, please check your api keys or retry later.', 'GetContentHookController'), $e->getCode(), $e);
        }

        if (
            !$merchant
            || !$merchant->can_create_payments
        ) {
            throw new ActivationException($this->moduleFactory);
        }

        return $merchant;
    }

    /**
     * @param Merchant $merchant
     * @param string $merchantKey
     * @param string $configKey
     *
     * @return int
     */
    public function saveFeatureFlag($merchant, $merchantKey, $configKey, $formSettingName)
    {
        $value = 1;

        if ($merchant && property_exists($merchant, $merchantKey)) {
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
            return $this->clientHelper->getPaymentEligibility($paymentData);
        } catch (\Exception $e) {
            LoggerFactory::instance()->error(
                sprintf(
                    'Error on check cart eligibility - payload : %s - message : %s',
                    json_encode($paymentData),
                    $e->getMessage()
                )
            );
        }

        return [];
    }

    /**
     * @param string $url
     *
     * @return void
     */
    public function sendUrlForGatherCmsData($url)
    {
        try {
            $this->clientHelper->sendUrlForGatherCmsData($url);
        } catch (ClientException $e) {
            LoggerFactory::instance()->error(
                sprintf(
                    '[Alma] Error sending URL for gather CMS data: %s',
                    $e->getMessage()
                )
            );
        }
    }
}
