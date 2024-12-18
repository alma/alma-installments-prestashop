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

use Alma\API\Lib\IntegrationsConfigurationsUtils;
use Alma\PrestaShop\Builders\Factories\ModuleFactoryBuilder;
use Alma\PrestaShop\Builders\Helpers\CustomFieldHelperBuilder;
use Alma\PrestaShop\Builders\Helpers\SettingsHelperBuilder;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Forms\ExcludedCategoryAdminFormBuilder;
use Alma\PrestaShop\Forms\InpageAdminFormBuilder;
use Alma\PrestaShop\Forms\PaymentButtonAdminFormBuilder;
use Alma\PrestaShop\Helpers\CmsDataHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Model\AlmaApiKeyModel;
use Alma\PrestaShop\Model\ClientModel;
use Alma\PrestaShop\Model\FeePlanModel;
use Alma\PrestaShop\Proxy\ConfigurationProxy;
use Alma\PrestaShop\Proxy\HelperFormProxy;
use Alma\PrestaShop\Proxy\ToolsProxy;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ConfigFormService
{
    const ALMA_API_MODE = 'ALMA_API_MODE';
    const ALMA_FULLY_CONFIGURED = 'ALMA_FULLY_CONFIGURED';
    const ALMA_MERCHANT_ID = 'ALMA_MERCHANT_ID';
    const API_ONLY = '_api_only';
    /**
     * @var array
     */
    const STATIC_KEY_CONFIG = [
        'ALMA_SHOW_ELIGIBILITY_MESSAGE' => [
            'action' => 'test_bool',
            'suffix' => '_ON',
        ],
        'ALMA_SHOW_PRODUCT_ELIGIBILITY' => [
            'action' => 'test_bool',
            'suffix' => '_ON',
        ],
        'ALMA_CART_WDGT_NOT_ELGBL' => [
            'action' => 'cast_bool',
            'suffix' => '_ON',
        ],
        'ALMA_PRODUCT_WDGT_NOT_ELGBL' => [
            'action' => 'cast_bool',
            'suffix' => '_ON',
        ],
        'ALMA_CATEGORIES_WDGT_NOT_ELGBL' => [
            'action' => 'cast_bool',
            'suffix' => '_ON',
        ],
        'ALMA_STATE_REFUND_ENABLED' => [
            'action' => 'cast_bool',
            'suffix' => '_ON',
        ],
        'ALMA_PAYMENT_ON_TRIGGERING_ENABLED' => [
            'action' => 'cast_bool',
            'suffix' => '_ON',
        ],
        InpageAdminFormBuilder::ALMA_ACTIVATE_INPAGE => [
            'action' => 'cast_bool',
            'suffix' => '_ON',
        ],
        InpageAdminFormBuilder::ALMA_INPAGE_PAYMENT_BUTTON_SELECTOR => 'none',
        InpageAdminFormBuilder::ALMA_INPAGE_PLACE_ORDER_BUTTON_SELECTOR => 'none',
        'ALMA_ACTIVATE_LOGGING' => [
            'action' => 'cast_bool',
            'suffix' => '_ON',
        ],
        'ALMA_WIDGET_POSITION_CUSTOM' => 'cast_bool',
        'ALMA_SHOW_DISABLED_BUTTON' => 'cast_bool',
        'ALMA_CART_WIDGET_POSITION_CUSTOM' => 'cast_bool',
        'ALMA_PRODUCT_PRICE_SELECTOR' => 'none',
        'ALMA_WIDGET_POSITION_SELECTOR' => 'none',
        'ALMA_PRODUCT_ATTR_SELECTOR' => 'none',
        'ALMA_PRODUCT_ATTR_RADIO_SELECTOR' => 'none',
        'ALMA_PRODUCT_COLOR_PICK_SELECTOR' => 'none',
        'ALMA_PRODUCT_QUANTITY_SELECTOR' => 'none',
        'ALMA_CART_WDGT_POS_SELECTOR' => 'none',
        'ALMA_STATE_REFUND' => 'none',
        'ALMA_STATE_TRIGGER' => 'none',
        'ALMA_DESCRIPTION_TRIGGER' => 'none',
    ];

    /**
     * @var \Alma\PrestaShop\Services\AdminFormBuilderService
     */
    private $adminFormBuilderService;
    /**
     * @var \Alma\PrestaShop\Model\FeePlanModel
     */
    private $feePlanModel;
    /**
     * @var HelperFormProxy
     */
    private $helperFormProxy;
    /**
     * @var \Alma
     */
    private $module;
    /**
     * @var \Context|mixed|null
     */
    private $context;
    /**
     * @var \Alma\PrestaShop\Helpers\CustomFieldsHelper|mixed|null
     */
    private $customFieldsHelper;
    /**
     * @var \Alma\PrestaShop\Helpers\SettingsHelper|mixed|null
     */
    private $settingsHelper;
    /**
     * @var \Alma\PrestaShop\Proxy\ToolsProxy|mixed|null
     */
    private $toolsProxy;
    /**
     * @var \Alma\PrestaShop\Proxy\ConfigurationProxy|mixed|null
     */
    private $configurationProxy;
    /**
     * @var \Alma\PrestaShop\Model\ClientModel|null
     */
    private $clientModel;
    /**
     * @var \Alma\PrestaShop\Model\AlmaApiKeyModel
     */
    private $almaApiKeyModel;
    /**
     * @var \Alma\PrestaShop\Services\ShareOfCheckoutService
     */
    private $shareOfCheckoutService;
    /**
     * @var \Alma\PrestaShop\Services\PnxFormService
     */
    private $pnxFormService;
    /**
     * @var \Alma\PrestaShop\Services\CustomFieldsFormService
     */
    private $customFieldsFormService;

    public function __construct(
        $module = null,
        $context = null,
        $adminFormBuilderService = null,
        $feePlanModel = null,
        $customFieldsHelper = null,
        $settingsHelper = null,
        $helperFormProxy = null,
        $configurationProxy = null,
        $toolsProxy = null,
        $clientModel = null,
        $almaApiKeyModel = null,
        $shareOfCheckoutService = null,
        $pnxFormService = null,
        $customFieldsFormService = null
    ) {
        if (!$module) {
            $module = (new ModuleFactoryBuilder())->getInstance();
        }
        $this->module = $module;
        if (!$context) {
            $context = (new ContextFactory())->getContext();
        }
        $this->context = $context;
        if (!$adminFormBuilderService) {
            $adminFormBuilderService = new AdminFormBuilderService(
                $module,
                $context,
                $this->needsAPIKey()
            );
        }
        $this->adminFormBuilderService = $adminFormBuilderService;
        if (!$feePlanModel) {
            $feePlanModel = new FeePlanModel();
        }
        $this->feePlanModel = $feePlanModel;
        if (!$customFieldsHelper) {
            $customFieldsHelper = (new CustomFieldHelperBuilder())->getInstance();
        }
        $this->customFieldsHelper = $customFieldsHelper;
        if (!$settingsHelper) {
            $settingsHelper = (new SettingsHelperBuilder())->getInstance();
        }
        $this->settingsHelper = $settingsHelper;
        if (!$helperFormProxy) {
            $helperFormProxy = new HelperFormProxy(
                $context
            );
        }
        $this->helperFormProxy = $helperFormProxy;
        if (!$configurationProxy) {
            $configurationProxy = new ConfigurationProxy();
        }
        $this->configurationProxy = $configurationProxy;
        if (!$toolsProxy) {
            $toolsProxy = new ToolsProxy();
        }
        $this->toolsProxy = $toolsProxy;
        if (!$clientModel) {
            $clientModel = ClientModel::getInstance();
        }
        $this->clientModel = $clientModel;
        if (!$almaApiKeyModel) {
            $almaApiKeyModel = new AlmaApiKeyModel();
        }
        $this->almaApiKeyModel = $almaApiKeyModel;
        if (!$shareOfCheckoutService) {
            $shareOfCheckoutService = new ShareOfCheckoutService();
        }
        $this->shareOfCheckoutService = $shareOfCheckoutService;
        if (!$pnxFormService) {
            $pnxFormService = new PnxFormService();
        }
        $this->pnxFormService = $pnxFormService;
        if (!$customFieldsFormService) {
            $customFieldsFormService = new CustomFieldsFormService();
        }
        $this->customFieldsFormService = $customFieldsFormService;
    }

    /**
     * Return the HTML of the configuration form
     *
     * @return string
     */
    public function getRenderPaymentFormHtml()
    {
        $feePlans = $this->clientModel->getMerchantFeePlans();
        $this->initPaymentForm();
        $formFields = $this->adminFormBuilderService->getFormFields($this->needsAPIKey());

        // If we have fee plans, we need to add them to the HelperForm
        if ($feePlans) {
            $this->helperFormProxy->setFieldsValue(array_merge($this->helperFormProxy->getFieldsValue(), $this->feePlanModel->getFieldsValueFromFeePlans($feePlans)));
        }

        return $this->helperFormProxy->getHelperForm($formFields);
    }

    /**
     * Initialize the payment form to the helperFormProxy
     *
     * @return void
     */
    public function initPaymentForm()
    {
        $this->helperFormProxy->setModule($this->module);
        $this->helperFormProxy->setTable('alma_config');
        $this->helperFormProxy->setDefaultFormLanguage((int) $this->configurationProxy->get('PS_LANG_DEFAULT'));
        $this->helperFormProxy->setAllowEmployeeFormLang((int) $this->configurationProxy->get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG'));
        $this->helperFormProxy->setSubmitAction('alma_config_form');
        $this->helperFormProxy->setBaseFolder('helpers/form/15/');
        $this->helperFormProxy->setAssetsCss([_MODULE_DIR_ . $this->module->name . '/views/css/admin/tabs.css']);
        $this->helperFormProxy->setCurrentIndex($this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->module->name
            . '&tab_module=' . $this->module->tab
            . '&module_name=' . $this->module->name);
        $this->helperFormProxy->setToken($this->toolsProxy->getAdminTokenLite('AdminModules'));
        $this->helperFormProxy->setFieldsValue($this->getFieldsValueForPaymentForm());
        $this->helperFormProxy->setLanguages($this->context->controller->getLanguages());
    }

    /**
     * Save all configurations
     *
     * @throws \Alma\PrestaShop\Exceptions\AlmaApiKeyException
     * @throws \Alma\PrestaShop\Exceptions\ClientException
     * @throws \Alma\PrestaShop\Exceptions\MissingParameterException
     * @throws \Alma\PrestaShop\Exceptions\PnxFormException
     * @throws \Alma\PrestaShop\Exceptions\ShareOfCheckoutException
     */
    public function saveConfigurations()
    {
        // Consider the plugin as fully configured only when everything goes well
        $this->configurationProxy->updateValue(self::ALMA_FULLY_CONFIGURED, '0');
        $apiMode = $this->toolsProxy->getValue(self::ALMA_API_MODE);
        $apiKeys = $this->almaApiKeyModel->getAllApiKeySend($apiMode);
        $this->almaApiKeyModel->checkActiveApiKeySendIsEmpty();
        $this->almaApiKeyModel->checkApiKeys($apiKeys);
        $this->almaApiKeyModel->saveApiKeys($apiKeys);

        $this->configurationProxy->updateValue(self::ALMA_MERCHANT_ID, $this->clientModel->getMerchantId());

        // At the first installation we don't need to set the static configuration
        // The static configuration is get with default value
        if (!$this->toolsProxy->getValue(self::API_ONLY)) {
            $this->updateStaticConfigurations();
        }
        $this->pnxFormService->save();
        $this->customFieldsFormService->save();

        $this->configurationProxy->updateValue(self::ALMA_API_MODE, $apiMode);
        $this->shareOfCheckoutService->handleConsent();

        // At this point, consider things are sufficiently configured to be usable
        $this->configurationProxy->updateValue(self::ALMA_FULLY_CONFIGURED, '1');

        if (IntegrationsConfigurationsUtils::isUrlRefreshRequired($this->settingsHelper->getKey(CmsDataHelper::ALMA_CMSDATA_DATE))) {
            $this->clientModel->sendUrlForGatherCmsData($this->context->link->getModuleLink($this->module->name, 'cmsdataexport', [], true));
            $this->settingsHelper->updateKey(CmsDataHelper::ALMA_CMSDATA_DATE, time());
        }
    }

    /**
     * Save the STATIC_KEY_CONFIG in the configuration table
     * All the key who haven't dynamic value from the API or language for example
     *
     * @return void
     */
    protected function updateStaticConfigurations()
    {
        foreach (self::STATIC_KEY_CONFIG as $key => $conditions) {
            $type = $conditions;
            $searchKey = $key;

            if (is_array($conditions)) {
                $searchKey = $key . $conditions['suffix'];
                $type = $conditions['action'];
            }

            $value = $this->toolsProxy->getValue($searchKey);

            switch ($type) {
                case 'test_bool':
                    $value = $value ? '1' : '0';
                    break;
                case 'cast_bool':
                    $value = (bool) $value;
                    break;
                default:
                    break;
            }

            if (is_array($value)) {
                $value = json_encode($value);
            }

            $this->configurationProxy->updateValue($key, $value);
        }
    }

    /**
     * Default fields value for the configuration form
     *
     * @return array
     */
    protected function getFieldsValueForPaymentForm()
    {
        return [
            'ALMA_LIVE_API_KEY' => SettingsHelper::getLiveKey(),
            'ALMA_TEST_API_KEY' => SettingsHelper::getTestKey(),
            self::ALMA_API_MODE => SettingsHelper::getActiveMode(),
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

    /**
     * Check if the api_key is set in the settings
     *
     * @return bool
     */
    protected function needsAPIKey()
    {
        $key = trim(SettingsHelper::getActiveAPIKey());

        return '' == $key || null == $key;
    }
}
