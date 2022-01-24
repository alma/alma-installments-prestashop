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
 * Class CartEligibilityAdminFormBuilder
 */
class CartEligibilityAdminFormBuilder extends AbstractAlmaAdminFormBuilder
{
    const ALMA_SHOW_ELIGIBILITY_MESSAGE = 'ALMA_SHOW_ELIGIBILITY_MESSAGE';
    const ALMA_CART_WDGT_NOT_ELGBL = 'ALMA_CART_WDGT_NOT_ELGBL';
    const ALMA_CART_WIDGET_POSITION_CUSTOM = 'ALMA_CART_WIDGET_POSITION_CUSTOM';
    const ALMA_CART_WDGT_POS_SELECTOR = 'ALMA_CART_WDGT_POS_SELECTOR';

    protected function configForm()
    {
        $htmlContent = $this->module->l('This badge allows you to inform your customers of the availability of Alma\'s payment facilities right from the product page, which will help increase your conversion rate. For more details on its configuration or in case of problems, please consult', 'GetContentHookController');
        $htmlContent .= ' <a href="https://docs.getalma.eu/docs/prestashop-alma-widget">';
        $htmlContent .= $this->module->l('this documentation.', 'GetContentHookController');
        $htmlContent .= '</a>';

        return [
            $this->inputHtml(null, $htmlContent),
            $this->inputAlmaSwitchForm(
                self::ALMA_SHOW_ELIGIBILITY_MESSAGE,
                $this->module->l('Display badge', 'GetContentHookController')
            ),
            $this->inputAlmaSwitchForm(
                self::ALMA_CART_WDGT_NOT_ELGBL,
                $this->module->l('Display even if the cart is not eligible', 'GetContentHookController')
            ),
            $this->inputRadioForm(
                self::ALMA_CART_WIDGET_POSITION_CUSTOM,
                $this->module->l('Badge position', 'GetContentHookController'),
                $this->module->l('Display badge after cart (by default)', 'GetContentHookController'),
                $this->module->l('Display badge on custom css selector', 'GetContentHookController')
            ),
            $this->inputTextForm(
                self::ALMA_CART_WDGT_POS_SELECTOR,
                $this->module->l('Display badge on custom css selector', 'GetContentHookController'),
                $this->module->l(
                    '%1$sAdvanced%2$s [Optional] Query selector for our scripts to display the badge on cart page',
                    'GetContentHookController'
                ),
                $this->module->l('E.g. #id, .class, ...', 'GetContentHookController')
            ),
        ];
    }

    protected function getTitle()
    {
        return $this->module->l('Display the badge in the cart page', 'GetContentHookController');
    }
}
