<?php
/**
 * 2018-2023 Alma SAS
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

namespace Alma\PrestaShop\Forms;

use Alma\PrestaShop\Utils\SettingsCustomFields;
use OrderState;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class PaymentOnTriggeringAdminFormBuilder
 */
class PaymentOnTriggeringAdminFormBuilder extends AbstractAlmaAdminFormBuilder
{
    const ALMA_PAYMENT_ON_TRIGGERING_ENABLED = 'ALMA_PAYMENT_ON_TRIGGERING_ENABLED';
    const ALMA_STATE_TRIGGER = 'ALMA_STATE_TRIGGER';
    const ALMA_DESCRIPTION_TRIGGER = 'ALMA_DESCRIPTION_TRIGGER';
    const ALMA_DESCRIPTION_TRIGGER_AT_SHIPPING = 'at_shipping';

    protected function configForm()
    {
        $htmlContent = $this->module->l('This option is available only for Alma payment in 2x, 3x and 4x. When it\'s turned on, your clients will pay the first installment at the order status change. When your client order on your website, Alma will only ask for a payment authorization. Only status handled by Alma are available in the menu below. Please contact Alma if you need us to add another status.', 'PaymentOnTriggeringAdminFormBuilder');

        $query[] = [
            'description_trigger' => self::ALMA_DESCRIPTION_TRIGGER_AT_SHIPPING,
            'name' => SettingsCustomFields::getDescriptionPaymentTriggerByLang($this->context->language->id),
        ];

        return [
            $this->inputHtml(null, $htmlContent),
            $this->inputAlmaSwitchForm(
                self::ALMA_PAYMENT_ON_TRIGGERING_ENABLED,
                $this->module->l('Activate the payment upon trigger', 'PaymentOnTriggeringAdminFormBuilder'),
                null,
                null,
                null,
                true
            ),
            $this->inputSelectForm(
                self::ALMA_DESCRIPTION_TRIGGER,
                $this->module->l('Trigger typology', 'PaymentOnTriggeringAdminFormBuilder'),
                $this->module->l('Text that will appear in the payments schedule and in the customer\'s payment authorization email.', 'PaymentOnTriggeringAdminFormBuilder'),
                $query,
                'description_trigger'
            ),
            $this->inputSelectForm(
                self::ALMA_STATE_TRIGGER,
                $this->module->l('Order status that triggers the first payment', 'PaymentOnTriggeringAdminFormBuilder'),
                '',
                OrderState::getOrderStates($this->context->cookie->id_lang),
                'id_order_state'
            ),
        ];
    }

    protected function getTitle()
    {
        return $this->module->l('Payment upon trigger', 'PaymentOnTriggeringAdminFormBuilder');
    }
}
