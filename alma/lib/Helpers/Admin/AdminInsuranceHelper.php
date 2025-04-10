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

namespace Alma\PrestaShop\Helpers\Admin;

use Alma\PrestaShop\Exceptions\WrongParamsException;
use Alma\PrestaShop\Factories\ModuleFactory;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use phpDocumentor\Reflection\DocBlock\Tags\Deprecated;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @deprecated We will remove insurance
 */
class AdminInsuranceHelper
{
    /**
     * Insurance form fields for mapping
     *
     * @var string[]
     */
    public static $fieldsDbInsuranceToIframeParamNames = [
        ConstantsHelper::ALMA_ACTIVATE_INSURANCE => 'isInsuranceActivated',
        ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_PRODUCT => 'isInsuranceOnProductPageActivated',
        ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_CART => 'isInCartWidgetActivated',
        ConstantsHelper::ALMA_SHOW_INSURANCE_POPUP_CART => 'isAddToCartPopupActivated',
    ];

    /**
     * @var TabsHelper
     */
    public $tabsHelper;
    /**
     * @var ConfigurationHelper
     */
    public $configurationHelper;

    /**
     * @var ModuleFactory
     */
    private $moduleFactory;

    /**
     * @var AlmaInsuranceProductRepository
     */
    protected $almaInsuranceProductRepository;

    /**
     * @param ModuleFactory $moduleFactory
     * @param TabsHelper $tabsHelper
     * @param ConfigurationHelper $configurationHelper
     * @param AlmaInsuranceProductRepository $almaInsuranceProductRepository
     */
    public function __construct($moduleFactory, $tabsHelper, $configurationHelper, $almaInsuranceProductRepository)
    {
        $this->moduleFactory = $moduleFactory;
        $this->tabsHelper = $tabsHelper;
        $this->configurationHelper = $configurationHelper;
        $this->almaInsuranceProductRepository = $almaInsuranceProductRepository;
    }

    /**
     * @return array[]
     */
    protected function tabsInsuranceDescription()
    {
        return [
            ConstantsHelper::BO_CONTROLLER_INSURANCE_CLASSNAME => [
                'name' => $this->moduleFactory->l('Insurance', 'InsuranceHelper'),
                'parent' => ConstantsHelper::ALMA_MODULE_NAME,
                'position' => 3,
                'icon' => 'security',
            ],
            ConstantsHelper::BO_CONTROLLER_INSURANCE_ORDERS_CLASSNAME => [
                'name' => $this->moduleFactory->l('Orders', 'InsuranceHelper'),
                'parent' => ConstantsHelper::BO_CONTROLLER_INSURANCE_CLASSNAME,
                'position' => 2,
                'icon' => 'shopping_basket',
            ],
            ConstantsHelper::BO_CONTROLLER_INSURANCE_ORDERS_DETAILS_CLASSNAME => [
                'name' => false,
                'parent' => ConstantsHelper::BO_CONTROLLER_INSURANCE_CLASSNAME,
                'position' => null,
                'icon' => null,
            ],
            ConstantsHelper::BO_CONTROLLER_INSURANCE_ORDERS_LIST_CLASSNAME => [
                'name' => false,
                'parent' => ConstantsHelper::BO_CONTROLLER_INSURANCE_CLASSNAME,
                'position' => null,
                'icon' => null,
            ],
        ];
    }

    /**
     * @param int $isAllowInsurance
     *
     * @return bool|null
     *
     * @throws \PrestaShopException
     */
    public function handleBOMenu($isAllowInsurance)
    {
        // Remove tab if the tab exists and we are not allowed to have it
        if (!$isAllowInsurance) {
            $this->tabsHelper->uninstallTabs($this->tabsInsuranceDescription());
        }

        // Add tab if the tab not exists and we are allowed to have it
        if ($isAllowInsurance) {
            $this->tabsHelper->installTabs($this->tabsInsuranceDescription());
            // Uninstall Insurance Configure tab everytime
            $this->tabsHelper->uninstallTabs([
                ConstantsHelper::BO_CONTROLLER_INSURANCE_CONFIGURATION_CLASSNAME => [
                    'name' => $this->moduleFactory->l('Configure', 'InsuranceHelper'),
                    'parent' => ConstantsHelper::BO_CONTROLLER_INSURANCE_CLASSNAME,
                    'position' => 1,
                    'icon' => 'tune',
                ]
            ]);
        }

        return null;
    }

    /**
     * Instantiate default db values if insurance is activated or remove it
     *
     * @param bool $isAllowInsurance
     *
     * @return void
     */
    public function handleDefaultInsuranceFieldValues($isAllowInsurance)
    {
        $isAlmaInsuranceActivated = $this->configurationHelper->hasKey(ConstantsHelper::ALMA_ACTIVATE_INSURANCE);

        // If insurance is allowed and do not exist in db
        if (
            $isAllowInsurance
            && !$isAlmaInsuranceActivated
        ) {
            foreach (ConstantsHelper::$fieldsBoInsurance as $configKey) {
                $this->configurationHelper->updateValue($configKey, 0);
            }
        }

        // If insurance is not allowed and exists in db
        if (
            !$isAllowInsurance
            && $isAlmaInsuranceActivated
        ) {
            $this->configurationHelper->deleteByNames(ConstantsHelper::$fieldsBoInsurance);
        }
    }

    /**
     * @return string
     */
    public function envUrl()
    {
        if (SettingsHelper::getActiveMode() === ALMA_MODE_LIVE) {
            return ConstantsHelper::DOMAIN_URL_INSURANCE_LIVE;
        }

        return ConstantsHelper::DOMAIN_URL_INSURANCE_TEST;
    }

    /**
     * @return array
     *
     * @throws \PrestaShopException
     */
    public function mapDbFieldsWithIframeParams()
    {
        $mapParams = [];
        $fieldsBoInsurance = $this->configurationHelper->getMultiple(ConstantsHelper::$fieldsBoInsurance);

        foreach ($fieldsBoInsurance as $fieldName => $fieldValue) {
            $configKey = static::$fieldsDbInsuranceToIframeParamNames[$fieldName];
            $mapParams[$configKey] = (bool) $fieldValue;
        }

        return $mapParams;
    }

    /**
     * @param array $configKeys
     * @param array $dbFields
     *
     * @return void
     */
    protected function saveBOFormValues($configKeys, $dbFields)
    {
        foreach ($configKeys as $configKey => $configValue) {
            $this->configurationHelper->updateValue(
                $dbFields[$configKey],
                (int) filter_var($configValue, FILTER_VALIDATE_BOOLEAN)
            );
        }
    }

    /**
     * @param array $config
     *
     * @return void
     *
     * @throws WrongParamsException
     */
    public function saveConfigInsurance($config)
    {
        $dbFields = array_flip(static::$fieldsDbInsuranceToIframeParamNames);
        $diffKeysArray = array_diff_key($config, $dbFields);

        if (!empty($diffKeysArray)) {
            $this->setHeader();
            throw new WrongParamsException($this->moduleFactory, $diffKeysArray);
        }

        $this->saveBOFormValues($config, $dbFields);
    }

    /**
     * @codeCoverageIgnore because it's a method to set header
     *
     * @return void
     */
    public function setHeader()
    {
        header('HTTP/1.1 401 Unauthorized request');
    }
}
