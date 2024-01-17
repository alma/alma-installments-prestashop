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

namespace Alma\PrestaShop\Helpers\Admin;

use Alma\PrestaShop\Exceptions\WrongParamsException;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

class InsuranceHelper
{
    /**
     * Insurance form fields for mapping
     *
     * @var string[]
     */
    public static $fieldsDbInsuranceToIframeParamNames = [
        ConstantsHelper::ALMA_ACTIVATE_INSURANCE => 'is_insurance_activated',
        ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_PRODUCT => 'is_insurance_on_product_page_activated',
        ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_CART => 'is_insurance_on_cart_page_activated',
        ConstantsHelper::ALMA_SHOW_INSURANCE_POPUP_CART => 'is_add_to_cart_popup_insurance_activated',
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
     * @var mixed
     */
    private $module;

    public function __construct($module)
    {
        $this->module = $module;
        $this->tabsHelper = new TabsHelper();
        $this->configurationHelper = new ConfigurationHelper();
    }

    /**
     * @return array[]
     */
    protected function tabsInsuranceDescription()
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return $this->tabsInsuranceDescriptionAfter17();
        }

        return $this->tabsInsuranceDescriptionBefore17();
    }
    /**
     * @return array[]
     */
    public function tabsInsuranceDescriptionBefore17()
    {
        return [
            ConstantsHelper::BO_CONTROLLER_INSURANCE_CONFIGURATION_CLASSNAME => [
                'name' => $this->module->l('Insurance Configuration'),
                'parent' => ConstantsHelper::ALMA_MODULE_NAME,
                'position' => 3,
                'icon' => 'security',
            ],
            ConstantsHelper::BO_CONTROLLER_INSURANCE_ORDERS_CLASSNAME => [
                'name' => $this->module->l('Insurance Orders'),
                'parent' => ConstantsHelper::ALMA_MODULE_NAME,
                'position' => 4,
                'icon' => 'shopping_basket',
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function tabsInsuranceDescriptionAfter17()
    {
        return [
            ConstantsHelper::BO_CONTROLLER_INSURANCE_CLASSNAME => [
                'name' => $this->module->l('Insurance'),
                'parent' => ConstantsHelper::ALMA_MODULE_NAME,
                'position' => 3,
                'icon' => 'security',
            ],
            ConstantsHelper::BO_CONTROLLER_INSURANCE_CONFIGURATION_CLASSNAME => [
                'name' => $this->module->l('Configuration'),
                'parent' => ConstantsHelper::BO_CONTROLLER_INSURANCE_CLASSNAME,
                'position' => 1,
                'icon' => 'tune',
            ],
            ConstantsHelper::BO_CONTROLLER_INSURANCE_ORDERS_CLASSNAME => [
                'name' => $this->module->l('Orders'),
                'parent' => ConstantsHelper::BO_CONTROLLER_INSURANCE_CLASSNAME,
                'position' => 2,
                'icon' => 'shopping_basket',
            ],
            ConstantsHelper::BO_CONTROLLER_INSURANCE_ORDERS_DETAILS_CLASSNAME => [
                'name' => false,
                'parent' => ConstantsHelper::BO_CONTROLLER_INSURANCE_CLASSNAME,
                'position' => null,
                'icon' => null,
            ]
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
        $tab = $this->tabsHelper->getInstanceFromClassName(ConstantsHelper::BO_CONTROLLER_INSURANCE_CLASSNAME);
        // Remove tab if the tab exists and we are not allowed to have it
        if (
            $tab->id
            && !$isAllowInsurance
        ) {
            $this->tabsHelper->uninstallTabs($this->tabsInsuranceDescription());
        }

        // Add tab if the tab not exists and we are allowed to have it
        if (
            !$tab->id
            && $isAllowInsurance
        ) {
            $this->tabsHelper->installTabs($this->tabsInsuranceDescription());
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
     * @return string
     *
     * @throws \PrestaShopException
     */
    public function constructIframeUrlWithParams()
    {
        return sprintf(
            '%s?%s',
            $this->envUrl() . ConstantsHelper::BO_IFRAME_CONFIGURATION_INSURANCE_PATH,
            http_build_query($this->mapDbFieldsWithIframeParams())
        );
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
            $mapParams[$configKey] = (bool) $fieldValue ? 'true' : 'false';
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
     * @return void
     *
     * @throws WrongParamsException
     */
    public function saveConfigInsurance($config)
    {
        $dbFields = array_flip(static::$fieldsDbInsuranceToIframeParamNames);
        $diffKeysArray = array_diff_key($config, $dbFields);

        if (!empty($diffKeysArray)) {
            header('HTTP/1.1 401 Unauthorized request');
            throw new WrongParamsException($this->module, $diffKeysArray);
        }

        $this->saveBOFormValues($config, $dbFields);
    }
}
