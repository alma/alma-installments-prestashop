<?php
/**
 * 2018-2021 Alma SAS
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
 * @copyright 2018-2021 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Controllers\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\API\RequestError;
use Alma\PrestaShop\API\ClientHelper;
use Alma\PrestaShop\Hooks\AdminHookController;
use Alma\PrestaShop\Utils\Logger;
use Alma\PrestaShop\Utils\Settings;
use Configuration;
use HelperForm;
use Media;
use OrderState;
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
                    $almaPlans[$key]['sort'] = 1;
                    Settings::updateValue('ALMA_FEE_PLANS', json_encode($almaPlans));
                    break;
                }
            }
        }

        if (!$apiOnly) {
            $title = Tools::getValue('ALMA_PAYMENT_BUTTON_TITLE');
            $titleDeferred = Tools::getValue('ALMA_DEFERRED_BUTTON_TITLE');
            $description = Tools::getValue('ALMA_PAYMENT_BUTTON_DESC');
            $descriptionDeferred = Tools::getValue('ALMA_DEFERRED_BUTTON_DESC');
            $position = (int) Tools::getValue('ALMA_PAYMENT_BUTTON_POSITION');
            if (!$position) {
                $position = 1;
            }
            $positionDeferred = Tools::getValue('ALMA_DEFERRED_BUTTON_POSITION');
            if (!$positionDeferred) {
                $positionDeferred = 2;
            }
            $showEligibility = (bool) Tools::getValue('ALMA_SHOW_ELIGIBILITY_MESSAGE_ON');
            $eligibleMsg = Tools::getValue('ALMA_IS_ELIGIBLE_MESSAGE');
            $nonEligibleMsg = Tools::getValue('ALMA_NOT_ELIGIBLE_MESSAGE');
            $nonEligibleCategoriesMsg = Tools::getValue('ALMA_NOT_ELIGIBLE_CATEGORIES');

            if (
                empty($title) || empty($description) ||
                empty($titleDeferred) || empty($descriptionDeferred) ||
                ($showEligibility && (empty($eligibleMsg) || empty($nonEligibleMsg)))
            ) {
                $this->context->smarty->assign('validation_error', 'missing_required_setting');

                return $this->module->display($this->module->file, 'getContent.tpl');
            }

            $showProductEligibility = (bool) Tools::getValue('ALMA_SHOW_PRODUCT_ELIGIBILITY_ON');
            Settings::updateValue('ALMA_SHOW_PRODUCT_ELIGIBILITY', $showProductEligibility ? '1' : '0');

            $productPriceQuerySelector = Tools::getValue('ALMA_PRODUCT_PRICE_SELECTOR');
            Settings::updateValue('ALMA_PRODUCT_PRICE_SELECTOR', $productPriceQuerySelector);

            $productAttrQuerySelector = Tools::getValue('ALMA_PRODUCT_ATTR_SELECTOR');
            Settings::updateValue('ALMA_PRODUCT_ATTR_SELECTOR', $productAttrQuerySelector);

            $productAttrRadioQuerySelector = Tools::getValue('ALMA_PRODUCT_ATTR_RADIO_SELECTOR');
            Settings::updateValue('ALMA_PRODUCT_ATTR_RADIO_SELECTOR', $productAttrRadioQuerySelector);

            $productColorPickQuerySelector = Tools::getValue('ALMA_PRODUCT_COLOR_PICK_SELECTOR');
            Settings::updateValue('ALMA_PRODUCT_COLOR_PICK_SELECTOR', $productColorPickQuerySelector);

            $productQuantityQuerySelector = Tools::getValue('ALMA_PRODUCT_QUANTITY_SELECTOR');
            Settings::updateValue('ALMA_PRODUCT_QUANTITY_SELECTOR', $productQuantityQuerySelector);

            Settings::updateValue('ALMA_PAYMENT_BUTTON_TITLE', $title);
            Settings::updateValue('ALMA_PAYMENT_BUTTON_DESC', $description);
            Settings::updateValue('ALMA_PAYMENT_BUTTON_POSITION', $position);
            Settings::updateValue('ALMA_DEFERRED_BUTTON_TITLE', $titleDeferred);
            Settings::updateValue('ALMA_DEFERRED_BUTTON_DESC', $descriptionDeferred);
            Settings::updateValue('ALMA_DEFERRED_BUTTON_POSITION', $positionDeferred);

            $showDisabledButton = (bool) Tools::getValue('ALMA_SHOW_DISABLED_BUTTON');
            Settings::updateValue('ALMA_SHOW_DISABLED_BUTTON', $showDisabledButton);

            Settings::updateValue('ALMA_SHOW_ELIGIBILITY_MESSAGE', $showEligibility);
            Settings::updateValue('ALMA_IS_ELIGIBLE_MESSAGE', $eligibleMsg);
            Settings::updateValue('ALMA_NOT_ELIGIBLE_MESSAGE', $nonEligibleMsg);
            Settings::updateValue('ALMA_NOT_ELIGIBLE_CATEGORIES', $nonEligibleCategoriesMsg);

            $idStateRefund = Tools::getValue('ALMA_STATE_REFUND');
            Settings::updateValue('ALMA_STATE_REFUND', $idStateRefund);

            $isStateRefundEnabled = (bool) Tools::getValue('ALMA_STATE_REFUND_ENABLED_ON');
            Settings::updateValue('ALMA_STATE_REFUND_ENABLED', $isStateRefundEnabled);

            $displayOrderConfirmation = (bool) Tools::getValue('ALMA_DISPLAY_ORDER_CONFIRMATION_ON');
            Settings::updateValue('ALMA_DISPLAY_ORDER_CONFIRMATION', $displayOrderConfirmation);

            $activateLogging = (bool) Tools::getValue('ALMA_ACTIVATE_LOGGING_ON');
            Settings::updateValue('ALMA_ACTIVATE_LOGGING', $activateLogging);

            if ($merchant) {
                // First validate that plans boundaries are correctly set
                $feePlans = $this->getFeePlans();
                foreach ($feePlans as $feePlan) {
                    $n = $feePlan->installments_count;
                    $key = Settings::keyForFeePlan($feePlan);
                    if (1 == $n && !Settings::isDeferred($feePlan)) {
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
                            'min' => almaPriceFromCents($feePlan->min_purchase_amount),
                            'max' => almaPriceFromCents(min($max, $feePlan->max_purchase_amount)),
                        ]);

                        return $this->module->display($this->module->file, 'getContent.tpl');
                    }

                    if ($enablePlan && !($max >= $min && $max <= $feePlan->max_purchase_amount)) {
                        $this->context->smarty->assign([
                            'validation_error' => 'pnx_max_amount',
                            'n' => $n,
                            'min' => almaPriceFromCents($min),
                            'max' => almaPriceFromCents($feePlan->max_purchase_amount),
                        ]);

                        return $this->module->display($this->module->file, 'getContent.tpl');
                    }
                }

                $almaPlans = [];
                foreach ($feePlans as $feePlan) {
                    $n = $feePlan->installments_count;
                    $key = Settings::keyForFeePlan($feePlan);

                    if (1 == $n && !Settings::isDeferred($feePlan)) {
                        continue;
                    }

                    $min = (int) Tools::getValue("ALMA_${key}_MIN_AMOUNT");
                    $max = (int) Tools::getValue("ALMA_${key}_MAX_AMOUNT");

                    // In case merchant inverted min & max values, correct it
                    if ($min > $max) {
                        $realMin = $max;
                        $max = $min;
                        $min = $realMin;
                    }

                    $enablePlan = (bool) Tools::getValue("ALMA_${key}_ENABLED_ON");
                    $almaPlans[$key]['enabled'] = $enablePlan ? '1' : '0';
                    $almaPlans[$key]['min'] = almaPriceToCents($min);
                    $almaPlans[$key]['max'] = almaPriceToCents($max);
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

        $apiConfigForm = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('API configuration', 'GetContentHookController'),
                    'image' => $iconPath,
                ],
                'input' => [
                    [
                        'name' => 'ALMA_API_MODE',
                        'label' => $this->module->l('API Mode', 'GetContentHookController'),
                        'type' => 'select',
                        'required' => true,
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        // phpcs:ignore
                        'desc' => $this->module->l('Use Test mode until you are ready to take real orders with Alma. In Test mode, only admins can see Alma on cart/checkout pages.', 'GetContentHookController'),
                        'options' => [
                            'id' => 'api_mode',
                            'name' => 'name',
                            'query' => [
                                ['api_mode' => ALMA_MODE_LIVE, 'name' => 'Live'],
                                ['api_mode' => ALMA_MODE_TEST, 'name' => 'Test'],
                            ],
                        ],
                    ],

                    [
                        'name' => 'ALMA_LIVE_API_KEY',
                        'label' => $this->module->l('Live API key', 'GetContentHookController'),
                        'type' => 'text',
                        'size' => 75,
                        'required' => false,
                        'desc' => $this->module->l('Not required for Test mode', 'GetContentHookController') .
                            ' – ' .
                            sprintf(
                                // phpcs:ignore
                                $this->module->l('You can find your Live API key on %1$syour Alma dashboard%2$s', 'GetContentHookController'),
                                '<a href="https://dashboard.getalma.eu/api" target="_blank">',
                                '</a>'
                            ),
                    ],
                    [
                        'name' => 'ALMA_TEST_API_KEY',
                        'label' => $this->module->l('Test API key', 'GetContentHookController'),
                        'type' => 'text',
                        'size' => 75,
                        'required' => false,
                        'desc' => $this->module->l('Not required for Live mode', 'GetContentHookController') .
                            ' – ' .
                            sprintf(
                                // phpcs:ignore
                                $this->module->l('You can find your Test API key on %1$syour sandbox dashboard%2$s', 'GetContentHookController'),
                                '<a href="https://dashboard.sandbox.getalma.eu/api" target="_blank">',
                                '</a>'
                            ),
                    ],
                ],
                'submit' => ['title' => $this->module->l('Save'), 'class' => 'button btn btn-default pull-right'],
            ],
        ];

        $pnxConfigForm = null;
        if ($merchant) {
            $pnxConfigForm = [
                'form' => [
                    'legend' => [
                        'title' => $this->module->l('Installments plans', 'GetContentHookController'),
                        'image' => $iconPath,
                    ],
                    'input' => [],
                    'submit' => ['title' => $this->module->l('Save'), 'class' => 'button btn btn-default pull-right'],
                ],
            ];

            $pnxTabs = [];
            $activeTab = null;

            $feePlans = $this->getFeePlans();
            $installmentsPlans = json_decode(Settings::getFeePlans());

            // sort fee plans by pnx then by pay later duration
            $feePlansOrdered = [];
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

            foreach ($feePlansOrdered as $feePlan) {
                $key = Settings::keyForFeePlan($feePlan);
                if (1 == $feePlan->installments_count && !Settings::isDeferred($feePlan)) {
                    continue;
                }

                // Disable and hide disallowed fee plans
                if (!$feePlan->allowed) {
                    unset($installmentsPlans->$key);
                    Settings::updateValue('ALMA_FEE_PLANS', json_encode($installmentsPlans));

                    continue;
                }

                $tabId = $key;
                $tabTitle = sprintf($this->module->l('%d-installment payments', 'GetContentHookController'), $feePlan->installments_count);
                $duration = Settings::getDuration($feePlan);
                $label = sprintf(
                    $this->module->l('Enable %d-installment payments', 'GetContentHookController'),
                    $feePlan->installments_count
                );
                if (Settings::isDeferred($feePlan)) {
                    if ($feePlan->installments_count == 1) {
                        $tabTitle = sprintf($this->module->l('Deferred payments + %d days', 'GetContentHookController'), $duration);
                        $label = sprintf(
                            $this->module->l('Enable deferred payments +%d days', 'GetContentHookController'),
                            $duration
                        );
                    } else {
                        $tabTitle = sprintf($this->module->l('%d-deferred payments + %d days', 'GetContentHookController'), $feePlan->installments_count, $duration);
                        $label = sprintf(
                            $this->module->l('Enable %d-deferred payments +%d days', 'GetContentHookController'),
                            $feePlan->installments_count,
                            $duration
                        );
                    }
                }

                $enable = isset($installmentsPlans->$key->enabled) ? $installmentsPlans->$key->enabled : 0;
                if (1 == $enable) {
                    $pnxTabs[$tabId] = '✅ ' . $tabTitle;
                    $activeTab = $activeTab ?: $tabId;
                } else {
                    $pnxTabs[$tabId] = '❌ ' . $tabTitle;
                }

                $minAmount = (int) almaPriceFromCents($feePlan->min_purchase_amount);
                $maxAmount = (int) almaPriceFromCents($feePlan->max_purchase_amount);

                $tpl = $this->context->smarty->createTemplate(
                    "{$this->module->local_path}views/templates/hook/pnx_fees.tpl"
                );
                $tpl->assign(['fee_plan' => (array) $feePlan, 'min_amount' => $minAmount, 'max_amount' => $maxAmount, 'deferred' => $duration]);

                $pnxConfigForm['form']['input'][] = [
                    // Prevent notices for undefined index
                    'name' => null,
                    'label' => null,
                    ///
                    'form_group_class' => "$tabId-content",
                    'type' => 'html',
                    'html_content' => $tpl->fetch(),
                ];

                $pnxConfigForm['form']['input'][] = [
                    'form_group_class' => "$tabId-content",
                    'name' => "ALMA_${key}_ENABLED",
                    'label' => $label,
                    'type' => 'checkbox',
                    'values' => [
                        'id' => 'id',
                        'name' => 'label',
                        'query' => [
                            [
                                'id' => 'ON',
                                'val' => true,
                                'label' => '',
                            ],
                        ],
                    ],
                ];

                $pnxConfigForm['form']['input'][] = [
                    'form_group_class' => "$tabId-content",
                    'name' => "ALMA_${key}_MIN_AMOUNT",
                    'label' => $this->module->l('Minimum amount (€)', 'GetContentHookController'),
                    // phpcs:ignore
                    'desc' => $this->module->l('Minimum purchase amount to activate this plan', 'GetContentHookController'),
                    'type' => 'number',
                    'min' => $minAmount,
                    'max' => $maxAmount,
                ];

                $pnxConfigForm['form']['input'][] = [
                    'form_group_class' => "$tabId-content",
                    'name' => "ALMA_${key}_MAX_AMOUNT",
                    'label' => $this->module->l('Maximum amount (€)', 'GetContentHookController'),
                    // phpcs:ignore
                    'desc' => $this->module->l('Maximum purchase amount to activate this plan', 'GetContentHookController'),
                    'type' => 'number',
                    'min' => $minAmount,
                    'max' => $maxAmount,
                ];
            }

            $tpl = $this->context->smarty->createTemplate(
                "{$this->module->local_path}views/templates/hook/pnx_tabs.tpl"
            );
            $forceTabs = false;
            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                $forceTabs = true;
            }
            $tpl->assign(['tabs' => $pnxTabs, 'active' => $activeTab, 'forceTabs' => $forceTabs]);

            array_unshift(
                $pnxConfigForm['form']['input'],
                [
                    'name' => null,
                    'label' => null,
                    'type' => 'html',
                    'html_content' => $tpl->fetch(),
                ]
            );
        }

        $paymentButtonForm = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Payment method configuration', 'GetContentHookController'),
                    'image' => $iconPath,
                ],
                'input' => [
                    [
                        'name' => null,
                        'label' => null,
                        'type' => 'html',
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        // phpcs:ignore
                        'html_content' => "<h4>{$this->module->l('Payment by installment', 'GetContentHookController')}</h4>",
                    ],
                    [
                        'name' => 'ALMA_PAYMENT_BUTTON_TITLE',
                        'label' => $this->module->l('Title', 'GetContentHookController'),
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        // phpcs:ignore
                        'desc' => $this->module->l('This controls the payment method name which the user sees during checkout.', 'GetContentHookController'),
                        'type' => 'text',
                        'size' => 75,
                        'required' => true,
                    ],
                    [
                        'name' => 'ALMA_PAYMENT_BUTTON_DESC',
                        'label' => $this->module->l('Description', 'GetContentHookController'),
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        // phpcs:ignore
                        'desc' => $this->module->l('This controls the payment method description which the user sees during checkout.', 'GetContentHookController'),
                        'type' => 'text',
                        'size' => 75,
                        'required' => true,
                    ],
                    [
                        'name' => 'ALMA_PAYMENT_BUTTON_POSITION',
                        'label' => $this->module->l('Position', 'GetContentHookController'),
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        // phpcs:ignore
                        'desc' => $this->module->l('Use relative values to set the order on the checkout page', 'GetContentHookController'),
                        'type' => 'text',
                        'size' => 75,
                    ],
                    [
                        'name' => null,
                        'label' => null,
                        'type' => 'html',
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        // phpcs:ignore
                        'html_content' => "<h4>{$this->module->l('Defered payment', 'GetContentHookController')}</h4>",
                    ],
                    [
                        'name' => 'ALMA_DEFERRED_BUTTON_TITLE',
                        'label' => $this->module->l('Title', 'GetContentHookController'),
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        // phpcs:ignore
                        'desc' => $this->module->l('This controls the payment method name which the user sees during checkout.', 'GetContentHookController'),
                        'type' => 'text',
                        'size' => 75,
                        'required' => true,
                    ],
                    [
                        'name' => 'ALMA_DEFERRED_BUTTON_DESC',
                        'label' => $this->module->l('Description', 'GetContentHookController'),
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        // phpcs:ignore
                        'desc' => $this->module->l('This controls the payment method description which the user sees during checkout.', 'GetContentHookController'),
                        'type' => 'text',
                        'size' => 75,
                        'required' => true,
                    ],
                    [
                        'name' => 'ALMA_DEFERRED_BUTTON_POSITION',
                        'label' => $this->module->l('Position', 'GetContentHookController'),
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        // phpcs:ignore
                        'desc' => $this->module->l('Use relative values to set the order on the checkout page', 'GetContentHookController'),
                        'type' => 'text',
                        'size' => 75,
                    ],
                ],
                'submit' => ['title' => $this->module->l('Save'), 'class' => 'button btn btn-default pull-right'],
            ],
        ];

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $paymentButtonForm['form']['input'][] = [
                'name' => 'ALMA_SHOW_DISABLED_BUTTON',
                'type' => 'radio',
                'label' => $this->module->l('When Alma is not available...', 'GetContentHookController'),
                'class' => 't',
                'required' => true,
                'values' => [
                    [
                        'id' => 'ON',
                        'value' => true,
                        'label' => $this->module->l('Display payment button, disabled', 'GetContentHookController'),
                    ],
                    [
                        'id' => 'OFF',
                        'value' => false,
                        'label' => $this->module->l('Hide payment button', 'GetContentHookController'),
                    ],
                ],
            ];
        }

        $productEligibilityForm = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Eligibility on product pages', 'GetContentHookController'),
                    'image' => $iconPath,
                ],
                'input' => [
                    [
                        'name' => 'ALMA_SHOW_PRODUCT_ELIGIBILITY',
                        // phpcs:ignore
                        'label' => $this->module->l('Show product eligibility on details page', 'GetContentHookController'),
                        // phpcs:ignore
                        'desc' => $this->module->l('Displays a badge with eligible Alma plans with installments details', 'GetContentHookController'),
                        'type' => 'checkbox',
                        'values' => [
                            'id' => 'id',
                            'name' => 'label',
                            'query' => [
                                [
                                    'id' => 'ON',
                                    'val' => true,
                                    // PrestaShop won't detect the string if the call to `l` is multiline
                                    // phpcs:ignore
                                    'label' => $this->module->l('Display the product\'s eligibility', 'GetContentHookController'),
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => 'ALMA_PRODUCT_PRICE_SELECTOR',
                        'label' => $this->module->l('Product price query selector', 'GetContentHookController'),
                        'desc' => sprintf(
                            // PrestaShop won't detect the string if the call to `l` is multiline
                            // phpcs:ignore
                            $this->module->l('%1$sAdvanced%2$s Query selector for our scripts to correctly find the displayed price of a product', 'GetContentHookController'),
                            '<b>',
                            '</b>'
                        ),
                        'type' => 'text',
                        'size' => 75,
                        'required' => true,
                    ],
                    [
                        'name' => 'ALMA_PRODUCT_ATTR_SELECTOR',
                        // phpcs:ignore
                        'label' => $this->module->l('Product attribute dropdown query selector', 'GetContentHookController'),
                        'desc' => sprintf(
                            // PrestaShop won't detect the string if the call to `l` is multiline
                            // phpcs:ignore
                            $this->module->l('%1$sAdvanced%2$s Query selector for our scripts to correctly find the selected attributes of a product combination', 'GetContentHookController'),
                            '<b>',
                            '</b>'
                        ),
                        'type' => 'text',
                        'size' => 75,
                        'required' => true,
                    ],
                    [
                        'name' => 'ALMA_PRODUCT_ATTR_RADIO_SELECTOR',
                        // phpcs:ignore
                        'label' => $this->module->l('Product attribute radio button query selector', 'GetContentHookController'),
                        'desc' => sprintf(
                            // PrestaShop won't detect the string if the call to `l` is multiline
                            // phpcs:ignore
                            $this->module->l('%1$sAdvanced%2$s Query selector for our scripts to correctly find the selected attributes of a product combination', 'GetContentHookController'),
                            '<b>',
                            '</b>'
                        ),
                        'type' => 'text',
                        'size' => 75,
                        'required' => true,
                    ],
                    [
                        'name' => 'ALMA_PRODUCT_COLOR_PICK_SELECTOR',
                        'label' => $this->module->l('Product color picker query selector', 'GetContentHookController'),
                        'desc' => sprintf(
                            // PrestaShop won't detect the string if the call to `l` is multiline
                            // phpcs:ignore
                            $this->module->l('%1$sAdvanced%2$s Query selector for our scripts to correctly find the chosen color option of a product', 'GetContentHookController'),
                            '<b>',
                            '</b>'
                        ),
                        'type' => 'text',
                        'size' => 75,
                        'required' => true,
                    ],
                    [
                        'name' => 'ALMA_PRODUCT_QUANTITY_SELECTOR',
                        'label' => $this->module->l('Product quantity query selector', 'GetContentHookController'),
                        'desc' => sprintf(
                            // PrestaShop won't detect the string if the call to `l` is multiline
                            // phpcs:ignore
                            $this->module->l('%1$sAdvanced%2$s Query selector for our scripts to correctly find the wanted quantity of a product', 'GetContentHookController'),
                            '<b>',
                            '</b>'
                        ),
                        'type' => 'text',
                        'size' => 75,
                        'required' => true,
                    ],
                ],
                'submit' => ['title' => $this->module->l('Save'), 'class' => 'button btn btn-default pull-right'],
            ],
        ];

        $cartEligibilityForm = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Cart eligibility message', 'GetContentHookController'),
                    'image' => $iconPath,
                ],
                'input' => [
                    [
                        'name' => 'ALMA_SHOW_ELIGIBILITY_MESSAGE',
                        'label' => $this->module->l('Show eligibility message', 'GetContentHookController'),
                        'type' => 'checkbox',
                        'values' => [
                            'id' => 'id',
                            'name' => 'label',
                            'query' => [
                                [
                                    'id' => 'ON',
                                    'val' => true,
                                    // PrestaShop won't detect the string if the call to `l` is multiline
                                    // phpcs:ignore
                                    'label' => $this->module->l('Display a message under the cart summary to indicate its eligibility for monthly installments.', 'GetContentHookController'),
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => 'ALMA_IS_ELIGIBLE_MESSAGE',
                        'label' => $this->module->l('Eligibility message', 'GetContentHookController'),
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        // phpcs:ignore
                        'desc' => $this->module->l('Message displayed below the cart totals when it is eligible for monthly installments.', 'GetContentHookController'),
                        'type' => 'text',
                        'size' => 75,
                        'required' => true,
                    ],
                    [
                        'name' => 'ALMA_NOT_ELIGIBLE_MESSAGE',
                        'label' => $this->module->l('Non-eligibility message', 'GetContentHookController'),
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        // phpcs:ignore
                        'desc' => $this->module->l('Message displayed below the cart totals when it is not eligible for monthly installments.', 'GetContentHookController'),
                        'type' => 'text',
                        'size' => 75,
                        'required' => true,
                    ],
                ],
                'submit' => ['title' => $this->module->l('Save'), 'class' => 'button btn btn-default pull-right'],
            ],
        ];

        $orderConfirmationForm = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Order confirmation', 'GetContentHookController'),
                    'image' => $iconPath,
                ],
                'input' => [
                    [
                        'name' => 'ALMA_DISPLAY_ORDER_CONFIRMATION',
                        'label' => $this->module->l('Display order confirmation', 'GetContentHookController'),
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        // phpcs:ignore
                        'desc' => $this->module->l('Activate this setting when you do not have your own order confirmation page', 'GetContentHookController'),
                        'type' => 'checkbox',
                        'values' => [
                            'id' => 'id',
                            'name' => 'label',
                            'query' => [
                                [
                                    'id' => 'ON',
                                    'val' => true,
                                    // PrestaShop won't detect the string if the call to `l` is multiline
                                    // phpcs:ignore
                                    'label' => $this->module->l('Confirm successful order to customers when they come back from the Alma payment page', 'GetContentHookController'),
                                ],
                            ],
                        ],
                    ],
                ],
                'submit' => ['title' => $this->module->l('Save'), 'class' => 'button btn btn-default pull-right'],
            ],
        ];

        // Exclusion
        $tpl = $this->context->smarty->createTemplate(
            "{$this->module->local_path}views/templates/hook/excludedCategories.tpl"
        );

        $excludedCategoryNames = Settings::getExcludedCategoryNames();

        $tpl->assign([
            'excludedCategories' => count($excludedCategoryNames) > 0
                ? implode(', ', $excludedCategoryNames)
                : $this->module->l('No excluded categories', 'GetContentHookController'),
            'excludedLink' => $this->context->link->getAdminLink('AdminAlmaCategories'),
        ]);
        $excludedForm = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Excluded categories', 'GetContentHookController'),
                    'image' => $iconPath,
                ],
                'input' => [
                    [
                        'name' => null,
                        'label' => null,
                        'type' => 'html',
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        'desc' => $tpl->fetch(),
                    ],
                    [
                        'name' => 'ALMA_NOT_ELIGIBLE_CATEGORIES',
                        // phpcs:ignore
                        'label' => $this->module->l('Excluded categories non-eligibility message ', 'GetContentHookController'),
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        // phpcs:ignore
                        'desc' => $this->module->l('Message displayed on an excluded product page or on the cart page if it contains an excluded product.', 'GetContentHookController'),
                        'type' => 'text',
                        'size' => 75,
                        'required' => false,
                    ],
                ],
                'submit' => ['title' => $this->module->l('Save'), 'class' => 'button btn btn-default pull-right'],
            ],
        ];

        $refundStateForm = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Refund with state change', 'GetContentHookController'),
                    'image' => $iconPath,
                ],
                'input' => [
                    [
                        'name' => null,
                        'label' => null,
                        'type' => 'html',
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        // phpcs:ignore
                        'html_content' => $this->module->l('If you usually refund orders by changing their state, activate this option and choose the state you want to use to trigger refunds on Alma payments', 'GetContentHookController'),
                    ],
                    [
                        'name' => 'ALMA_STATE_REFUND_ENABLED',
                        'label' => $this->module->l('Activate refund by change state', 'GetContentHookController'),
                        'type' => 'checkbox',
                        'values' => [
                            'id' => 'id',
                            'name' => 'label',
                            'query' => [
                                [
                                    'id' => 'ON',
                                    'val' => true,
                                    'label' => '',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => 'ALMA_STATE_REFUND',
                        'label' => $this->module->l('Refund state order', 'GetContentHookController'),
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        // phpcs:ignore
                        'desc' => $this->module->l('Your order state to sync refund with Alma', 'GetContentHookController'),
                        'type' => 'select',
                        'required' => true,
                        'options' => [
                            'query' => OrderState::getOrderStates($this->context->cookie->id_lang),
                            'id' => 'id_order_state',
                            'name' => 'name',
                        ],
                    ],
                ],
                'submit' => ['title' => $this->module->l('Save'), 'class' => 'button btn btn-default pull-right'],
            ],
        ];

        $debugForm = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Debug options', 'GetContentHookController'),
                    'image' => $iconPath,
                ],
                'input' => [
                    [
                        'name' => 'ALMA_ACTIVATE_LOGGING',
                        'label' => $this->module->l('Activate logging', 'GetContentHookController'),
                        'type' => 'checkbox',
                        'values' => [
                            'id' => 'id',
                            'name' => 'label',
                            'query' => [
                                [
                                    'id' => 'ON',
                                    'val' => true,
                                    'label' => '',
                                ],
                            ],
                        ],
                    ],
                ],
                'submit' => ['title' => $this->module->l('Save'), 'class' => 'button btn btn-default pull-right'],
            ],
        ];

        if ($needsKeys) {
            $apiConfigForm['form']['input'][] = [
                'name' => '_api_only',
                'label' => null,
                'type' => 'hidden',
            ];
            $fieldsForms = [$apiConfigForm, $debugForm];
        } else {
            $fieldsForms = [];

            if ($pnxConfigForm) {
                $fieldsForms[] = $pnxConfigForm;
            }

            $fieldsForms = array_merge($fieldsForms, [
                $productEligibilityForm,
                $cartEligibilityForm,
                $paymentButtonForm,
                $excludedForm,
                $refundStateForm,
                $orderConfirmationForm,
                $apiConfigForm,
                $debugForm,
            ]);
        }

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
            'ALMA_PAYMENT_BUTTON_TITLE' => Settings::getPaymentButtonTitle(),
            'ALMA_PAYMENT_BUTTON_DESC' => Settings::getPaymentButtonDescription(),
            'ALMA_PAYMENT_BUTTON_POSITION' => Settings::getPaymentButtonPosition(),
            'ALMA_DEFERRED_BUTTON_TITLE' => Settings::getPaymentButtonTitleDeferred(),
            'ALMA_DEFERRED_BUTTON_DESC' => Settings::getPaymentButtonDescriptionDeferred(),
            'ALMA_DEFERRED_BUTTON_POSITION' => Settings::getPaymentButtonPositionDeferred(),
            'ALMA_SHOW_DISABLED_BUTTON' => Settings::showDisabledButton(),
            'ALMA_SHOW_ELIGIBILITY_MESSAGE_ON' => Settings::showEligibilityMessage(),
            'ALMA_IS_ELIGIBLE_MESSAGE' => Settings::getEligibilityMessage(),
            'ALMA_NOT_ELIGIBLE_MESSAGE' => Settings::getNonEligibilityMessage(),
            'ALMA_DISPLAY_ORDER_CONFIRMATION_ON' => Settings::displayOrderConfirmation(),
            'ALMA_ACTIVATE_LOGGING_ON' => (bool) Settings::canLog(),
            'ALMA_STATE_REFUND' => Settings::getRefundState(),
            'ALMA_STATE_REFUND_ENABLED_ON' => Settings::isRefundEnabledByState(),
            'ALMA_NOT_ELIGIBLE_CATEGORIES' => Settings::getNonEligibleCategoriesMessage(),
            'ALMA_SHOW_PRODUCT_ELIGIBILITY_ON' => Settings::showProductEligibility(),
            'ALMA_PRODUCT_PRICE_SELECTOR' => Settings::getProductPriceQuerySelector(),
            'ALMA_PRODUCT_ATTR_SELECTOR' => Settings::getProductAttrQuerySelector(),
            'ALMA_PRODUCT_ATTR_RADIO_SELECTOR' => Settings::getProductAttrRadioQuerySelector(),
            'ALMA_PRODUCT_COLOR_PICK_SELECTOR' => Settings::getProductColorPickQuerySelector(),
            'ALMA_PRODUCT_QUANTITY_SELECTOR' => Settings::getProductQuantityQuerySelector(),
            '_api_only' => true,
        ];

        if ($merchant) {
            $i = 1;
            foreach ($feePlans as $feePlan) {
                $key = Settings::keyForFeePlan($feePlan);
                if ((1 == $feePlan->installments_count && !Settings::isDeferred($feePlan))
                    || !$feePlan->allowed) {
                    continue;
                }

                $helper->fields_value["ALMA_${key}_ENABLED_ON"] = isset($installmentsPlans->$key->enabled) ? $installmentsPlans->$key->enabled : 0;
                $minAmount = isset($installmentsPlans->$key->min) ? $installmentsPlans->$key->min : $feePlan->min_purchase_amount;
                $helper->fields_value["ALMA_${key}_MIN_AMOUNT"] = (int) almaPriceFromCents($minAmount);
                $maxAmount = isset($installmentsPlans->$key->max) ? $installmentsPlans->$key->max : $feePlan->max_purchase_amount;
                $helper->fields_value["ALMA_${key}_MAX_AMOUNT"] = (int) almaPriceFromCents($maxAmount);
                $sortOrder = isset($installmentsPlans->$key->sort) ? $installmentsPlans->$key->sort : $i;
                ++$i;
            }
        }

        $helper->languages = $this->context->controller->getLanguages();

        return $extraMessage . $helper->generateForm($fieldsForms);
    }

    private function assignSmartyAlertClasses($level = 'danger')
    {
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
