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

namespace Alma\PrestaShop\Controllers\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\API\Entities\Merchant;
use Alma\API\RequestError;
use Alma\PrestaShop\Exceptions\MissingParameterException;
use Alma\PrestaShop\Forms\ApiAdminFormBuilder;
use Alma\PrestaShop\Forms\CartEligibilityAdminFormBuilder;
use Alma\PrestaShop\Forms\DebugAdminFormBuilder;
use Alma\PrestaShop\Forms\ExcludedCategoryAdminFormBuilder;
use Alma\PrestaShop\Forms\InpageAdminFormBuilder;
use Alma\PrestaShop\Forms\PaymentButtonAdminFormBuilder;
use Alma\PrestaShop\Forms\PaymentOnTriggeringAdminFormBuilder;
use Alma\PrestaShop\Forms\PnxAdminFormBuilder;
use Alma\PrestaShop\Forms\ProductEligibilityAdminFormBuilder;
use Alma\PrestaShop\Forms\RefundAdminFormBuilder;
use Alma\PrestaShop\Forms\ShareOfCheckoutAdminFormBuilder;
use Alma\PrestaShop\Helpers\ApiHelper;
use Alma\PrestaShop\Helpers\ApiKeyHelper;
use Alma\PrestaShop\Helpers\ClientHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\MediaHelper;
use Alma\PrestaShop\Helpers\OrderHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\SettingsCustomFieldsHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ShareOfCheckoutHelper;
use Alma\PrestaShop\Hooks\AdminHookController;
use Alma\PrestaShop\Logger;

final class GetContentHookController extends AdminHookController
{
    /**
     * @var ApiHelper $apiHelper
     */
    protected $apiHelper;

    /** @var ApiKeyHelper */
    private $apiKeyHelper;

    /** @var Alma */
    protected $module;

    /**
     * @var array
     */
    const KEY_CONFIG = [
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
     * GetContentHook Controller construct.
     */
    public function __construct($module)
    {
        $this->apiHelper = new ApiHelper();
        $this->apiKeyHelper = new ApiKeyHelper();
        parent::__construct($module);
    }

    /**
     * @return mixed|null
     *
     * @throws \Exception
     */
    public function processConfiguration()
    {
        if (!\Tools::isSubmit('alma_config_form')) {
            return null;
        }

        // Consider the plugin as fully configured only when everything goes well
        $this->updateSettingsValue('ALMA_FULLY_CONFIGURED', '0');

        $oldApiMode = SettingsHelper::getActiveMode();
        $apiMode = \Tools::getValue('ALMA_API_MODE');
        $this->updateSettingsValue('ALMA_API_MODE', $apiMode);

        // Get & check provided API keys
        $liveKey = trim(\Tools::getValue(ApiAdminFormBuilder::ALMA_LIVE_API_KEY));
        $testKey = trim(\Tools::getValue(ApiAdminFormBuilder::ALMA_TEST_API_KEY));

        if ((empty($liveKey) && ALMA_MODE_LIVE == $apiMode) || (empty($testKey) && ALMA_MODE_TEST == $apiMode)) {
            $this->context->smarty->assign('validation_error', "missing_key_for_{$apiMode}_mode");

            return $this->module->display($this->module->file, 'getContent.tpl');
        }

        $credentialsError = null;

        if ((ConstantsHelper::OBSCURE_VALUE != $liveKey && ALMA_MODE_LIVE == $apiMode)
            || (ConstantsHelper::OBSCURE_VALUE != $testKey && ALMA_MODE_TEST == $apiMode)
        ) {
            $credentialsError = $this->credentialsError($liveKey, $testKey);
        }

        if ($credentialsError
            && array_key_exists('error', $credentialsError)
        ) {
            return $credentialsError['message'];
        }

        $orderHelper = new OrderHelper();
        $shareOfCheckoutHelper = new ShareOfCheckoutHelper($orderHelper);

        if ($liveKey !== SettingsHelper::getLiveKey()
            && ConstantsHelper::OBSCURE_VALUE !== $liveKey
        ) {
            $shareOfCheckoutHelper->resetShareOfCheckoutConsent();
        } else {
            // Prestashop FormBuilder adds `_ON` after name in the switch
            if (
                true === SettingsHelper::isShareOfCheckoutAnswered()
                && $oldApiMode === $apiMode
            ) {
                $shareOfCheckoutHelper->handleCheckoutConsent(
                    ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_STATE . '_ON'
                );
            }
        }

        // Down here, we know the provided API keys are correct (at least the one for the chosen API mode)
        $this->setKeyIfValueIsNotObscur($liveKey, ALMA_MODE_LIVE);
        $this->setKeyIfValueIsNotObscur($testKey, ALMA_MODE_TEST);

        // Try to get merchant from configured API key/mode
        try {
            $merchant = $this->apiHelper->getMerchant($this->module);
        } catch (\Exception $e) {
            $this->context->smarty->assign(
                [
                    'validation_error' => 'custom_error',
                    'validation_message' => $e->getMessage(),
                ]
            );
            Logger::instance()->error($e->getMessage());

            return $this->module->display($this->module->file, 'getContent.tpl');
        }

        if ($merchant) {
            // Save merchant API ID for widgets usage on frontend
            $this->updateSettingsValue('ALMA_MERCHANT_ID', $merchant->id);
        }

        $apiOnly = \Tools::getValue('_api_only');

        if ($apiOnly && $merchant) {
            $feePlans = $this->getFeePlans();
            foreach ($feePlans as $feePlan) {
                $n = $feePlan->installments_count;
                if (3 == $n && !SettingsHelper::isDeferred($feePlan)) {
                    $key = SettingsHelper::keyForFeePlan($feePlan);
                    $almaPlans = [];
                    $almaPlans[$key]['enabled'] = 1;
                    $almaPlans[$key]['min'] = $feePlan->min_purchase_amount;
                    $almaPlans[$key]['max'] = $feePlan->max_purchase_amount;
                    $almaPlans[$key]['deferred_trigger_limit_days'] = $feePlan->deferred_trigger_limit_days;
                    $almaPlans[$key]['order'] = 1;
                    $this->updateSettingsValue('ALMA_FEE_PLANS', $almaPlans);
                    break;
                }
            }
        }

        if (!$apiOnly) {
            try {
                $this->saveCustomFieldsValues();
            } catch (MissingParameterException $e) {
                $this->context->smarty->assign('validation_error', 'missing_required_setting');
                Logger::instance()->error($e->getMessage());

                return $this->module->display($this->module->file, 'getContent.tpl');
            }

            $this->saveConfigValues();

            if ($merchant) {
                // First validate that plans boundaries are correctly set
                $feePlans = $this->getFeePlans();

                foreach ($feePlans as $feePlan) {
                    $n = $feePlan->installments_count;
                    $deferred_days = $feePlan->deferred_days;
                    $deferred_months = $feePlan->deferred_months;
                    $key = SettingsHelper::keyForFeePlan($feePlan);

                    if (1 != $n && SettingsHelper::isDeferred($feePlan)) {
                        continue;
                    }

                    $min = PriceHelper::convertPriceToCents((int) \Tools::getValue("ALMA_{$key}_MIN_AMOUNT"));
                    $max = PriceHelper::convertPriceToCents((int) \Tools::getValue("ALMA_{$key}_MAX_AMOUNT"));

                    $enablePlan = (bool) \Tools::getValue("ALMA_{$key}_ENABLED_ON");

                    if ($enablePlan
                        && !(
                            $min >= $feePlan->min_purchase_amount
                            && $min <= min($max, $feePlan->max_purchase_amount)
                        )
                    ) {
                        $this->context->smarty->assign([
                            'validation_error' => 'pnx_min_amount',
                            'n' => $n,
                            'deferred_days' => $deferred_days,
                            'deferred_months' => $deferred_months,
                            'min' => PriceHelper::convertPriceFromCents($feePlan->min_purchase_amount),
                            'max' => PriceHelper::convertPriceFromCents(min($max, $feePlan->max_purchase_amount)),
                        ]);

                        return $this->module->display($this->module->file, 'getContent.tpl');
                    }

                    if ($enablePlan
                        && !(
                            $max >= $min
                            && $max <= $feePlan->max_purchase_amount
                        )
                    ) {
                        $this->context->smarty->assign([
                            'validation_error' => 'pnx_max_amount',
                            'n' => $n,
                            'deferred_days' => $deferred_days,
                            'deferred_months' => $deferred_months,
                            'min' => PriceHelper::convertPriceFromCents($min),
                            'max' => PriceHelper::convertPriceFromCents($feePlan->max_purchase_amount),
                        ]);

                        return $this->module->display($this->module->file, 'getContent.tpl');
                    }
                }

                $almaPlans = [];
                $position = 1;

                foreach ($feePlans as $feePlan) {
                    $n = $feePlan->installments_count;
                    $key = SettingsHelper::keyForFeePlan($feePlan);

                    if (1 != $n && SettingsHelper::isDeferred($feePlan)) {
                        continue;
                    }

                    $min = (int) \Tools::getValue("ALMA_{$key}_MIN_AMOUNT");
                    $max = (int) \Tools::getValue("ALMA_{$key}_MAX_AMOUNT");
                    $order = (int) \Tools::getValue("ALMA_{$key}_SORT_ORDER");

                    // In case merchant inverted min & max values, correct it
                    if ($min > $max) {
                        $realMin = $max;
                        $max = $min;
                        $min = $realMin;
                    }

                    // in case of difference between sandbox and production feeplans
                    if (0 == $min
                        && 0 == $max
                        && 0 == $order
                    ) {
                        $almaPlans[$key]['enabled'] = '0';
                        $almaPlans[$key]['min'] = $feePlan->min_purchase_amount;
                        $almaPlans[$key]['max'] = $feePlan->max_purchase_amount;
                        $almaPlans[$key]['deferred_trigger_limit_days'] = $feePlan->deferred_trigger_limit_days;
                        $almaPlans[$key]['order'] = (int) $position;
                        ++$position;
                    } else {
                        $enablePlan = (bool) \Tools::getValue("ALMA_{$key}_ENABLED_ON");
                        $almaPlans[$key]['enabled'] = $enablePlan ? '1' : '0';
                        $almaPlans[$key]['min'] = PriceHelper::convertPriceToCents($min);
                        $almaPlans[$key]['max'] = PriceHelper::convertPriceToCents($max);
                        $almaPlans[$key]['deferred_trigger_limit_days'] = $feePlan->deferred_trigger_limit_days;
                        $almaPlans[$key]['order'] = (int) \Tools::getValue("ALMA_{$key}_SORT_ORDER");
                    }
                }

                $this->updateSettingsValue('ALMA_FEE_PLANS', $almaPlans);
            }
        }

        // At this point, consider things are sufficiently configured to be usable
        $this->updateSettingsValue('ALMA_FULLY_CONFIGURED', '1');

        if ($credentialsError
            && array_key_exists('warning', $credentialsError)
        ) {
            return $credentialsError['message'];
        }

        $this->context->smarty->clearAssign('validation_error');

        return $this->module->display($this->module->file, 'getContent.tpl');
    }

    /**
     * Check if Api key are obscur.
     *
     * @param string $apiKey
     * @param string $mode
     *
     * @return void
     */
    private function setKeyIfValueIsNotObscur($apiKey, $mode)
    {
        if (ConstantsHelper::OBSCURE_VALUE === $apiKey) {
            return;
        }

        if (ALMA_MODE_LIVE === $mode) {
            $this->apiKeyHelper->setLiveApiKey($apiKey);
        } else {
            $this->apiKeyHelper->setTestApiKey($apiKey);
        }
    }

    /**
     * @param $liveKey
     * @param $testKey
     *
     * @return array|null
     */
    private function credentialsError($liveKey, $testKey)
    {
        $modes = [ALMA_MODE_TEST, ALMA_MODE_LIVE];

        foreach ($modes as $mode) {
            $key = (ALMA_MODE_LIVE == $mode ? $liveKey : $testKey);
            if (
                !$key
                || ConstantsHelper::OBSCURE_VALUE === $key
                || SettingsHelper::getActiveMode() !== $mode
            ) {
                continue;
            }

            $alma = ClientHelper::createInstance($key, $mode);
            if (!$alma) {
                $this->context->smarty->assign('validation_error', 'alma_client_null');

                $errorMessage = $this->module->display($this->module->file, 'getContent.tpl');

                return ['error' => true, 'message' => $errorMessage];
            }

            // Try to get merchant from configured API key/mode
            try {
                $this->apiHelper->getMerchant($this->module, $alma);
            } catch (\Exception $e) {
                $this->context->smarty->assign(
                    [
                        'validation_error' => 'custom_error',
                        'validation_message' => $e->getMessage(),
                    ]
                );
                Logger::instance()->error($e->getMessage());

                $errorMessage = $this->module->display($this->module->file, 'getContent.tpl');

                return ['error' => true, 'message' => $errorMessage];
            }
        }

        return null;
    }

    /**
     * @return array|null
     */
    private function getFeePlans()
    {
        $alma = ClientHelper::defaultInstance();

        if (!$alma) {
            return null;
        }

        try {
            return (array) $alma->merchants->feePlans('general', 'all', true);
        } catch (RequestError $e) {
            return null;
        }
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    public function renderForm()
    {
        // Try to get merchant from configured API key/mode
        $merchant = null;

        try {
            $merchant = $this->apiHelper->getMerchant($this->module);
        } catch (\Exception $e) {
            Logger::instance()->error($e->getMessage());
        }

        $extraMessage = null;

        $needsKeys = $this->needsAPIKey();

        if ($needsKeys
            && !\Tools::isSubmit('alma_config_form')
        ) {
            $this->context->smarty->clearAllAssign();

            $this->assignSmartyAlertClasses();
            $this->context->smarty->assign('tip', 'fill_api_keys');

            $extraMessage = $this->module->display($this->module->file, 'getContent.tpl');
        }

        $this->assignSmartyAlertClasses();

        $feePlans = $this->getFeePlans();

        list($feePlansOrdered, $installmentsPlans) = $this->getPlansForForms($feePlans, $merchant);

        $fieldsForms = $this->buildForms($needsKeys, $feePlansOrdered, $installmentsPlans);

        $helper = new \HelperForm();
        $helper->module = $this->module;
        $helper->table = 'alma_config';
        $helper->default_form_language = (int) \Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) \Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->submit_action = 'alma_config_form';

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $helper->base_folder = 'helpers/form/15/';
            $this->context->controller->addCss(_MODULE_DIR_ . $this->module->name . '/views/css/admin/tabs.css');
        }

        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->module->name
            . '&tab_module=' . $this->module->tab
            . '&module_name=' . $this->module->name;

        $helper->token = \Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value = $this->getFieldsValueForForm();

        if ($merchant) {
            $sortOrder = 1;
            foreach ($feePlans as $feePlan) {
                $key = SettingsHelper::keyForFeePlan($feePlan);

                $helper->fields_value["ALMA_{$key}_ENABLED_ON"] = isset($installmentsPlans->$key->enabled)
                    ? $installmentsPlans->$key->enabled
                    : 0;

                $minAmount = isset($installmentsPlans->$key->min)
                    ? $installmentsPlans->$key->min
                    : $feePlan->min_purchase_amount;

                $helper->fields_value["ALMA_{$key}_MIN_AMOUNT"] = (int) round(
                    PriceHelper::convertPriceFromCents($minAmount)
                );
                $maxAmount = isset($installmentsPlans->$key->max)
                    ? $installmentsPlans->$key->max
                    : $feePlan->max_purchase_amount;

                $helper->fields_value["ALMA_{$key}_MAX_AMOUNT"] = (int) PriceHelper::convertPriceFromCents($maxAmount);

                $order = isset($installmentsPlans->$key->order)
                    ? $installmentsPlans->$key->order
                    : $sortOrder;

                $helper->fields_value["ALMA_{$key}_SORT_ORDER"] = $order;

                ++$sortOrder;
            }
        }

        $helper->languages = $this->context->controller->getLanguages();

        return $extraMessage . $helper->generateForm($fieldsForms);
    }

    /**
     * @param bool $needsKeys
     * @param array $feePlansOrdered
     * @param array $installmentsPlans
     *
     * @return array
     */
    protected function buildForms($needsKeys, $feePlansOrdered, $installmentsPlans)
    {
        $iconPath = MediaHelper::getIconPathAlmaTiny($this->module);
        $fieldsForms = [];

        if (!$needsKeys) {
            $pnxBuilder = new PnxAdminFormBuilder(
                $this->module,
                $this->context,
                $iconPath,
                ['feePlans' => $feePlansOrdered, 'installmentsPlans' => $installmentsPlans]
            );
            if ($pnxBuilder) {
                $fieldsForms[] = $pnxBuilder->build();
            }

            $productBuilder = new ProductEligibilityAdminFormBuilder($this->module, $this->context, $iconPath);
            $fieldsForms[] = $productBuilder->build();

            $cartBuilder = new CartEligibilityAdminFormBuilder($this->module, $this->context, $iconPath);
            $fieldsForms[] = $cartBuilder->build();

            $paymentBuilder = new PaymentButtonAdminFormBuilder($this->module, $this->context, $iconPath);
            $fieldsForms[] = $paymentBuilder->build();

            $excludedBuilder = new ExcludedCategoryAdminFormBuilder($this->module, $this->context, $iconPath);
            $fieldsForms[] = $excludedBuilder->build();

            $refundBuilder = new RefundAdminFormBuilder($this->module, $this->context, $iconPath);
            $fieldsForms[] = $refundBuilder->build();

            if (!SettingsHelper::shouldHideShareOfCheckoutForm()) {
                $shareOfCheckoutBuilder = new ShareOfCheckoutAdminFormBuilder($this->module, $this->context, $iconPath);
                $fieldsForms[] = $shareOfCheckoutBuilder->build();
            }

            if (SettingsHelper::isInpageAllowed()) {
                $inpageBuilder = new InpageAdminFormBuilder($this->module, $this->context, $iconPath);
                $fieldsForms[] = $inpageBuilder->build();
            }
        }

        if (SettingsHelper::isPaymentTriggerEnabledByState()) {
            $triggerBuilder = new PaymentOnTriggeringAdminFormBuilder($this->module, $this->context, $iconPath);
            $fieldsForms[] = $triggerBuilder->build();
        }

        $apiBuilder = new ApiAdminFormBuilder($this->module, $this->context, $iconPath, ['needsAPIKey' => $needsKeys]);
        $fieldsForms[] = $apiBuilder->build();

        $debugBuilder = new DebugAdminFormBuilder($this->module, $this->context, $iconPath);
        $fieldsForms[] = $debugBuilder->build();

        return $fieldsForms;
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    protected function getFieldsValueForForm()
    {
        return [
            'ALMA_LIVE_API_KEY' => SettingsHelper::getLiveKey(),
            'ALMA_TEST_API_KEY' => SettingsHelper::getTestKey(),
            'ALMA_API_MODE' => SettingsHelper::getActiveMode(),
            PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_TITLE => SettingsCustomFieldsHelper::getPayNowButtonTitle(),
            PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_DESC => SettingsCustomFieldsHelper::getPayNowButtonDescription(),
            PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE => SettingsCustomFieldsHelper::getPnxButtonTitle(),
            PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC => SettingsCustomFieldsHelper::getPnxButtonDescription(),
            PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE => SettingsCustomFieldsHelper::getPaymentButtonTitleDeferred(),
            PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC => SettingsCustomFieldsHelper::getPaymentButtonDescriptionDeferred(),
            PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE => SettingsCustomFieldsHelper::getPnxAirButtonTitle(),
            PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_DESC => SettingsCustomFieldsHelper::getPnxAirButtonDescription(),
            InpageAdminFormBuilder::ALMA_ACTIVATE_INPAGE . '_ON' => SettingsHelper::isInPageEnabled(),
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
            'ALMA_PAYMENT_ON_TRIGGERING_ENABLED_ON' => SettingsHelper::isPaymentTriggerEnabledByState(),
            'ALMA_DESCRIPTION_TRIGGER' => SettingsHelper::getKeyDescriptionPaymentTrigger(),
            'ALMA_NOT_ELIGIBLE_CATEGORIES' => SettingsCustomFieldsHelper::getNonEligibleCategoriesMessage(),
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
     * @param array|null $feePlans
     * @param Merchant|null $merchant
     *
     * @return array
     */
    protected function getPlansForForms($feePlans, $merchant = null)
    {
        $feePlansOrdered = [];
        $installmentsPlans = [];

        if ($merchant) {
            $installmentsPlans = json_decode(SettingsHelper::getFeePlans());

            // sort fee plans by pnx then by pay later duration
            $feePlanDeferred = [];

            foreach ($feePlans as $feePlan) {
                if (!SettingsHelper::isDeferred($feePlan)) {
                    $feePlansOrdered[$feePlan->installments_count] = $feePlan;
                    continue;
                }

                $duration = SettingsHelper::getDuration($feePlan);
                $feePlanDeferred[$feePlan->installments_count . $duration] = $feePlan;
            }

            ksort($feePlanDeferred);
            $feePlansOrdered = array_merge($feePlansOrdered, $feePlanDeferred);
        }

        return [$feePlansOrdered, $installmentsPlans];
    }

    private function assignSmartyAlertClasses($level = 'danger')
    {
        $token = \Tools::getAdminTokenLite('AdminModules');
        $href = $this->context->link->getAdminLink('AdminParentModulesSf', $token);

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->context->smarty->assign([
                'validation_error_classes' => 'alert',
                'tip_classes' => 'conf',
                'success_classes' => 'conf',
            ]);
        } else {
            $this->context->smarty->assign([
                'validation_error_classes' => "alert alert-$level",
                'tip_classes' => 'alert alert-info',
                'success_classes' => 'alert alert-success',
                'breadcrumbs2' => [
                    'container' => [
                        'name' => $this->module->l('Modules'),
                        'href' => $href,
                    ],
                    'tab' => [
                        'name' => $this->module->l('Module Manager '),
                        'href' => $href,
                    ],
                ],
                'quick_access_current_link_name' => $this->module->l('Module Manager - List'),
                'quick_access_current_link_icon' => 'icon-AdminParentModulesSf',
                'token' => $token,
                'host_mode' => 0,
            ]);
        }
    }

    /**
     * @return bool
     *
     * @throws \Exception
     */
    public function needsAPIKey()
    {
        $key = trim(SettingsHelper::getActiveAPIKey());

        return '' == $key || null == $key;
    }

    /**
     * @param $params
     *
     * @return string
     *
     * @throws \Exception
     */
    public function run($params)
    {
        $this->assignSmartyAlertClasses();

        if (\Tools::isSubmit('alma_config_form')) {
            $messages = $this->processConfiguration();
        } elseif (!$this->needsAPIKey()) {
            $messages = $this->credentialsError(
                SettingsHelper::getLiveKey(),
                SettingsHelper::getTestKey()
            );

            if ($messages) {
                $messages = $messages['message'];
            }
        } else {
            $messages = '';
        }

        $htmlForm = $this->renderForm();

        return $messages . $htmlForm;
    }

    /**
     * @param int $languageId
     * @param string $locale
     * @param string $keyForm
     *
     * @return array
     *
     * @throws MissingParameterException
     */
    protected function getLocaleAndString($languageId, $locale, $keyForm)
    {
        $result = [
            'locale' => $locale,
            'string' => \Tools::getValue(sprintf('%s_%s', $keyForm, $languageId)),
        ];

        if (empty($result['string'])) {
            throw new MissingParameterException($locale, $keyForm, $languageId);
        }

        return $result;
    }

    /**
     * @param array $languages
     *
     * @return void
     *
     * @throws MissingParameterException
     */
    protected function saveCustomFieldsValues()
    {
        // Get languages are active
        $languages = $this->context->controller->getLanguages();

        $titlesPayNow = $titles = $titlesDeferred = $titlesCredit = $descriptionsPayNow = $descriptions = $descriptionsDeferred = $descriptionsCredit = $nonEligibleCategoriesMsg = [];

        foreach ($languages as $language) {
            $locale = $language['iso_code'];
            $languageId = $language['id_lang'];

            if (array_key_exists('locale', $language)) {
                $locale = $language['locale'];
            }

            $titlesPayNow[$languageId] = $this->getLocaleAndString(
                $languageId,
                $locale,
                PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_TITLE
            );
            $titles[$languageId] = $this->getLocaleAndString($languageId, $locale, PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE);
            $titlesDeferred[$languageId] = $this->getLocaleAndString($languageId, $locale, PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE);
            $titlesCredit[$languageId] = $this->getLocaleAndString($languageId, $locale, PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE);
            $descriptionsPayNow[$languageId] = $this->getLocaleAndString($languageId, $locale, PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_DESC);
            $descriptions[$languageId] = $this->getLocaleAndString($languageId, $locale, PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC);
            $descriptionsDeferred[$languageId] = $this->getLocaleAndString($languageId, $locale, PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC);
            $descriptionsCredit[$languageId] = $this->getLocaleAndString($languageId, $locale, PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_DESC);
            $nonEligibleCategoriesMsg[$languageId] = $this->getLocaleAndString($languageId, $locale, ExcludedCategoryAdminFormBuilder::ALMA_NOT_ELIGIBLE_CATEGORIES);
        }

        $this->updateSettingsValue(PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE, $titles);
        $this->updateSettingsValue(PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE, $titlesDeferred);
        $this->updateSettingsValue(PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE, $titlesCredit);
        $this->updateSettingsValue(PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_TITLE, $titlesPayNow);
        $this->updateSettingsValue(PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC, $descriptions);
        $this->updateSettingsValue(PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC, $descriptionsDeferred);
        $this->updateSettingsValue(PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_DESC, $descriptionsCredit);
        $this->updateSettingsValue(PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_DESC, $descriptionsPayNow);
        $this->updateSettingsValue('ALMA_NOT_ELIGIBLE_CATEGORIES', $nonEligibleCategoriesMsg);
    }

    /**
     * @param string $configKey
     * @param array|string $value
     *
     * @return void
     */
    protected function updateSettingsValue($configKey, $value)
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        SettingsHelper::updateValue($configKey, $value);
    }

    /**
     * @return void
     */
    protected function saveConfigValues()
    {
        foreach (self::KEY_CONFIG as $key => $conditions) {
            $type = $conditions;
            $searchKey = $key;

            if (is_array($conditions)) {
                $searchKey = $key . $conditions['suffix'];
                $type = $conditions['action'];
            }

            $value = \Tools::getValue($searchKey);

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

            $this->updateSettingsValue($key, $value);
        }
    }
}
