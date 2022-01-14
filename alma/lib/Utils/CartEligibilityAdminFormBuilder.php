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
 *
 * @package Alma\PrestaShop\Utils
 */
class CartEligibilityAdminFormBuilder extends AbstractAlmaAdminFormBuilder
{
    const ALMA_SHOW_ELIGIBILITY_MESSAGE = 'ALMA_SHOW_ELIGIBILITY_MESSAGE';

    protected function configForm()
    {
        return [
            $this->inputAlmaSwitchForm(
                self::ALMA_SHOW_ELIGIBILITY_MESSAGE,
                $this->module->l('Show cart eligibility', 'GetContentHookController'),
                $this->module->l(
                    'Displays a badge with eligible Alma plans with installments details',
                    'GetContentHookController'
                ),
                $this->module->l('Display the cart\'s eligibility.', 'GetContentHookController')
            ),
            $this->inputAlmaSwitchForm(
                'ALMA_CART_WDGT_NOT_ELGBL',
                $this->module->l('Display badge', 'GetContentHookController'),
                $this->module->l(
                    'Displays a badge when cart amount is too high or tow low',
                    'GetContentHookController'
                ),
                $this->module->l('Display badge when the cart is not eligible.', 'GetContentHookController')
            ),
            $this->inputRadioForm(
                'ALMA_CART_WIDGET_POSITION_CUSTOM',
                $this->module->l('Badge position', 'GetContentHookController'),
                $this->module->l('Display badge after cart (by default)', 'GetContentHookController'),
                $this->module->l('Display badge on custom css selector', 'GetContentHookController')
            ),
            $this->inputTextForm(
                'ALMA_CART_WDGT_POS_SELECTOR',
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
        return $this->module->l('Cart eligibility message', 'GetContentHookController');
    }
}
