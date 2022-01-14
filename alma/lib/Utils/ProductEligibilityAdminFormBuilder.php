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
 * Class ProductEligibilityAdminFormBuilder.
 *
 * Builder Form Product Eligibility Alma
 */
class ProductEligibilityAdminFormBuilder extends AbstractAlmaAdminFormBuilder
{
    const ALMA_SHOW_PRODUCT_ELIGIBILITY = 'ALMA_SHOW_PRODUCT_ELIGIBILITY';
    const ALMA_PRODUCT_WDGT_NOT_ELGBL = 'ALMA_PRODUCT_WDGT_NOT_ELGBL';
    const ALMA_WIDGET_POSITION_CUSTOM = 'ALMA_WIDGET_POSITION_CUSTOM';
    const ALMA_WIDGET_POSITION_SELECTOR = 'ALMA_WIDGET_POSITION_SELECTOR';
    const ALMA_PRODUCT_PRICE_SELECTOR = 'ALMA_PRODUCT_PRICE_SELECTOR';
    const ALMA_PRODUCT_ATTR_SELECTOR = 'ALMA_PRODUCT_ATTR_SELECTOR';
    const ALMA_PRODUCT_ATTR_RADIO_SELECTOR = 'ALMA_PRODUCT_ATTR_RADIO_SELECTOR';
    const ALMA_PRODUCT_COLOR_PICK_SELECTOR = 'ALMA_PRODUCT_COLOR_PICK_SELECTOR';
    const ALMA_PRODUCT_QUANTITY_SELECTOR = 'ALMA_PRODUCT_QUANTITY_SELECTOR';

    protected function configForm()
    {
        return [
           $this->inputAlmaSwitchForm(
               self::ALMA_SHOW_PRODUCT_ELIGIBILITY,
               $this->module->l('Show product eligibility on details page', 'GetContentHookController'),
               // phpcs:ignore
               $this->module->l('Displays a badge with eligible Alma plans with installments details', 'GetContentHookController'),
               $this->module->l('Display the product\'s eligibility', 'GetContentHookController')
           ),
           $this->inputAlmaSwitchForm(
               self::ALMA_PRODUCT_WDGT_NOT_ELGBL,
               $this->module->l('Display badge', 'GetContentHookController'),
               // phpcs:ignore
               $this->module->l('Displays a badge when product price is too high or tow low', 'GetContentHookController'),
               $this->module->l('Display badge when the product is not eligible.', 'GetContentHookController')
           ),
           $this->inputRadioForm(
               self::ALMA_WIDGET_POSITION_CUSTOM,
               $this->module->l('Badge position', 'GetContentHookController'),
               $this->module->l('Display badge after price (by default)', 'GetContentHookController'),
               $this->module->l('Display badge on custom css selector', 'GetContentHookController')
           ),
           $this->inputTextForm(
               self::ALMA_WIDGET_POSITION_SELECTOR,
               $this->module->l('Display badge on custom css selector', 'GetContentHookController'),
               sprintf(
                // PrestaShop won't detect the string if the call to `l` is multiline
                // phpcs:ignore
                $this->module->l('%1$sAdvanced%2$s [Optional] Query selector for our scripts to display the badge on product page', 'GetContentHookController'),
                   '<b>',
                   '</b>'
               ),
               $this->module->l('E.g. #id, .class, ...', 'GetContentHookController')
           ),
           $this->inputTextForm(
               self::ALMA_PRODUCT_PRICE_SELECTOR,
               $this->module->l('Product price query selector', 'GetContentHookController'),
               sprintf(
                // PrestaShop won't detect the string if the call to `l` is multiline
                // phpcs:ignore
                $this->module->l('%1$sAdvanced%2$s Query selector for our scripts to correctly find the displayed price of a product', 'GetContentHookController'),
                   '<b>',
                   '</b>'
               )
           ),
           $this->inputTextForm(
               self::ALMA_PRODUCT_ATTR_SELECTOR,
               $this->module->l('Product attribute dropdown query selector', 'GetContentHookController'),
               sprintf(
                // PrestaShop won't detect the string if the call to `l` is multiline
                // phpcs:ignore
                $this->module->l('%1$sAdvanced%2$s Query selector for our scripts to correctly find the selected attributes of a product combination', 'GetContentHookController'),
                   '<b>',
                   '</b>'
               )
           ),
           $this->inputTextForm(
               self::ALMA_PRODUCT_ATTR_RADIO_SELECTOR,
               $this->module->l('Product attribute radio button query selector', 'GetContentHookController'),
               sprintf(
                // PrestaShop won't detect the string if the call to `l` is multiline
                // phpcs:ignore
                $this->module->l('%1$sAdvanced%2$s Query selector for our scripts to correctly find the selected attributes of a product combination', 'GetContentHookController'),
                   '<b>',
                   '</b>'
               )
           ),
           $this->inputTextForm(
               self::ALMA_PRODUCT_COLOR_PICK_SELECTOR,
               $this->module->l('Product color picker query selector', 'GetContentHookController'),
               sprintf(
                // PrestaShop won't detect the string if the call to `l` is multiline
                // phpcs:ignore
                $this->module->l('%1$sAdvanced%2$s Query selector for our scripts to correctly find the chosen color option of a product', 'GetContentHookController'),
                   '<b>',
                   '</b>'
               )
           ),
           $this->inputTextForm(
               self::ALMA_PRODUCT_QUANTITY_SELECTOR,
               $this->module->l('Product quantity query selector', 'GetContentHookController'),
               sprintf(
                // PrestaShop won't detect the string if the call to `l` is multiline
                // phpcs:ignore
                $this->module->l('%1$sAdvanced%2$s Query selector for our scripts to correctly find the wanted quantity of a product', 'GetContentHookController'),
                   '<b>',
                   '</b>'
               )
           ),
        ];
    }

    protected function getTitle()
    {
        return $this->module->l('Eligibility on product pages', 'GetContentHookController');
    }
}
