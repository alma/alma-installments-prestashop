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

use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use PrestaShop\PrestaShop\Adapter\Entity\Tab;

class InsuranceHelper
{
    /**
     * @var array
     */
    protected static $tabInsuranceDescription = [
        'position' => 3,
        'icon' => 'security',
    ];

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
    private $tabsHelper;
    /**
     * @var ConfigurationHelper
     */
    private $configurationHelper;

    public function __construct()
    {
        $this->tabsHelper = new TabsHelper();
        $this->configurationHelper = new ConfigurationHelper();
    }

    /**
     * @param int $isAllowInsurance
     *
     * @return bool|null
     *
     * @throws \PrestaShopException
     */
    public function handleBOMenu($module, $isAllowInsurance)
    {
        /**
         * @var Tab|object $tab
         */
        $tab = \Tab::getInstanceFromClassName(ConstantsHelper::BO_CONTROLLER_INSURANCE_CLASSNAME);

        // Remove tab if the tab exists and we are not allowed to have it
        if (
            $tab->id
            && !$isAllowInsurance
        ) {
            return $this->tabsHelper->uninstallTab(ConstantsHelper::BO_CONTROLLER_INSURANCE_CLASSNAME);
        }

        // Add tab if the tab not exists and we are allowed to have it
        if (
            !$tab->id
            && $isAllowInsurance
        ) {
            return $this->tabsHelper->installTab(
                ConstantsHelper::ALMA_MODULE_NAME,
                ConstantsHelper::BO_CONTROLLER_INSURANCE_CLASSNAME,
                $module->l('Insurance'),
                ConstantsHelper::ALMA_MODULE_NAME,
                static::$tabInsuranceDescription['position'],
                static::$tabInsuranceDescription['icon']
            );
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
    public function constructIframeUrlWithParams()
    {
        return sprintf(
            '%s?%s',
            ConstantsHelper::BO_URL_IFRAME_CONFIGURATION_INSURANCE,
            http_build_query($this->mapDbFieldsWithIframeParams())
        );
    }

    /**
     * @return mixed
     */
    public function mapDbFieldsWithIframeParams()
    {
        $mapParams = [];
        $fieldsBoInsurance = $this->configurationHelper->getMultiple(ConstantsHelper::$fieldsBoInsurance);

        foreach ($fieldsBoInsurance as $fieldName => $fieldValue) {
            $mapParams[self::$fieldsDbInsuranceToIframeParamNames[$fieldName]] = (bool) $fieldValue ? 'true' : 'false';
        }

        return $mapParams;
    }
}
