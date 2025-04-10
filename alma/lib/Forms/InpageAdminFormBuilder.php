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

namespace Alma\PrestaShop\Forms;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class InpageAdminFormBuilder
 */
class InpageAdminFormBuilder extends AbstractAlmaAdminFormBuilder
{
    const ALMA_INPAGE_DEFAULT_VALUE = true;
    const ALMA_ACTIVATE_INPAGE = 'ALMA_ACTIVATE_INPAGE';
    const ALMA_INPAGE_PAYMENT_BUTTON_SELECTOR = 'ALMA_INPAGE_PAYMENT_BUTTON_SELECTOR';
    const ALMA_INPAGE_DEFAULT_VALUE_PAYMENT_BUTTON_SELECTOR = '[data-module-name=alma]';
    const ALMA_INPAGE_PLACE_ORDER_BUTTON_SELECTOR = 'ALMA_INPAGE_PLACE_ORDER_BUTTON_SELECTOR';
    const ALMA_INPAGE_DEFAULT_VALUE_PLACE_ORDER_BUTTON_SELECTOR = '#payment-confirmation button';

    /**
     * @return array
     */
    protected function configForm()
    {
        return [
            $this->inputAlmaSwitchForm(
                self::ALMA_ACTIVATE_INPAGE,
                $this->module->l('Activate in-page checkout', 'InpageAdminFormBuilder'),
                sprintf(
                    $this->module->l('Let your customers pay with Alma in a secure pop-up, without leaving your site. %1$sLearn more.%2$s', 'InpageAdminFormBuilder'),
                    '<a href="https://docs.almapay.com/docs/in-page-prestashop" target="_blank">',
                    '</a>'
                )
            ),
            $this->inputTextForm(
                self::ALMA_INPAGE_PAYMENT_BUTTON_SELECTOR,
                $this->module->l('Input payment button Alma selector', 'InpageAdminFormBuilder'),
                sprintf(
                    $this->module->l('%1$sAdvanced%2$s [Optional] CSS selector used by our scripts to identify the Alma payment button', 'InpageAdminFormBuilder'),
                    '<b>',
                    '</b>'
                ),
                $this->module->l('E.g. #id, .class, ...', 'InpageAdminFormBuilder')
            ),
            $this->inputTextForm(
                self::ALMA_INPAGE_PLACE_ORDER_BUTTON_SELECTOR,
                $this->module->l('Place order button selector', 'InpageAdminFormBuilder'),
                sprintf(
                    $this->module->l('%1$sAdvanced%2$s [Optional] CSS selector used by our scripts to identify the payment confirmation button', 'InpageAdminFormBuilder'),
                    '<b>',
                    '</b>'
                ),
                $this->module->l('E.g. #id, .class, ...', 'InpageAdminFormBuilder')
            ),
        ];
    }

    /**
     * @return string
     */
    protected function getTitle()
    {
        return $this->module->l('In-page checkout', 'InpageAdminFormBuilder');
    }
}
