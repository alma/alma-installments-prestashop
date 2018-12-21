<?php
/**
 * 2018 Alma / Nabla SAS
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
 * @author    Alma / Nabla SAS <contact@getalma.eu>
 * @copyright 2018 Alma / Nabla SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 *
 */

use Alma\API\RequestError;

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(_PS_MODULE_DIR_ . 'alma/includes/AlmaAdminHookController.php');
include_once(_PS_MODULE_DIR_ . 'alma/includes/AlmaSettings.php');
include_once(_PS_MODULE_DIR_ . 'alma/includes/AlmaClient.php');
include_once(_PS_MODULE_DIR_ . 'alma/includes/AlmaLogger.php');

class AlmaGetContentController extends AlmaAdminHookController
{
    public function processConfiguration()
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

            $activateLogging = Tools::getValue('ALMA_ACTIVATE_LOGGING_ON') == '1';
            AlmaSettings::updateValue('ALMA_ACTIVATE_LOGGING', $activateLogging);
        }

        $apiMode = Tools::getValue('ALMA_API_MODE');
        AlmaSettings::updateValue('ALMA_API_MODE', $apiMode);

        // Get & check provided API keys
        $liveKey = trim(Tools::getValue('ALMA_LIVE_API_KEY'));
        $testKey = trim(Tools::getValue('ALMA_TEST_API_KEY'));

        if (empty($liveKey) || empty($testKey)) {
            $this->context->smarty->assign('validation_error', 'missing_required_setting');
            return $this->module->display($this->module->file, 'getContent.tpl');
        }

        $credentialsError = $this->credentialsError($apiMode, $liveKey, $testKey);
        if ($credentialsError) {
            return $credentialsError;
        }

        AlmaSettings::updateValue('ALMA_LIVE_API_KEY', $liveKey);
        AlmaSettings::updateValue('ALMA_TEST_API_KEY', $testKey);

        // Everything has been properly validated: we're fully configured
        AlmaSettings::updateValue('ALMA_FULLY_CONFIGURED', '1');

        $this->context->smarty->clearAssign('validation_error');
        return $this->module->display($this->module->file, 'getContent.tpl');
    }

    private function credentialsError($apiMode, $liveKey, $testKey)
    {
        $modes = array(ALMA_MODE_TEST, ALMA_MODE_LIVE);

        foreach ($modes as $mode) {
            $alma = AlmaClient::createInstance($mode == ALMA_MODE_LIVE ? $liveKey : $testKey);
            if (!$alma) {
                $this->context->smarty->assign('validation_error', 'alma_client_null');
                return $this->module->display($this->module->file, 'getContent.tpl');
            }

            try {
                $merchant = $alma->merchants->me();
            } catch (RequestError $e) {
                if ($e->response && $e->response->responseCode === 401) {
                    $this->context->smarty->assign('validation_error', "{$mode}_authentication_error");
                    return $this->module->display($this->module->file, 'getContent.tpl');
                } else {
                    AlmaLogger::instance()->error('Error while fetching merchant status: ' . $e->getMessage());

                    $this->context->smarty->assign('validation_error', 'api_request_error');
                    $this->context->smarty->assign('error', $e->getMessage());

                    return $this->module->display($this->module->file, 'getContent.tpl');
                }
            }

            if (!$merchant->can_create_payments) {
                $this->context->smarty->assign(
                    array(
                        'validation_error' => "inactive_{$mode}_account",
                        'level' => $apiMode == $mode ? 'danger' : 'warning',
                    )
                );
                return $this->module->display($this->module->file, 'getContent.tpl');
            }
        }

        return null;
    }

    public function renderForm()
    {
        $needs_key = AlmaSettings::needsAPIKeys();
        $iconPath = Media::getMediaPath(_PS_MODULE_DIR_ . $this->module->name . '/views/img/logo16x16.png');

        $extra_msg = null;
        if ($needs_key && !Tools::isSubmit('alma_config_form')) {
            $this->context->smarty->clearAllAssign();
            $this->context->smarty->assign('tip', 'fill_api_keys');
            $extra_msg = $this->module->display($this->module->file, 'getContent.tpl');
        }

        $api_config_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->module->l('API configuration', 'getContent'),
                    'image' => $iconPath,
                ),
                'input' => array(
                    array(
                        'name' => 'ALMA_LIVE_API_KEY',
                        'label' => $this->module->l('Live API key', 'getContent'),
                        'type' => 'text',
                        'required' => true
                    ),
                    array(
                        'name' => 'ALMA_TEST_API_KEY',
                        'label' => $this->module->l('Test API key', 'getContent'),
                        'type' => 'text',
                        'required' => true,
                        'desc' => sprintf(
                            $this->module->l('You can find your API keys on %1$syour Alma dashboard%2$s', 'getContent'),
                            '<a href="https://dashboard.getalma.eu/security" target="_blank">',
                            '</a>'
                        ),
                    ),
                    array(
                        'name' => 'ALMA_API_MODE',
                        'label' => $this->module->l('API Mode', 'getContent'),
                        'type' => 'select',
                        'required' => true,
                        'desc' => $this->module->l(
                            'Use Test mode until you are ready to take real orders with Alma. ' .
                            'In Test mode, only admins can see Alma on cart/checkout pages.',
                            'getContent'
                        ),
                        'options' => array(
                            'id' => 'api_mode',
                            'name' => 'name',
                            'query' => array(
                                array('api_mode' => ALMA_MODE_LIVE, 'name' => 'Live'),
                                array('api_mode' => ALMA_MODE_TEST, 'name' => 'Test'),
                            ),
                        ),
                    ),
                ),
                'submit' => array('title' => $this->module->l('Save'), 'class' => 'btn btn-default pull-right'),
            ),
        );

        $payment_button_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->module->l('Payment method configuration', 'getContent'),
                    'image' => $iconPath,
                ),
                'input' => array(
                    array(
                        'name' => 'ALMA_PAYMENT_BUTTON_TITLE',
                        'label' => $this->module->l('Title', 'getContent'),
                        'desc' => $this->module->l(
                            'This controls the payment method name which the user sees during checkout.',
                            'getContent'
                        ),
                        'type' => 'text',
                        'required' => true,
                    ),
                    array(
                        'name' => 'ALMA_PAYMENT_BUTTON_DESC',
                        'label' => $this->module->l('Description', 'getContent'),
                        'desc' => $this->module->l(
                            'This controls the payment method description which the user sees during checkout.',
                            'getContent'
                        ),
                        'type' => 'text',
                        'required' => true,
                    ),
                    array(
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
                    ),
                ),
                'submit' => array('title' => $this->module->l('Save'), 'class' => 'btn btn-default pull-right'),
            ),
        );

        $cart_eligibility_form = array(
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
                                    'label' => $this->module->l(
                                        'Display a message under the cart summary to indicate its ' .
                                        'eligibility for monthly payments.',
                                        'getContent'
                                    ),
                                )
                            ),
                        ),
                    ),
                    array(
                        'name' => 'ALMA_IS_ELIGIBLE_MESSAGE',
                        'label' => $this->module->l('Eligibility message', 'getContent'),
                        'desc' => $this->module->l(
                            'Message displayed below the cart totals when it is eligible for monthly payments.',
                            'getContent'
                        ),
                        'type' => 'text',
                        'required' => true,
                    ),
                    array(
                        'name' => 'ALMA_NOT_ELIGIBLE_MESSAGE',
                        'label' => $this->module->l('Non-eligibility message', 'getContent'),
                        'desc' => $this->module->l(
                            'Message displayed below the cart totals when it is not eligible' .
                            ' for monthly payments.',
                            'getContent'
                        ),
                        'type' => 'text',
                        'required' => true,
                    ),
                ),
                'submit' => array('title' => $this->module->l('Save'), 'class' => 'btn btn-default pull-right'),
            ),
        );

        $debug_form = array(
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
                                )
                            ),
                        ),
                    ),
                ),
                'submit' => array('title' => $this->module->l('Save'), 'class' => 'btn btn-default pull-right'),
            ),
        );

        if ($needs_key) {
            $api_config_form['form']['input'][] = array(
                'name' => '_api_only',
                'type' => 'hidden',
            );
            $fields_forms = array($api_config_form, $debug_form);
        } else {
            $fields_forms = array($cart_eligibility_form, $payment_button_form, $api_config_form, $debug_form);
        }

        $helper = new HelperForm();
        $helper->table = 'alma_config';
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int)Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->submit_action = 'alma_config_form';

        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->module->name .
            '&tab_module=' . $this->module->tab .
            '&module_name=' . $this->module->name;

        $helper->token = Tools::getAdminTokenLite('AdminModules');


        $defaultButtonTitle = $this->module->l('Monthly Payments with Alma', 'getContent');
        $defaultButtonDescription = $this->module->l('Pay in 3 monthly payments with your credit card.', 'getContent');
        $defaultEligibilityMsg = $this->module->l('Your cart is eligible for monthly payments.', 'getContent');
        $defaultNoneligibilityMsg = $this->module->l('Your cart is not eligible for monthly payments.', 'getContent');
        $helper->tpl_vars = array(
            'fields_value' => array(
                'ALMA_LIVE_API_KEY' => AlmaSettings::getLiveKey(),
                'ALMA_TEST_API_KEY' => AlmaSettings::getTestKey(),
                'ALMA_API_MODE' => AlmaSettings::getActiveMode(),
                'ALMA_PAYMENT_BUTTON_TITLE' => AlmaSettings::getPaymentButtonTitle($defaultButtonTitle),
                'ALMA_PAYMENT_BUTTON_DESC' => AlmaSettings::getPaymentButtonDescription($defaultButtonDescription),
                'ALMA_SHOW_DISABLED_BUTTON' => AlmaSettings::showDisabledButton(),
                'ALMA_SHOW_ELIGIBILITY_MESSAGE_ON' => AlmaSettings::showEligibilityMessage(),
                'ALMA_IS_ELIGIBLE_MESSAGE' => AlmaSettings::getEligibilityMessage($defaultEligibilityMsg),
                'ALMA_NOT_ELIGIBLE_MESSAGE' => AlmaSettings::getNonEligibilityMessage($defaultNoneligibilityMsg),
                'ALMA_ACTIVATE_LOGGING_ON' => (bool)AlmaSettings::canLog(),
                '_api_only' => true,
            ),
            'languages' => $this->context->controller->getLanguages()
        );

        return $extra_msg . $helper->generateForm($fields_forms);
    }

    public function run($params)
    {
        if (Tools::isSubmit('alma_config_form')) {
            $messages = $this->processConfiguration();
        } elseif (!AlmaSettings::needsAPIKeys()) {
            $messages = $this->credentialsError(
                AlmaSettings::getActiveMode(),
                AlmaSettings::getLiveKey(),
                AlmaSettings::getTestKey()
            );
        }

        $html_form = $this->renderForm();
        return $messages . $html_form;
    }
}
