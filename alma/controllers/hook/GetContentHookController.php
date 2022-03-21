<?php
/**
 * 2018-2022 Alma SAS
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
 * @copyright 2018-2022 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Controllers\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\API\RequestError;
use Alma\PrestaShop\API\ClientHelper;
use Alma\PrestaShop\Forms\ApiAdminFormBuilder;
use Alma\PrestaShop\Forms\CartEligibilityAdminFormBuilder;
use Alma\PrestaShop\Forms\DebugAdminFormBuilder;
use Alma\PrestaShop\Forms\ExcludedCategoryAdminFormBuilder;
use Alma\PrestaShop\Forms\PaymentButtonAdminFormBuilder;
use Alma\PrestaShop\Forms\PaymentOnTriggeringAdminFormBuilder;
use Alma\PrestaShop\Forms\PnxAdminFormBuilder;
use Alma\PrestaShop\Forms\ProductEligibilityAdminFormBuilder;
use Alma\PrestaShop\Forms\RefundAdminFormBuilder;
use Alma\PrestaShop\Forms\ShareOfCheckoutAdminFormBuilder;
use Alma\PrestaShop\Hooks\AdminHookController;
use Alma\PrestaShop\Utils\Logger;
use Alma\PrestaShop\Utils\Settings;
use Alma\PrestaShop\Utils\SettingsCustomFields;
use Configuration;
use HelperForm;
use Media;
use Tools;

final class GetContentHookController extends AdminHookController
{
    public function processConfiguration()
    {
        if (!Tools::isSubmit('alma_config_form')) {
            return null;
        }

        // Consider the plugin as fully configured only when everything goes well
        Settings::updateValue('ALMA_FULLY_CONFIGURED', '0');

        $apiMode = Tools::getValue('ALMA_API_MODE');
        Settings::updateValue('ALMA_API_MODE', $apiMode);

        // Get & check provided API keys
        $liveKey = trim(Tools::getValue('ALMA_LIVE_API_KEY'));
        $testKey = trim(Tools::getValue('ALMA_TEST_API_KEY'));

        if ((empty($liveKey) && $apiMode == ALMA_MODE_LIVE) || (empty($testKey) && $apiMode == ALMA_MODE_TEST)) {
            $this->context->smarty->assign('validation_error', "missing_key_for_{$apiMode}_mode");

            return $this->module->display($this->module->file, 'getContent.tpl');
        }

        $credentialsError = $this->credentialsError($apiMode, $liveKey, $testKey);

        if ($credentialsError && array_key_exists('error', $credentialsError)) {
            return $credentialsError['message'];
        }

        // Down here, we know the provided API keys are correct (at least the one for the chosen API mode)
        Settings::updateValue('ALMA_LIVE_API_KEY', $liveKey);
        Settings::updateValue('ALMA_TEST_API_KEY', $testKey);

        // Try to get merchant from configured API key/mode
        $merchant = $this->getMerchant();

        if ($merchant) {
            // Save merchant API ID for widgets usage on frontend
            Settings::updateValue('ALMA_MERCHANT_ID', $merchant->id);
        }

        $apiOnly = Tools::getValue('_api_only');

        if ($apiOnly && $merchant) {
            $feePlans = $this->getFeePlans();
            foreach ($feePlans as $feePlan) {
                $n = $feePlan->installments_count;
                if (3 == $n && !Settings::isDeferred($feePlan)) {
                    $key = Settings::keyForFeePlan($feePlan);
                    $almaPlans = [];
                    $almaPlans[$key]['enabled'] = 1;
                    $almaPlans[$key]['min'] = $feePlan->min_purchase_amount;
                    $almaPlans[$key]['max'] = $feePlan->max_purchase_amount;
                    $almaPlans[$key]['deferred_trigger_limit_days'] = $feePlan->deferred_trigger_limit_days;
                    $almaPlans[$key]['order'] = 1;
                    Settings::updateValue('ALMA_FEE_PLANS', json_encode($almaPlans));
                    break;
                }
            }
        }

        // Get languages are active
        $languages = $this->context->controller->getLanguages();

        if (!$apiOnly) {
            $titles = [];
            $titlesDeferred = [];
            $descriptions = [];
            $descriptionsDeferred = [];
            $nonEligibleCategoriesMsg = [];
            foreach ($languages as $language) {
                $locale = $language['iso_code'];
                if (array_key_exists('locale', $language)) {
                    $locale = $language['locale'];
                }
                $titles[$language['id_lang']] = [
                    'locale' => $locale,
                    'string' => Tools::getValue(PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE . '_' . $language['id_lang']),
                ];
                $titlesDeferred[$language['id_lang']] = [
                    'locale' => $locale,
                    'string' => Tools::getValue(PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE . '_' . $language['id_lang']),
                ];
                $titlesCredit[$language['id_lang']] = [
                    'locale' => $locale,
                    'string' => Tools::getValue(PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE . '_' . $language['id_lang']),
                ];
                $descriptions[$language['id_lang']] = [
                    'locale' => $locale,
                    'string' => Tools::getValue(PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC . '_' . $language['id_lang']),
                ];
                $descriptionsDeferred[$language['id_lang']] = [
                    'locale' => $locale,
                    'string' => Tools::getValue(PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC . '_' . $language['id_lang']),
                ];
                $descriptionsCredit[$language['id_lang']] = [
                    'locale' => $locale,
                    'string' => Tools::getValue(PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_DESC . '_' . $language['id_lang']),
                ];
                $nonEligibleCategoriesMsg[$language['id_lang']] = [
                    'locale' => $locale,
                    'string' => Tools::getValue(ExcludedCategoryAdminFormBuilder::ALMA_NOT_ELIGIBLE_CATEGORIES . '_' . $language['id_lang']),
                ];

                if (empty($titles[$language['id_lang']]['string'])
                    || empty($descriptions[$language['id_lang']]['string'])
                    || empty($titlesDeferred[$language['id_lang']]['string'])
                    || empty($descriptionsDeferred[$language['id_lang']]['string'])
                ) {
                    $this->context->smarty->assign('validation_error', 'missing_required_setting');

                    return $this->module->display($this->module->file, 'getContent.tpl');
                }
            }

            $showEligibility = (bool) Tools::getValue('ALMA_SHOW_ELIGIBILITY_MESSAGE_ON');
            $showCartEligibilityNotEligible = (bool) Tools::getValue('ALMA_CART_WDGT_NOT_ELGBL_ON');
            $showProductEligibilityNotEligible = (bool) Tools::getValue('ALMA_PRODUCT_WDGT_NOT_ELGBL_ON');
            $showCategoriesEligibilityNotEligible = (bool) Tools::getValue('ALMA_CATEGORIES_WDGT_NOT_ELGBL_ON');

            $showProductEligibility = (bool) Tools::getValue('ALMA_SHOW_PRODUCT_ELIGIBILITY_ON');
            Settings::updateValue('ALMA_SHOW_PRODUCT_ELIGIBILITY', $showProductEligibility ? '1' : '0');

            $productPriceQuerySelector = Tools::getValue('ALMA_PRODUCT_PRICE_SELECTOR');
            Settings::updateValue('ALMA_PRODUCT_PRICE_SELECTOR', $productPriceQuerySelector);

            $widgetCustomPosition = (bool) Tools::getValue('ALMA_WIDGET_POSITION_CUSTOM');
            Settings::updateValue('ALMA_WIDGET_POSITION_CUSTOM', $widgetCustomPosition);

            $productWidgetPositionQuerySelector = Tools::getValue('ALMA_WIDGET_POSITION_SELECTOR');
            Settings::updateValue('ALMA_WIDGET_POSITION_SELECTOR', $productWidgetPositionQuerySelector);

            $productAttrQuerySelector = Tools::getValue('ALMA_PRODUCT_ATTR_SELECTOR');
            Settings::updateValue('ALMA_PRODUCT_ATTR_SELECTOR', $productAttrQuerySelector);

            $productAttrRadioQuerySelector = Tools::getValue('ALMA_PRODUCT_ATTR_RADIO_SELECTOR');
            Settings::updateValue('ALMA_PRODUCT_ATTR_RADIO_SELECTOR', $productAttrRadioQuerySelector);

            $productColorPickQuerySelector = Tools::getValue('ALMA_PRODUCT_COLOR_PICK_SELECTOR');
            Settings::updateValue('ALMA_PRODUCT_COLOR_PICK_SELECTOR', $productColorPickQuerySelector);

            $productQuantityQuerySelector = Tools::getValue('ALMA_PRODUCT_QUANTITY_SELECTOR');
            Settings::updateValue('ALMA_PRODUCT_QUANTITY_SELECTOR', $productQuantityQuerySelector);

            $cartWidgetCustomPosition = (bool) Tools::getValue('ALMA_CART_WIDGET_POSITION_CUSTOM');
            Settings::updateValue('ALMA_CART_WIDGET_POSITION_CUSTOM', $cartWidgetCustomPosition);

            $cartWidgetPositionQuerySelector = Tools::getValue('ALMA_CART_WDGT_POS_SELECTOR');
            Settings::updateValue('ALMA_CART_WDGT_POS_SELECTOR', $cartWidgetPositionQuerySelector);

            Settings::updateValue(PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE, json_encode($titles));
            Settings::updateValue(PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC, json_encode($descriptions));

            Settings::updateValue(PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE, json_encode($titlesDeferred));
            Settings::updateValue(PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC, json_encode($descriptionsDeferred));

            Settings::updateValue(PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE, json_encode($titlesCredit));
            Settings::updateValue(PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_DESC, json_encode($descriptionsCredit));

            $showDisabledButton = (bool) Tools::getValue('ALMA_SHOW_DISABLED_BUTTON');
            Settings::updateValue('ALMA_SHOW_DISABLED_BUTTON', $showDisabledButton);

            Settings::updateValue('ALMA_SHOW_ELIGIBILITY_MESSAGE', $showEligibility ? '1' : '0');
            Settings::updateValue('ALMA_NOT_ELIGIBLE_CATEGORIES', json_encode($nonEligibleCategoriesMsg));

            Settings::updateValue('ALMA_CART_WDGT_NOT_ELGBL', $showCartEligibilityNotEligible);
            Settings::updateValue('ALMA_PRODUCT_WDGT_NOT_ELGBL', $showProductEligibilityNotEligible);
            Settings::updateValue('ALMA_CATEGORIES_WDGT_NOT_ELGBL', $showCategoriesEligibilityNotEligible);

            $idStateRefund = Tools::getValue('ALMA_STATE_REFUND');
            Settings::updateValue('ALMA_STATE_REFUND', $idStateRefund);

            $isStateRefundEnabled = (bool) Tools::getValue('ALMA_STATE_REFUND_ENABLED_ON');
            Settings::updateValue('ALMA_STATE_REFUND_ENABLED', $isStateRefundEnabled);

            $idStatePaymentTrigger = Tools::getValue('ALMA_STATE_TRIGGER');
            Settings::updateValue('ALMA_STATE_TRIGGER', $idStatePaymentTrigger);

            $isStatePaymentTriggerEnabled = (bool) Tools::getValue('ALMA_PAYMENT_ON_TRIGGERING_ENABLED_ON');
            Settings::updateValue('ALMA_PAYMENT_ON_TRIGGERING_ENABLED', $isStatePaymentTriggerEnabled);

            $descriptionPaymentTrigger = Tools::getValue('ALMA_DESCRIPTION_TRIGGER');
            Settings::updateValue('ALMA_DESCRIPTION_TRIGGER', $descriptionPaymentTrigger);

            $activateLogging = (bool) Tools::getValue('ALMA_ACTIVATE_LOGGING_ON');
            Settings::updateValue('ALMA_ACTIVATE_LOGGING', $activateLogging);

            $activateShareOfCheckout = (bool) Tools::getValue('ALMA_ACTIVATE_SHARE_OF_CHECKOUT_ON');
            // phpcs:ignore
            Settings::updateValue(ShareOfCheckoutAdminFormBuilder::ALMA_ACTIVATE_SHARE_OF_CHECKOUT, $activateShareOfCheckout);

            if ($merchant) {
                // First validate that plans boundaries are correctly set
                $feePlans = $this->getFeePlans();
                foreach ($feePlans as $feePlan) {
                    $n = $feePlan->installments_count;
                    $deferred_days = $feePlan->deferred_days;
                    $deferred_months = $feePlan->deferred_months;
                    $key = Settings::keyForFeePlan($feePlan);
                    if (1 == $n && !Settings::isDeferred($feePlan)) {
                        continue;
                    }
                    if (1 != $n && Settings::isDeferred($feePlan)) {
                        continue;
                    }
                    $min = almaPriceToCents((int) Tools::getValue("ALMA_${key}_MIN_AMOUNT"));
                    $max = almaPriceToCents((int) Tools::getValue("ALMA_${key}_MAX_AMOUNT"));
                    $enablePlan = (bool) Tools::getValue("ALMA_${key}_ENABLED_ON");

                    if ($enablePlan && !($min >= $feePlan->min_purchase_amount &&
                        $min <= min($max, $feePlan->max_purchase_amount))) {
                        $this->context->smarty->assign([
                            'validation_error' => 'pnx_min_amount',
                            'n' => $n,
                            'deferred_days' => $deferred_days,
                            'deferred_months' => $deferred_months,
                            'min' => almaPriceFromCents($feePlan->min_purchase_amount),
                            'max' => almaPriceFromCents(min($max, $feePlan->max_purchase_amount)),
                        ]);

                        return $this->module->display($this->module->file, 'getContent.tpl');
                    }

                    if ($enablePlan && !($max >= $min && $max <= $feePlan->max_purchase_amount)) {
                        $this->context->smarty->assign([
                            'validation_error' => 'pnx_max_amount',
                            'n' => $n,
                            'deferred_days' => $deferred_days,
                            'deferred_months' => $deferred_months,
                            'min' => almaPriceFromCents($min),
                            'max' => almaPriceFromCents($feePlan->max_purchase_amount),
                        ]);

                        return $this->module->display($this->module->file, 'getContent.tpl');
                    }
                }

                $almaPlans = [];
                $position = 1;
                foreach ($feePlans as $feePlan) {
                    $n = $feePlan->installments_count;
                    $key = Settings::keyForFeePlan($feePlan);

                    if (1 == $n && !Settings::isDeferred($feePlan)) {
                        continue;
                    }

                    if (1 != $n && Settings::isDeferred($feePlan)) {
                        continue;
                    }

                    $min = (int) Tools::getValue("ALMA_${key}_MIN_AMOUNT");
                    $max = (int) Tools::getValue("ALMA_${key}_MAX_AMOUNT");
                    $order = (int) Tools::getValue("ALMA_${key}_SORT_ORDER");

                    // In case merchant inverted min & max values, correct it
                    if ($min > $max) {
                        $realMin = $max;
                        $max = $min;
                        $min = $realMin;
                    }

                    // in case of difference between sandbox and production feeplans
                    if (0 == $min && 0 == $max && 0 == $order) {
                        $enablePlan = (bool) Tools::getValue("ALMA_${key}_ENABLED_ON");
                        $almaPlans[$key]['enabled'] = '0';
                        $almaPlans[$key]['min'] = $feePlan->min_purchase_amount;
                        $almaPlans[$key]['max'] = $feePlan->max_purchase_amount;
                        $almaPlans[$key]['deferred_trigger_limit_days'] = $feePlan->deferred_trigger_limit_days;
                        $almaPlans[$key]['order'] = (int) $position;
                        ++$position;
                    } else {
                        $enablePlan = (bool) Tools::getValue("ALMA_${key}_ENABLED_ON");
                        $almaPlans[$key]['enabled'] = $enablePlan ? '1' : '0';
                        $almaPlans[$key]['min'] = almaPriceToCents($min);
                        $almaPlans[$key]['max'] = almaPriceToCents($max);
                        $almaPlans[$key]['deferred_trigger_limit_days'] = $feePlan->deferred_trigger_limit_days;
                        $almaPlans[$key]['order'] = (int) Tools::getValue("ALMA_${key}_SORT_ORDER");
                    }
                }

                Settings::updateValue('ALMA_FEE_PLANS', json_encode($almaPlans));
            }
        }

        // At this point, consider things are sufficiently configured to be usable
        Settings::updateValue('ALMA_FULLY_CONFIGURED', '1');

        if ($credentialsError && array_key_exists('warning', $credentialsError)) {
            return $credentialsError['message'];
        }

        $this->context->smarty->clearAssign('validation_error');

        return $this->module->display($this->module->file, 'getContent.tpl');
    }

    private function credentialsError($apiMode, $liveKey, $testKey)
    {
        $modes = [ALMA_MODE_TEST, ALMA_MODE_LIVE];

        foreach ($modes as $mode) {
            $key = ($mode == ALMA_MODE_LIVE ? $liveKey : $testKey);
            if (!$key) {
                continue;
            }

            $alma = ClientHelper::createInstance($key, $mode);
            if (!$alma) {
                $this->context->smarty->assign('validation_error', 'alma_client_null');

                $errorMessage = $this->module->display($this->module->file, 'getContent.tpl');

                return ['error' => true, 'message' => $errorMessage];
            }

            try {
                $merchant = $alma->merchants->me();
            } catch (RequestError $e) {
                if ($e->response && $e->response->responseCode === 401) {
                    $this->context->smarty->assign('validation_error', "{$mode}_authentication_error");

                    $errorMessage = $this->module->display($this->module->file, 'getContent.tpl');

                    return ['error' => true, 'message' => $errorMessage];
                } else {
                    Logger::instance()->error('Error while fetching merchant status: ' . $e->getMessage());

                    $this->context->smarty->assign('validation_error', 'api_request_error');
                    $this->context->smarty->assign('error', $e->getMessage());

                    $errorMessage = $this->module->display($this->module->file, 'getContent.tpl');

                    return ['error' => true, 'message' => $errorMessage];
                }
            }

            if (!$merchant->can_create_payments) {
                $this->context->smarty->assign('validation_error', "inactive_{$mode}_account");
                $this->assignSmartyAlertClasses($apiMode == $mode ? 'danger' : 'warning');

                $errorMessage = $this->module->display($this->module->file, 'getContent.tpl');

                return ['warning' => true, 'message' => $errorMessage];
            }
        }

        return null;
    }

    private function getMerchant()
    {
        $alma = ClientHelper::defaultInstance();

        if (!$alma) {
            return null;
        }

        try {
            return $alma->merchants->me();
        } catch (RequestError $e) {
            return null;
        }
    }

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

    public function renderForm()
    {
        $needsKeys = $this->needsAPIKey();
        $merchant = $this->getMerchant();

        if (is_callable('Media::getMediaPath')) {
            $iconPath = Media::getMediaPath(_PS_MODULE_DIR_ . $this->module->name . '/views/img/logos/alma_tiny.svg');
        } else {
            $iconPath = $this->module->getPathUri() . '/views/img/logos/alma_tiny.svg';
        }

        $extraMessage = null;
        if ($needsKeys && !Tools::isSubmit('alma_config_form')) {
            $this->context->smarty->clearAllAssign();

            $this->assignSmartyAlertClasses();
            $this->context->smarty->assign('tip', 'fill_api_keys');

            $extraMessage = $this->module->display($this->module->file, 'getContent.tpl');
        }

        $feePlansOrdered = [];
        $installmentsPlans = [];
        if ($merchant) {
            $feePlans = $this->getFeePlans();
            $installmentsPlans = json_decode(Settings::getFeePlans());

            // sort fee plans by pnx then by pay later duration
            $feePlanDeferred = [];
            foreach ($feePlans as $feePlan) {
                if (!Settings::isDeferred($feePlan)) {
                    $feePlansOrdered[$feePlan->installments_count] = $feePlan;
                } else {
                    $duration = Settings::getDuration($feePlan);
                    $feePlanDeferred[$feePlan->installments_count . $duration] = $feePlan;
                }
            }
            ksort($feePlanDeferred);
            $feePlansOrdered = array_merge($feePlansOrdered, $feePlanDeferred);
        }

        $pnxBuilder = new PnxAdminFormBuilder(
            $this->module,
            $this->context,
            $iconPath,
            ['feePlans' => $feePlansOrdered, 'installmentsPlans' => $installmentsPlans]
        );
        $apiBuilder = new ApiAdminFormBuilder($this->module, $this->context, $iconPath, ['needsAPIKey' => $needsKeys]);
        $cartBuilder = new CartEligibilityAdminFormBuilder($this->module, $this->context, $iconPath);
        $productBuilder = new ProductEligibilityAdminFormBuilder($this->module, $this->context, $iconPath);
        $excludedBuilder = new ExcludedCategoryAdminFormBuilder($this->module, $this->context, $iconPath);
        $refundBuilder = new RefundAdminFormBuilder($this->module, $this->context, $iconPath);
        $triggerBuilder = new PaymentOnTriggeringAdminFormBuilder($this->module, $this->context, $iconPath, ['feePlans' => $feePlansOrdered]);
        $paymentBuilder = new PaymentButtonAdminFormBuilder($this->module, $this->context, $iconPath);
        $debugBuilder = new DebugAdminFormBuilder($this->module, $this->context, $iconPath);
        $shareOfCheckoutBuilder = new ShareOfCheckoutAdminFormBuilder($this->module, $this->context, $iconPath);

        $fieldsForms = [];

        if (!$needsKeys) {
            if ($pnxBuilder) {
                $fieldsForms[] = $pnxBuilder->build();
            }
            $fieldsForms[] = $productBuilder->build();
            $fieldsForms[] = $cartBuilder->build();
            $fieldsForms[] = $paymentBuilder->build();
            $fieldsForms[] = $excludedBuilder->build();
            $fieldsForms[] = $refundBuilder->build();
            $fieldsForms[] = $triggerBuilder->build();
        }
        $fieldsForms[] = $apiBuilder->build();
        $fieldsForms[] = $debugBuilder->build();
        $fieldsForms[] = $shareOfCheckoutBuilder->build();

        $helper = new HelperForm();
        $helper->module = $this->module;
        $helper->table = 'alma_config';
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->submit_action = 'alma_config_form';

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $helper->base_folder = 'helpers/form/15/';
            $this->context->controller->addCss(_MODULE_DIR_ . $this->module->name . '/views/css/admin/tabs.css');
        }

        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->module->name .
            '&tab_module=' . $this->module->tab .
            '&module_name=' . $this->module->name;

        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value = [
            'ALMA_LIVE_API_KEY' => Settings::getLiveKey(),
            'ALMA_TEST_API_KEY' => Settings::getTestKey(),
            'ALMA_API_MODE' => Settings::getActiveMode(),
            PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE => SettingsCustomFields::getPnxButtonTitle(),
            PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC => SettingsCustomFields::getPnxButtonDescription(),
            PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE => SettingsCustomFields::getPaymentButtonTitleDeferred(),
            PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC => SettingsCustomFields::getPaymentButtonDescriptionDeferred(),
            PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE => SettingsCustomFields::getPnxAirButtonTitle(),
            PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_DESC => SettingsCustomFields::getPnxAirButtonDescription(),
            'ALMA_SHOW_DISABLED_BUTTON' => Settings::showDisabledButton(),
            'ALMA_SHOW_ELIGIBILITY_MESSAGE_ON' => Settings::showEligibilityMessage(),
            'ALMA_CART_WDGT_NOT_ELGBL_ON' => Settings::showCartWidgetIfNotEligible(),
            'ALMA_PRODUCT_WDGT_NOT_ELGBL_ON' => Settings::showProductWidgetIfNotEligible(),
            'ALMA_CATEGORIES_WDGT_NOT_ELGBL_ON' => Settings::showCategoriesWidgetIfNotEligible(),
            'ALMA_ACTIVATE_LOGGING_ON' => (bool) Settings::canLog(),
            'ALMA_ACTIVATE_SHARE_OF_CHECKOUT_ON' => (bool) Settings::canShareOfCheckout(),
            'ALMA_STATE_REFUND' => Settings::getRefundState(),
            'ALMA_STATE_REFUND_ENABLED_ON' => Settings::isRefundEnabledByState(),
            'ALMA_STATE_TRIGGER' => Settings::getPaymentTriggerState(),
            'ALMA_PAYMENT_ON_TRIGGERING_ENABLED_ON' => Settings::isPaymentTriggerEnabledByState(),
            'ALMA_DESCRIPTION_TRIGGER' => Settings::getKeyDescriptionPaymentTrigger(),
            'ALMA_NOT_ELIGIBLE_CATEGORIES' => SettingsCustomFields::getNonEligibleCategoriesMessage(),
            'ALMA_SHOW_PRODUCT_ELIGIBILITY_ON' => Settings::showProductEligibility(),
            'ALMA_PRODUCT_PRICE_SELECTOR' => Settings::getProductPriceQuerySelector(),
            'ALMA_WIDGET_POSITION_SELECTOR' => Settings::getProductWidgetPositionQuerySelector(),
            'ALMA_WIDGET_POSITION_CUSTOM' => Settings::isWidgetCustomPosition(),
            'ALMA_CART_WDGT_POS_SELECTOR' => Settings::getCartWidgetPositionQuerySelector(),
            'ALMA_CART_WIDGET_POSITION_CUSTOM' => Settings::isCartWidgetCustomPosition(),
            'ALMA_PRODUCT_ATTR_SELECTOR' => Settings::getProductAttrQuerySelector(),
            'ALMA_PRODUCT_ATTR_RADIO_SELECTOR' => Settings::getProductAttrRadioQuerySelector(),
            'ALMA_PRODUCT_COLOR_PICK_SELECTOR' => Settings::getProductColorPickQuerySelector(),
            'ALMA_PRODUCT_QUANTITY_SELECTOR' => Settings::getProductQuantityQuerySelector(),
            '_api_only' => true,
        ];

        if ($merchant) {
            $i = 2;
            foreach ($feePlans as $feePlan) {
                $key = Settings::keyForFeePlan($feePlan);
                if ((1 == $feePlan->installments_count && !Settings::isDeferred($feePlan))
                    || !$feePlan->allowed) {
                    continue;
                }

                $helper->fields_value["ALMA_${key}_ENABLED_ON"] = isset($installmentsPlans->$key->enabled)
                    ? $installmentsPlans->$key->enabled
                    : 0;
                $minAmount = isset($installmentsPlans->$key->min)
                    ? $installmentsPlans->$key->min
                    : $feePlan->min_purchase_amount;
                $helper->fields_value["ALMA_${key}_MIN_AMOUNT"] = (int) almaPriceFromCents($minAmount);
                $maxAmount = isset($installmentsPlans->$key->max)
                    ? $installmentsPlans->$key->max
                    : $feePlan->max_purchase_amount;
                $helper->fields_value["ALMA_${key}_MAX_AMOUNT"] = (int) almaPriceFromCents($maxAmount);
                $order = isset($installmentsPlans->$key->order)
                    ? $installmentsPlans->$key->order
                    : $i;
                $helper->fields_value["ALMA_${key}_SORT_ORDER"] = $order;
                ++$i;
            }
        }

        $helper->languages = $this->context->controller->getLanguages();

        return $extraMessage . $helper->generateForm($fieldsForms);
    }

    private function assignSmartyAlertClasses($level = 'danger')
    {
        $token = Tools::getAdminTokenLite('AdminModules');
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

    public function needsAPIKey()
    {
        $key = trim(Settings::getActiveAPIKey());

        return $key == '' || $key == null;
    }

    public function run($params)
    {
        $messages = '';
        $this->assignSmartyAlertClasses();

        if (Tools::isSubmit('alma_config_form')) {
            $messages = $this->processConfiguration();
        } elseif (!$this->needsAPIKey()) {
            $messages = $this->credentialsError(
                Settings::getActiveMode(),
                Settings::getLiveKey(),
                Settings::getTestKey()
            );

            if ($messages) {
                $messages = $messages['message'];
            }
        }

        $htmlForm = $this->renderForm();

        return $messages . $htmlForm;
    }
}
