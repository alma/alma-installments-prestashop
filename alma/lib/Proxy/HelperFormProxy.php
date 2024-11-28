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

namespace Alma\PrestaShop\Proxy;

use Alma\PrestaShop\Builders\Factories\ModuleFactoryBuilder;
use Alma\PrestaShop\Builders\Helpers\CustomFieldHelperBuilder;
use Alma\PrestaShop\Builders\Helpers\SettingsHelperBuilder;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Forms\ExcludedCategoryAdminFormBuilder;
use Alma\PrestaShop\Forms\InpageAdminFormBuilder;
use Alma\PrestaShop\Forms\PaymentButtonAdminFormBuilder;
use Alma\PrestaShop\Helpers\SettingsHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

class HelperFormProxy
{
    /**
     * @var \Alma
     */
    private $module;
    /**
     * @var \Context
     */
    private $context;
    /**
     * @var \Alma\PrestaShop\Helpers\CustomFieldsHelper|mixed
     */
    private $customFieldsHelper;
    /**
     * @var \Alma\PrestaShop\Helpers\SettingsHelper|mixed
     */
    private $settingsHelper;
    /**
     * @var \HelperForm|mixed|null
     */
    private $helperForm;

    public function __construct(
        $module = null,
        $context = null,
        $customFieldsHelper = null,
        $settingsHelper = null,
        $helperForm = null
    ) {
        if (!$module) {
            $module = (new ModuleFactoryBuilder())->getInstance();
        }
        $this->module = $module;
        if (!$context) {
            $context = new ContextFactory();
        }
        $this->context = $context;
        if (!$customFieldsHelper) {
            $customFieldsHelper = (new CustomFieldHelperBuilder())->getInstance();
        }
        $this->customFieldsHelper = $customFieldsHelper;
        if (!$settingsHelper) {
            $settingsHelper = (new SettingsHelperBuilder())->getInstance();
        }
        $this->settingsHelper = $settingsHelper;
        if (!$helperForm) {
            $helperForm = new \HelperForm();
        }
        $this->helperForm = $helperForm;
    }

    /**
     * Use the HelperForm from Prestashop to build the default data for configuration form
     *
     * @return \HelperForm
     */
    public function getHelperForm()
    {
        $this->helperForm->module = $this->module;
        $this->helperForm->table = 'alma_config';
        $this->helperForm->default_form_language = (int) \Configuration::get('PS_LANG_DEFAULT');
        $this->helperForm->allow_employee_form_lang = (int) \Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $this->helperForm->submit_action = 'alma_config_form';

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->helperForm->base_folder = 'helpers/form/15/';
            $this->context->controller->addCss(_MODULE_DIR_ . $this->module->name . '/views/css/admin/tabs.css');
        }
        $this->helperForm->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->module->name
            . '&tab_module=' . $this->module->tab
            . '&module_name=' . $this->module->name;

        $this->helperForm->token = \Tools::getAdminTokenLite('AdminModules');
        $this->helperForm->fields_value = $this->getFieldsValueForForm();
        $this->helperForm->languages = $this->context->controller->getLanguages();

        return $this->helperForm;
    }

    /**
     * Default fields value for the configuration form
     *
     * @return array
     */
    protected function getFieldsValueForForm()
    {
        return [
            'ALMA_LIVE_API_KEY' => SettingsHelper::getLiveKey(),
            'ALMA_TEST_API_KEY' => SettingsHelper::getTestKey(),
            'ALMA_API_MODE' => SettingsHelper::getActiveMode(),
            PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_TITLE => $this->customFieldsHelper->getValue(
                PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_TITLE
            ),
            PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_DESC => $this->customFieldsHelper->getValue(
                PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_DESC
            ),
            PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE => $this->customFieldsHelper->getValue(
                PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE
            ),
            PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC => $this->customFieldsHelper->getValue(
                PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC
            ),
            PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE => $this->customFieldsHelper->getValue(
                PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE
            ),
            PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC => $this->customFieldsHelper->getValue(
                PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC
            ),
            PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE => $this->customFieldsHelper->getValue(
                PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE
            ),
            PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_DESC => $this->customFieldsHelper->getValue(
                PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_DESC
            ),
            InpageAdminFormBuilder::ALMA_ACTIVATE_INPAGE . '_ON' => $this->settingsHelper->isInPageEnabled(),
            InpageAdminFormBuilder::ALMA_INPAGE_PAYMENT_BUTTON_SELECTOR => $this->settingsHelper->getKey(InpageAdminFormBuilder::ALMA_INPAGE_PAYMENT_BUTTON_SELECTOR, InpageAdminFormBuilder::ALMA_INPAGE_DEFAULT_VALUE_PAYMENT_BUTTON_SELECTOR),
            InpageAdminFormBuilder::ALMA_INPAGE_PLACE_ORDER_BUTTON_SELECTOR => $this->settingsHelper->getKey(InpageAdminFormBuilder::ALMA_INPAGE_PLACE_ORDER_BUTTON_SELECTOR, InpageAdminFormBuilder::ALMA_INPAGE_DEFAULT_VALUE_PLACE_ORDER_BUTTON_SELECTOR),
            'ALMA_SHOW_DISABLED_BUTTON' => SettingsHelper::showDisabledButton(),
            'ALMA_SHOW_ELIGIBILITY_MESSAGE_ON' => SettingsHelper::showEligibilityMessage(),
            'ALMA_CART_WDGT_NOT_ELGBL_ON' => SettingsHelper::showCartWidgetIfNotEligible(),
            'ALMA_PRODUCT_WDGT_NOT_ELGBL_ON' => SettingsHelper::showProductWidgetIfNotEligible(),
            'ALMA_CATEGORIES_WDGT_NOT_ELGBL_ON' => SettingsHelper::showCategoriesWidgetIfNotEligible(),
            'ALMA_ACTIVATE_LOGGING_ON' => (bool) SettingsHelper::canLog(),
            'ALMA_SHARE_OF_CHECKOUT_STATE_ON' => SettingsHelper::getShareOfCheckoutStatus(),
            'ALMA_SHARE_OF_CHECKOUT_DATE' => SettingsHelper::getCurrentTimestamp(),
            'ALMA_STATE_REFUND' => SettingsHelper::getRefundState(),
            'ALMA_STATE_REFUND_ENABLED_ON' => SettingsHelper::isRefundEnabledByState(),
            'ALMA_STATE_TRIGGER' => SettingsHelper::getPaymentTriggerState(),
            'ALMA_PAYMENT_ON_TRIGGERING_ENABLED_ON' => $this->settingsHelper->isPaymentTriggerEnabledByState(),
            'ALMA_DESCRIPTION_TRIGGER' => SettingsHelper::getKeyDescriptionPaymentTrigger(),
            'ALMA_NOT_ELIGIBLE_CATEGORIES' => $this->customFieldsHelper->getValue(
                ExcludedCategoryAdminFormBuilder::ALMA_NOT_ELIGIBLE_CATEGORIES
            ),
            'ALMA_SHOW_PRODUCT_ELIGIBILITY_ON' => SettingsHelper::showProductEligibility(),
            'ALMA_PRODUCT_PRICE_SELECTOR' => SettingsHelper::getProductPriceQuerySelector(),
            'ALMA_WIDGET_POSITION_SELECTOR' => SettingsHelper::getProductWidgetPositionQuerySelector(),
            'ALMA_WIDGET_POSITION_CUSTOM' => SettingsHelper::isWidgetCustomPosition(),
            'ALMA_CART_WDGT_POS_SELECTOR' => SettingsHelper::getCartWidgetPositionQuerySelector(),
            'ALMA_CART_WIDGET_POSITION_CUSTOM' => SettingsHelper::isCartWidgetCustomPosition(),
            'ALMA_PRODUCT_ATTR_SELECTOR' => SettingsHelper::getProductAttrQuerySelector(),
            'ALMA_PRODUCT_ATTR_RADIO_SELECTOR' => SettingsHelper::getProductAttrRadioQuerySelector(),
            'ALMA_PRODUCT_COLOR_PICK_SELECTOR' => SettingsHelper::getProductColorPickQuerySelector(),
            'ALMA_PRODUCT_QUANTITY_SELECTOR' => SettingsHelper::getProductQuantityQuerySelector(),
            '_api_only' => true,
        ];
    }
}
