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
 */
class PaymentButtonAdminFormBuilder extends AbstractAlmaAdminFormBuilder
{
    const ALMA_PAYMENT_BUTTON_TITLE = 'ALMA_PAYMENT_BUTTON_TITLE';
    const ALMA_PAYMENT_BUTTON_DESC = 'ALMA_PAYMENT_BUTTON_DESC';
    const ALMA_DEFERRED_BUTTON_TITLE = 'ALMA_DEFERRED_BUTTON_TITLE';
    const ALMA_DEFERRED_BUTTON_DESC = 'ALMA_DEFERRED_BUTTON_DESC';
    const ALMA_SHOW_DISABLED_BUTTON = 'ALMA_SHOW_DISABLED_BUTTON';

    protected function configForm()
    {
        $imgPaymentButton = '/modules/alma/views/img/payment-button-1.7.png';
        $tplPaymentButton = 'sample_payment_button.tpl';
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $imgPaymentButton = '/modules/alma/views/img/payment-button-1.6.png';
        }
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $tplPaymentButton = '15/sample_payment_button.tpl';
        }
        // phpcs:ignore
        $htmlContent = $this->module->l('Edit the text displayed when choosing the payment method in your checkout. It will adapt to the languages of your site.', 'PaymentButtonAdminFormBuilder');
        $tpl = $this->context->smarty->createTemplate(
            "{$this->module->local_path}views/templates/hook/{$tplPaymentButton}"
        );
        $tpl->assign([
            'imgPaymentButton' => $imgPaymentButton,
        ]);

        $return = [
            $this->inputHtml(null, $htmlContent),
            $this->inputHtml($tpl),
            // phpcs:ignore
            $this->inputHtml(null, "<h2>{$this->module->l('Payment by installment', 'PaymentButtonAdminFormBuilder')}</h2>"),
            $this->inputTextForm(
                self::ALMA_PAYMENT_BUTTON_TITLE,
                $this->module->l('Title', 'PaymentButtonAdminFormBuilder'),
                null,
                null,
                true,
                true
            ),
            $this->inputTextForm(
                self::ALMA_PAYMENT_BUTTON_DESC,
                $this->module->l('Description', 'PaymentButtonAdminFormBuilder'),
                null,
                null,
                true,
                true
            ),
            $this->inputHtml(null, "<h2>{$this->module->l('Defered payment', 'PaymentButtonAdminFormBuilder')}</h2>"),
            $this->inputTextForm(
                self::ALMA_DEFERRED_BUTTON_TITLE,
                $this->module->l('Title', 'PaymentButtonAdminFormBuilder'),
                null,
                null,
                true,
                true
            ),
            $this->inputTextForm(
                self::ALMA_DEFERRED_BUTTON_DESC,
                $this->module->l('Description', 'PaymentButtonAdminFormBuilder'),
                null,
                null,
                true,
                true
            ),
        ];

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $return[] = $this->inputRadioForm(
                self::ALMA_SHOW_DISABLED_BUTTON,
                $this->module->l('When Alma is not available...', 'PaymentButtonAdminFormBuilder'),
                $this->module->l('Hide payment button', 'PaymentButtonAdminFormBuilder'),
                $this->module->l('Display payment button, disabled', 'PaymentButtonAdminFormBuilder')
            );
        }

        return $return;
    }

    protected function getTitle()
    {
        return $this->module->l('Payment method configuration', 'PaymentButtonAdminFormBuilder');
    }
}
