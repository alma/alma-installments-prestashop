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

namespace Alma\PrestaShop\Utils;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class PaymentButtonAdminFormBuilder
 *
 * @package Alma\PrestaShop\Utils
 */
class PaymentButtonAdminFormBuilder extends AbstractAlmaAdminFormBuilder
{
    protected function configForm() {
        $return = [
            $this->inputHtml(null, "<h4>{$this->module->l('Payment by installment', 'GetContentHookController')}</h4>"),
            $this->inputTextForm(
                'ALMA_PAYMENT_BUTTON_TITLE',
                $this->module->l('Title', 'GetContentHookController'),
                $this->module->l('This controls the payment method name which the user sees during checkout.', 'GetContentHookController'),
                null,
                true,
                true
            ),
            $this->inputTextForm(
                'ALMA_PAYMENT_BUTTON_DESC',
                $this->module->l('Description', 'GetContentHookController'),
                $this->module->l('This controls the payment method description which the user sees during checkout.', 'GetContentHookController'),
                null,
                true,
                true
            ),
            $this->inputHtml(null, "<h4>{$this->module->l('Defered payment', 'GetContentHookController')}</h4>"),
            $this->inputTextForm(
                'ALMA_DEFERRED_BUTTON_TITLE',
                $this->module->l('Title', 'GetContentHookController'),
                $this->module->l('This controls the payment method name which the user sees during checkout.', 'GetContentHookController'),
                null,
                true,
                true
            ),
            $this->inputTextForm(
                'ALMA_DEFERRED_BUTTON_DESC',
                $this->module->l('Description', 'GetContentHookController'),
                $this->module->l('This controls the payment method description which the user sees during checkout.', 'GetContentHookController'),
                null,
                true,
                true
            ),
        ];

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $return[] = $this->inputRadioForm(
                'ALMA_SHOW_DISABLED_BUTTON',
                $this->module->l('When Alma is not available...', 'GetContentHookController'),
                $this->module->l('Hide payment button', 'GetContentHookController'),
                $this->module->l('Display payment button, disabled', 'GetContentHookController')
            );
        }

        return $return;
    }

    protected function getTitle()
    {
        return $this->module->l('Payment method configuration', 'GetContentHookController');
    }
}