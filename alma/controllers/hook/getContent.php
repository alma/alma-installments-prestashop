<?php
/**
 * 2018-2020 Alma SAS
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
 * @copyright 2018-2020 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

use Alma\API\RequestError;

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaAdminHookController.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaSettings.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaShareOfCheckout.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaClient.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaLogger.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/functions.php';

class AlmaGetContentController extends AlmaAdminHookController
{
    public function processConfiguration($merchant)
    {
        if (!Tools::isSubmit('alma_config_form')) {
            return null;
        }

        // Consider the plugin as fully configured only when everything goes well
        AlmaSettings::updateValue('ALMA_FULLY_CONFIGURED', '0');

        $apiOnly = Tools::getValue('_api_only');

        if (!$apiOnly) {
            $title = Tools::getValue('ALMA_PAYMENT_BUTTON_TITLE');
            $description = Tools::getValue('ALMA_PAYMENT_BUTTON_DESC');
            $showEligibility = Tools::getValue('ALMA_SHOW_ELIGIBILITY_MESSAGE_ON') == '1';
            $eligibleMsg = Tools::getValue('ALMA_IS_ELIGIBLE_MESSAGE');
            $nonEligibleMsg = Tools::getValue('ALMA_NOT_ELIGIBLE_MESSAGE');

            if (empty($title) || empty($description) ||
                ($showEligibility && (empty($eligibleMsg) || empty($nonEligibleMsg)))) {
                $this->context->smarty->assign('validation_error', 'missing_required_setting');

                return $this->module->display($this->module->file, 'getContent.tpl');
            }

            AlmaSettings::updateValue('ALMA_PAYMENT_BUTTON_TITLE', $title);
            AlmaSettings::updateValue('ALMA_PAYMENT_BUTTON_DESC', $description);

            $showDisabledButton = Tools::getValue('ALMA_SHOW_DISABLED_BUTTON') == '1';
            AlmaSettings::updateValue('ALMA_SHOW_DISABLED_BUTTON', $showDisabledButton);

            AlmaSettings::updateValue('ALMA_SHOW_ELIGIBILITY_MESSAGE', $showEligibility);
            AlmaSettings::updateValue('ALMA_IS_ELIGIBLE_MESSAGE', $eligibleMsg);
            AlmaSettings::updateValue('ALMA_NOT_ELIGIBLE_MESSAGE', $nonEligibleMsg);

            $idStateRefund = Tools::getValue('ALMA_STATE_REFUND');
            AlmaSettings::updateValue('ALMA_STATE_REFUND', $idStateRefund);

            $isStateRefundEnabled = Tools::getValue('ALMA_STATE_REFUND_ENABLED_ON', 0);
            AlmaSettings::updateValue('ALMA_STATE_REFUND_ENABLED', $isStateRefundEnabled);

            $displayOrderConfirmation = Tools::getValue('ALMA_DISPLAY_ORDER_CONFIRMATION_ON') == '1';
            AlmaSettings::updateValue('ALMA_DISPLAY_ORDER_CONFIRMATION', $displayOrderConfirmation);

            //$almaShareOfCheckout = new AlmaShareOfCheckout($this->context, $this->module);
            //$isShareOfCheckout = Tools::getValue('ALMA_SHARE_OF_CHECKOUT_ON') == '1';
            AlmaSettings::updateValue('ALMA_SHARE_OF_CHECKOUT', 1);
            //$almaShareOfCheckout->toggleShare($isShareOfCheckout);


            $activateLogging = Tools::getValue('ALMA_ACTIVATE_LOGGING_ON') == '1';
            AlmaSettings::updateValue('ALMA_ACTIVATE_LOGGING', $activateLogging);

            if ($merchant) {
                // First validate that plans boundaries are correctly set
                foreach ($merchant->fee_plans as $feePlan) {
                    $n = $feePlan['installments_count'];
                    $min = almaPriceToCents(Tools::getValue("ALMA_P${n}X_MIN_AMOUNT"));
                    $max = almaPriceToCents(Tools::getValue("ALMA_P${n}X_MAX_AMOUNT"));

                    $enablePlan = Tools::getValue("ALMA_P${n}X_ENABLED_ON") == '1';

                    if ($enablePlan && !($min >= $merchant->minimum_purchase_amount &&
                            $min <= min($max, $merchant->maximum_purchase_amount))) {
                        $this->context->smarty->assign(array(
                            'validation_error' => 'pnx_min_amount',
                            'n' => $n,
                            'min' => almaPriceFromCents($merchant->minimum_purchase_amount),
                            'max' => almaPriceFromCents(min($max, $merchant->maximum_purchase_amount)),
                        ));

                        return $this->module->display($this->module->file, 'getContent.tpl');
                    }

                    if ($enablePlan && !($max >= $min && $max <= $merchant->maximum_purchase_amount)) {
                        $this->context->smarty->assign(array(
                            'validation_error' => 'pnx_max_amount',
                            'n' => $n,
                            'min' => almaPriceFromCents($min),
                            'max' => almaPriceFromCents($merchant->maximum_purchase_amount),
                        ));

                        return $this->module->display($this->module->file, 'getContent.tpl');
                    }
                }

                $maxN = 0;
                foreach ($merchant->fee_plans as $feePlan) {
                    $n = $feePlan['installments_count'];
                    $min = Tools::getValue("ALMA_P${n}X_MIN_AMOUNT");
                    $max = Tools::getValue("ALMA_P${n}X_MAX_AMOUNT");

                    $enablePlan = Tools::getValue("ALMA_P${n}X_ENABLED_ON") == '1';
                    AlmaSettings::updateValue("ALMA_P${n}X_ENABLED", $enablePlan ? '1' : '0');

                    // Validate that there's no purchase amount gaps between the different plans
                    // i.e. that there isn't a purchase amount too high to be eligible for some plans but too low
                    // to be eligible for the others
                    if ($enablePlan) {
                        $overlap = false;
                        foreach ($merchant->fee_plans as $other_plan) {
                            $otherN = $other_plan['installments_count'];
                            if ($n == $otherN) {
                                continue;
                            }

                            $otherMin = Tools::getValue("ALMA_P${otherN}X_MIN_AMOUNT");
                            $otherMax = Tools::getValue("ALMA_P${otherN}X_MAX_AMOUNT");

                            if (($min >= $otherMin && $min <= $otherMax) || ($max >= $otherMin && $max <= $otherMax)) {
                                $overlap = true;
                                break;
                            }
                        }

                        if (!$overlap) {
                            $this->context->smarty->assign(array(
                                'validation_error' => 'pnx_coverage_gap',
                                'n' => $n,
                            ));

                            return $this->module->display($this->module->file, 'getContent.tpl');
                        }
                    }

                    AlmaSettings::updateValue(
                        "ALMA_P${n}X_MIN_AMOUNT",
                        almaPriceToCents(Tools::getValue("ALMA_P${n}X_MIN_AMOUNT"))
                    );
                    AlmaSettings::updateValue(
                        "ALMA_P${n}X_MAX_AMOUNT",
                        almaPriceToCents(Tools::getValue("ALMA_P${n}X_MAX_AMOUNT"))
                    );

                    if ($n > $maxN && $enablePlan) {
                        $maxN = $n;
                    }
                }

                AlmaSettings::updateValue('ALMA_PNX_MAX_N', $maxN);
            }
        }

        $apiMode = Tools::getValue('ALMA_API_MODE');
        AlmaSettings::updateValue('ALMA_API_MODE', $apiMode);

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

        AlmaSettings::updateValue('ALMA_LIVE_API_KEY', $liveKey);
        AlmaSettings::updateValue('ALMA_TEST_API_KEY', $testKey);

        // At this point, consider things are sufficiently configured to be usable
        AlmaSettings::updateValue('ALMA_FULLY_CONFIGURED', '1');

        if ($credentialsError && array_key_exists('warning', $credentialsError)) {
            return $credentialsError['message'];
        }

        $this->context->smarty->clearAssign('validation_error');

        return $this->module->display($this->module->file, 'getContent.tpl');
    }

    private function credentialsError($apiMode, $liveKey, $testKey)
    {
        $modes = array(ALMA_MODE_TEST, ALMA_MODE_LIVE);

        foreach ($modes as $mode) {
            $key = ($mode == ALMA_MODE_LIVE ? $liveKey : $testKey);
            if (!$key) {
                continue;
            }

            $alma = AlmaClient::createInstance($key, $mode);
            if (!$alma) {
                $this->context->smarty->assign('validation_error', 'alma_client_null');

                $errorMessage = $this->module->display($this->module->file, 'getContent.tpl');

                return array('error' => true, 'message' => $errorMessage);
            }

            try {
                $merchant = $alma->merchants->me();
            } catch (RequestError $e) {
                if ($e->response && $e->response->responseCode === 401) {
                    $this->context->smarty->assign('validation_error', "{$mode}_authentication_error");

                    $errorMessage = $this->module->display($this->module->file, 'getContent.tpl');

                    return array('error' => true, 'message' => $errorMessage);
                } else {
                    AlmaLogger::instance()->error('Error while fetching merchant status: ' . $e->getMessage());

                    $this->context->smarty->assign('validation_error', 'api_request_error');
                    $this->context->smarty->assign('error', $e->getMessage());

                    $errorMessage = $this->module->display($this->module->file, 'getContent.tpl');

                    return array('error' => true, 'message' => $errorMessage);
                }
            }

            if (!$merchant->can_create_payments) {
                $this->context->smarty->assign('validation_error', "inactive_{$mode}_account");
                $this->assignSmartyAlertClasses($apiMode == $mode ? 'danger' : 'warning');

                $errorMessage = $this->module->display($this->module->file, 'getContent.tpl');

                return array('warning' => true, 'message' => $errorMessage);
            }
        }

        return null;
    }

    private function getMerchant()
    {
        $alma = AlmaClient::defaultInstance();

        if (!$alma) {
            return null;
        }

        try {
            return $alma->merchants->me();
        } catch (RequestError $e) {
            return null;
        }
    }

    public function renderForm($merchant)
    {
        $needsKeys = $this->needsAPIKey();

        if (is_callable('Media::getMediaPath')) {
            $iconPath = Media::getMediaPath(_PS_MODULE_DIR_ . $this->module->name . '/views/img/logo16x16.png');
        } else {
            $iconPath = $this->module->getPathUri() . '/views/img/logo16x16.png';
        }

        $extraMessage = null;
        if ($needsKeys && !Tools::isSubmit('alma_config_form')) {
            $this->context->smarty->clearAllAssign();

            $this->assignSmartyAlertClasses();
            $this->context->smarty->assign('tip', 'fill_api_keys');

            $extraMessage = $this->module->display($this->module->file, 'getContent.tpl');
        }

        $apiConfigForm = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->module->l('API configuration', 'getContent'),
                    'image' => $iconPath,
                ),
                'input' => array(
                    array(
                        'name' => 'ALMA_API_MODE',
                        'label' => $this->module->l('API Mode', 'getContent'),
                        'type' => 'select',
                        'required' => true,
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        'desc' => $this->module->l('Use Test mode until you are ready to take real orders with Alma. In Test mode, only admins can see Alma on cart/checkout pages.', 'getContent'),
                        'options' => array(
                            'id' => 'api_mode',
                            'name' => 'name',
                            'query' => array(
                                array('api_mode' => ALMA_MODE_LIVE, 'name' => 'Live'),
                                array('api_mode' => ALMA_MODE_TEST, 'name' => 'Test'),
                            ),
                        ),
                    ),

                    array(
                        'name' => 'ALMA_LIVE_API_KEY',
                        'label' => $this->module->l('Live API key', 'getContent'),
                        'type' => 'text',
                        'size' => 75,
                        'required' => false,
                        'desc' => $this->module->l('Not required for Test mode', 'getContent') . ' – ' . sprintf(
                            $this->module->l('You can find your Live API key on %1$syour Alma dashboard%2$s', 'getContent'),
                            '<a href="https://dashboard.getalma.eu/api" target="_blank">',
                            '</a>'
                        ),
                    ),
                    array(
                        'name' => 'ALMA_TEST_API_KEY',
                        'label' => $this->module->l('Test API key', 'getContent'),
                        'type' => 'text',
                        'size' => 75,
                        'required' => false,
                        'desc' => $this->module->l('Not required for Live mode', 'getContent') . ' – ' . sprintf(
                            $this->module->l('You can find your Test API key on %1$syour sandbox dashboard%2$s', 'getContent'),
                            '<a href="https://dashboard.sandbox.getalma.eu/api" target="_blank">',
                            '</a>'
                        ),
                    ),
                ),
                'submit' => array('title' => $this->module->l('Save'), 'class' => 'btn btn-default pull-right'),
            ),
        );

        $pnxConfigForm = null;
        if ($merchant) {
            $pnxConfigForm = array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->module->l('Installments plans', 'getContent'),
                        'image' => $iconPath,
                    ),
                    'input' => array(),
                    'submit' => array('title' => $this->module->l('Save'), 'class' => 'btn btn-default pull-right'),
                ),
            );

            $pnxTabs = array();
            $activeTab = null;

            foreach ($merchant->fee_plans as $feePlan) {
                $n = $feePlan['installments_count'];
                $tabId = "p${n}x";
                $tabTitle = sprintf($this->module->l('%d-installment payments', 'getContent'), $n);

                if (AlmaSettings::isInstallmentPlanEnabled($n)) {
                    $pnxTabs[$tabId] = '✅ ' . $tabTitle;
                    $activeTab = $activeTab ?: $tabId;
                } else {
                    $pnxTabs[$tabId] = '❌ ' . $tabTitle;
                }

                $minAmount = (int)almaPriceFromCents($merchant->minimum_purchase_amount);
                $maxAmount = (int)almaPriceFromCents($merchant->maximum_purchase_amount);

                $tpl = $this->context->smarty->createTemplate(
                    "{$this->module->local_path}views/templates/hook/pnx_fees.tpl"
                );
                $tpl->assign(array('fee_plan' => $feePlan, 'min_amount' => $minAmount, 'max_amount' => $maxAmount));

                $pnxConfigForm['form']['input'][] = array(
                    // Prevent notices for undefined index
                    'name' => null,
                    'label' => null,
                    ///
                    'form_group_class' => "$tabId-content",
                    'type' => 'html',
                    'html_content' => $tpl->fetch(),
                );

                $pnxConfigForm['form']['input'][] = array(
                    'form_group_class' => "$tabId-content",
                    'name' => "ALMA_P${n}X_ENABLED",
                    'label' => sprintf($this->module->l('Enable %d-installment payments', 'getContent'), $n),
                    'type' => 'checkbox',
                    'values' => array(
                        'id' => 'id',
                        'name' => 'label',
                        'query' => array(
                            array(
                                'id' => 'ON',
                                'val' => true,
                                'label' => '',
                            ),
                        ),
                    ),
                );

                $pnxConfigForm['form']['input'][] = array(
                    'form_group_class' => "$tabId-content",
                    'name' => "ALMA_P${n}X_MIN_AMOUNT",
                    'label' => $this->module->l('Minimum amount (€)', 'getContent'),
                    'desc' => $this->module->l('Minimum purchase amount to activate this plan', 'getContent'),
                    'type' => 'number',
                    'min' => $minAmount,
                    'max' => $maxAmount,
                );

                $pnxConfigForm['form']['input'][] = array(
                    'form_group_class' => "$tabId-content",
                    'name' => "ALMA_P${n}X_MAX_AMOUNT",
                    'label' => $this->module->l('Maximum amount (€)', 'getContent'),
                    'desc' => $this->module->l('Maximum purchase amount to activate this plan', 'getContent'),
                    'type' => 'number',
                    'min' => $minAmount,
                    'max' => $maxAmount,
                );
            }

            $tpl = $this->context->smarty->createTemplate(
                "{$this->module->local_path}views/templates/hook/pnx_tabs.tpl"
            );
            $forceTabs = false;
            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                $forceTabs = true;
            }
            $tpl->assign(array('tabs' => $pnxTabs, 'active' => $activeTab, 'forceTabs' => $forceTabs));

            array_unshift(
                $pnxConfigForm['form']['input'],
                array(
                    'name' => null,
                    'label' => null,
                    'type' => 'html',
                    'html_content' => $tpl->fetch(),
                )
            );
        }

        $paymentButtonForm = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->module->l('Payment method configuration', 'getContent'),
                    'image' => $iconPath,
                ),
                'input' => array(
                    array(
                        'name' => null,
                        'label' => null,
                        'type' => 'html',
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        'html_content' => $this->module->l('Use "%d" in the fields below where you want the installments count to appear. For instance, "Pay in %d monthly installments" will appear as "Pay in 3 monthly installments"', 'getContent'),
                    ),

                    array(
                        'name' => 'ALMA_PAYMENT_BUTTON_TITLE',
                        'label' => $this->module->l('Title', 'getContent'),
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        'desc' => $this->module->l('This controls the payment method name which the user sees during checkout.', 'getContent'),
                        'type' => 'text',
                        'size' => 75,
                        'required' => true,
                    ),
                    array(
                        'name' => 'ALMA_PAYMENT_BUTTON_DESC',
                        'label' => $this->module->l('Description', 'getContent'),
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        'desc' => $this->module->l('This controls the payment method description which the user sees during checkout.', 'getContent'),
                        'type' => 'text',
                        'size' => 75,
                        'required' => version_compare(_PS_VERSION_, '1.7', '<'),
                    ),
                ),
                'submit' => array('title' => $this->module->l('Save'), 'class' => 'btn btn-default pull-right'),
            ),
        );

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $paymentButtonForm['form']['input'][] = array(
                'name' => 'ALMA_SHOW_DISABLED_BUTTON',
                'type' => 'radio',
                'label' => $this->module->l('When Alma is not available...', 'getContent'),
                'class' => 't',
                'required' => true,
                'values' => array(
                    array(
                        'id' => 'ON',
                        'value' => true,
                        'label' => $this->module->l('Display payment button, disabled', 'getContent'),
                    ),
                    array(
                        'id' => 'OFF',
                        'value' => false,
                        'label' => $this->module->l('Hide payment button', 'getContent'),
                    ),
                ),
            );
        }

        $cartEligibilityForm = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->module->l('Cart eligibility message', 'getContent'),
                    'image' => $iconPath,
                ),
                'input' => array(
                    array(
                        'name' => 'ALMA_SHOW_ELIGIBILITY_MESSAGE',
                        'label' => $this->module->l('Show eligibility message', 'getContent'),
                        'type' => 'checkbox',
                        'values' => array(
                            'id' => 'id',
                            'name' => 'label',
                            'query' => array(
                                array(
                                    'id' => 'ON',
                                    'val' => true,
                                    // PrestaShop won't detect the string if the call to `l` is multiline
                                    'label' => $this->module->l('Display a message under the cart summary to indicate its eligibility for monthly installments.', 'getContent'),
                                ),
                            ),
                        ),
                    ),
                    array(
                        'name' => 'ALMA_IS_ELIGIBLE_MESSAGE',
                        'label' => $this->module->l('Eligibility message', 'getContent'),
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        'desc' => $this->module->l('Message displayed below the cart totals when it is eligible for monthly installments.', 'getContent'),
                        'type' => 'text',
                        'size' => 75,
                        'required' => true,
                    ),
                    array(
                        'name' => 'ALMA_NOT_ELIGIBLE_MESSAGE',
                        'label' => $this->module->l('Non-eligibility message', 'getContent'),
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        'desc' => $this->module->l('Message displayed below the cart totals when it is not eligible for monthly installments.', 'getContent'),
                        'type' => 'text',
                        'size' => 75,
                        'required' => true,
                    ),
                ),
                'submit' => array('title' => $this->module->l('Save'), 'class' => 'btn btn-default pull-right'),
            ),
        );

        $orderConfirmationForm = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->module->l('Order confirmation', 'getContent'),
                    'image' => $iconPath,
                ),
                'input' => array(
                    array(
                        'name' => 'ALMA_DISPLAY_ORDER_CONFIRMATION',
                        'label' => $this->module->l('Display order confirmation', 'getContent'),
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        'desc' => $this->module->l('Activate this setting when you do not have your own order confirmation page', 'getContent'),
                        'type' => 'checkbox',
                        'values' => array(
                            'id' => 'id',
                            'name' => 'label',
                            'query' => array(
                                array(
                                    'id' => 'ON',
                                    'val' => true,
                                    // PrestaShop won't detect the string if the call to `l` is multiline
                                    'label' => $this->module->l('Confirm successful order to customers when they come back from the Alma payment page', 'getContent'),
                                ),
                            ),
                        ),
                    ),
                ),
                'submit' => array('title' => $this->module->l('Save'), 'class' => 'btn btn-default pull-right'),
            ),
        );

        $refundStateForm = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->module->l('Refund with state change', 'getContent'),
                    'image' => $iconPath,
                ),
                'input' => array(
                    array(
                        'name' => null,
                        'label' => null,
                        'type' => 'html',
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        'html_content' => $this->module->l('If you usually refund orders by changing their state, activate this option and choose the state you want to use to trigger refunds on Alma payments', 'getContent'),
                    ),
                    array(
                        'name' => 'ALMA_STATE_REFUND_ENABLED',
                        'label' => $this->module->l('Activate refund by change state', 'getContent'),
                        'type' => 'checkbox',
                        'values' => array(
                            'id' => 'id',
                            'name' => 'label',
                            'query' => array(
                                array(
                                    'id' => 'ON',
                                    'val' => true,
                                    'label' => '',
                                ),
                            ),
                        ),
                    ),
                    array(
                        'name' => 'ALMA_STATE_REFUND',
                        'label' => $this->module->l('Refund state order', 'getContent'),
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        'desc' => $this->module->l('Your order state to sync refund with Alma', 'getContent'),
                        'type' => 'select',
                        'required' => true,
                        'options' => array(
                            'query' => OrderState::getOrderStates($this->context->cookie->id_lang),
                            'id' => 'id_order_state',
                            'name' => 'name',
                        ),
                    ),
                ),
                'submit' => array('title' => $this->module->l('Save'), 'class' => 'btn btn-default pull-right'),
            ),
        );

        // $shareForm = array(
        //     'form' => array(
        //         'legend' => array(
        //             'title' => $this->module->l('Share of checkout', 'getContent'),
        //             'image' => $iconPath,
        //         ),
        //         'input' => array(
        //             array(
        //                 'name' => null,
        //                 'label' => null,
        //                 'type' => 'html',
        //                 'html_content' => $this->module->l('If you want to share data from your shop checkout to build better Alma product'),
        //             ),
        //             array(
        //                 'name' => 'ALMA_SHARE_OF_CHECKOUT',
        //                 'label' => $this->module->l('Enable', 'getContent'),
        //                 'type' => 'checkbox',
        //                 'values' => array(
        //                     'id' => 'id',
        //                     'name' => 'label',
        //                     'query' => array(
        //                         array(
        //                             'id' => 'ON',
        //                             'val' => true,
        //                             'label' => '',
        //                         ),
        //                     ),
        //                 ),
        //             ),
        //         ),
        //         'submit' => array('title' => $this->module->l('Save'), 'class' => 'btn btn-default pull-right'),
        //     ),
        // );

        $debugForm = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->module->l('Debug options', 'getContent'),
                    'image' => $iconPath,
                ),
                'input' => array(
                    array(
                        'name' => 'ALMA_ACTIVATE_LOGGING',
                        'label' => $this->module->l('Activate logging', 'getContent'),
                        'type' => 'checkbox',
                        'values' => array(
                            'id' => 'id',
                            'name' => 'label',
                            'query' => array(
                                array(
                                    'id' => 'ON',
                                    'val' => true,
                                    'label' => '',
                                ),
                            ),
                        ),
                    ),
                ),
                'submit' => array('title' => $this->module->l('Save'), 'class' => 'btn btn-default pull-right'),
            ),
        );

        if ($needsKeys) {
            $apiConfigForm['form']['input'][] = array(
                'name' => '_api_only',
                'label' => null,
                'type' => 'hidden',
            );
            $fieldsForms = array($apiConfigForm, $debugForm);
        } else {
            $fieldsForms = array($cartEligibilityForm, $paymentButtonForm, $refundStateForm);

            if ($pnxConfigForm) {
                $fieldsForms[] = $pnxConfigForm;
            }

            $fieldsForms = array_merge($fieldsForms, array($orderConfirmationForm, $apiConfigForm, $debugForm));
        }

        $helper = new HelperForm();
        $helper->module = $this->module;
        $helper->table = 'alma_config';
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int)Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
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

        $helper->fields_value = array(
            'ALMA_LIVE_API_KEY' => AlmaSettings::getLiveKey(),
            'ALMA_TEST_API_KEY' => AlmaSettings::getTestKey(),
            'ALMA_API_MODE' => AlmaSettings::getActiveMode(),
            'ALMA_PAYMENT_BUTTON_TITLE' => AlmaSettings::getPaymentButtonTitle(),
            'ALMA_PAYMENT_BUTTON_DESC' => AlmaSettings::getPaymentButtonDescription(),
            'ALMA_SHOW_DISABLED_BUTTON' => AlmaSettings::showDisabledButton(),
            'ALMA_SHOW_ELIGIBILITY_MESSAGE_ON' => AlmaSettings::showEligibilityMessage(),
            'ALMA_IS_ELIGIBLE_MESSAGE' => AlmaSettings::getEligibilityMessage(),
            'ALMA_NOT_ELIGIBLE_MESSAGE' => AlmaSettings::getNonEligibilityMessage(),
            'ALMA_DISPLAY_ORDER_CONFIRMATION_ON' => AlmaSettings::displayOrderConfirmation(),
            'ALMA_SHARE_OF_CHECKOUT_ON' => AlmaSettings::isShareOfCheckout(),
            'ALMA_ACTIVATE_LOGGING_ON' => (bool)AlmaSettings::canLog(),
            'ALMA_STATE_REFUND' => AlmaSettings::getRefundState(),
            'ALMA_STATE_REFUND_ENABLED_ON' => AlmaSettings::isRefundEnabledByState(),
            '_api_only' => true,
        );

        if ($merchant) {
            foreach ($merchant->fee_plans as $feePlan) {
                $n = $feePlan['installments_count'];
                $helper->fields_value["ALMA_P${n}X_ENABLED_ON"] = AlmaSettings::isInstallmentPlanEnabled($n);

                $minAmount = (int)almaPriceFromCents(AlmaSettings::installmentPlanMinAmount($n, $merchant));
                $helper->fields_value["ALMA_P${n}X_MIN_AMOUNT"] = $minAmount;

                $maxAmount = (int)almaPriceFromCents(AlmaSettings::installmentPlanMaxAmount($n));
                $helper->fields_value["ALMA_P${n}X_MAX_AMOUNT"] = $maxAmount;
            }
        }

        $helper->languages = $this->context->controller->getLanguages();

        return $extraMessage . $helper->generateForm($fieldsForms);
    }

    private function assignSmartyAlertClasses($level = 'danger')
    {
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->context->smarty->assign(array(
                'validation_error_classes' => 'alert',
                'tip_classes' => 'conf',
                'success_classes' => 'conf',
            ));
        } else {
            $this->context->smarty->assign(array(
                'validation_error_classes' => "alert alert-$level",
                'tip_classes' => 'alert alert-info',
                'success_classes' => 'alert alert-success',
            ));
        }
    }

    public function needsAPIKey()
    {
        $key = trim(AlmaSettings::getActiveAPIKey());
        return $key == "" || $key == null;
    }

    public function run($params)
    {
        $messages = '';
        $this->assignSmartyAlertClasses();

        $merchant = $this->getMerchant();

        if (Tools::isSubmit('alma_config_form')) {
            $messages = $this->processConfiguration($merchant);
        } elseif (!$this->needsAPIKey()) {
            $messages = $this->credentialsError(
                AlmaSettings::getActiveMode(),
                AlmaSettings::getLiveKey(),
                AlmaSettings::getTestKey()
            );

            if ($messages) {
                $messages = $messages['message'];
            }
        }

        // Re-get merchant, in case API keys were set/fixed above
        if (!$merchant) {
            $merchant = $this->getMerchant();
        }

        $htmlForm = $this->renderForm($merchant);

        return $messages . $htmlForm;
    }
}
