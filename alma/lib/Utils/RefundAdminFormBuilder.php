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

use OrderState;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class RefundAdminFormBuilder
 */
class RefundAdminFormBuilder extends AbstractAlmaAdminFormBuilder
{
    const ALMA_STATE_REFUND_ENABLED = 'ALMA_STATE_REFUND_ENABLED';
    const ALMA_STATE_REFUND = 'ALMA_STATE_REFUND';

    protected function configForm()
    {
        $htmlContent = $this->module->l('If you usually refund orders by changing their state, activate this option and choose the state you want to use to trigger refunds on Alma payments', 'RefundAdminFormBuilder');
        $htmlContent2 = sprintf(
            // phpcs:ignore
            $this->module->l('With Alma, you can make your refunds directly from your PrestaShop back-office. Go to your order to find the new Alma section. %1$sMore information on how to use it.%2$s', 'RefundAdminFormBuilder'),
            '<a href="https://docs.getalma.eu/docs/prestashop-refund" target="_blank">',
            '</a>'
        );
        return [
            $this->inputHtml(null, $htmlContent),
            $this->inputHtml(null, $htmlContent2),
            $this->inputAlmaSwitchForm(
                self::ALMA_STATE_REFUND_ENABLED,
                $this->module->l('Activate refund by change state', 'RefundAdminFormBuilder')
            ),
            $this->inputSelectForm(
                self::ALMA_STATE_REFUND,
                $this->module->l('Refund state order', 'RefundAdminFormBuilder'),
                $this->module->l('Your order state to sync refund with Alma', 'RefundAdminFormBuilder'),
                OrderState::getOrderStates($this->context->cookie->id_lang),
                'id_order_state'
            ),
        ];
    }

    protected function getTitle()
    {
        return $this->module->l('Refund with state change', 'RefundAdminFormBuilder');
    }
}
