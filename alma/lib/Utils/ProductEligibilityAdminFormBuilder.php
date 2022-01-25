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
        $htmlContent = sprintf(
            // phpcs:ignore
            $this->module->l('This widget allows you to inform your customers of the availability of Alma\'s payment facilities right from the product page, which will help to increase your conversion rate. For more details on its configuration or in case of problems, please consult %1$sthis documentation%2$s.', 'ProductEligibilityAdminFormBuilder'),
            '<a href="https://docs.getalma.eu/docs/prestashop-alma-widget" target="_blank">',
            '</a>'
        );
        $tpl = $this->context->smarty->createTemplate(
            "{$this->module->local_path}views/templates/hook/sample_widget.tpl"
        );
        return [
           $this->inputHtml($tpl, $htmlContent),
           $this->inputAlmaSwitchForm(
               self::ALMA_SHOW_PRODUCT_ELIGIBILITY,
               $this->module->l('Display widget', 'ProductEligibilityAdminFormBuilder')
           ),
           $this->inputAlmaSwitchForm(
               self::ALMA_PRODUCT_WDGT_NOT_ELGBL,
               $this->module->l('Display even if the product is not eligible', 'ProductEligibilityAdminFormBuilder')
           ),
           $this->inputRadioForm(
               self::ALMA_WIDGET_POSITION_CUSTOM,
               $this->module->l('Badge position', 'ProductEligibilityAdminFormBuilder'),
               $this->module->l('Display badge after price (by default)', 'ProductEligibilityAdminFormBuilder'),
               $this->module->l('Display badge on custom css selector', 'ProductEligibilityAdminFormBuilder')
           ),
           $this->inputTextForm(
               self::ALMA_WIDGET_POSITION_SELECTOR,
               $this->module->l('Display badge on custom css selector', 'ProductEligibilityAdminFormBuilder'),
               sprintf(
                // PrestaShop won't detect the string if the call to `l` is multiline
                // phpcs:ignore
                $this->module->l('%1$sAdvanced%2$s [Optional] Query selector for our scripts to display the badge on product page', 'ProductEligibilityAdminFormBuilder'),
                   '<b>',
                   '</b>'
               ),
               $this->module->l('E.g. #id, .class, ...', 'ProductEligibilityAdminFormBuilder')
           ),
           $this->inputTextForm(
               self::ALMA_PRODUCT_PRICE_SELECTOR,
               $this->module->l('Product price query selector', 'ProductEligibilityAdminFormBuilder'),
               sprintf(
                // PrestaShop won't detect the string if the call to `l` is multiline
                // phpcs:ignore
                $this->module->l('%1$sAdvanced%2$s Query selector for our scripts to correctly find the displayed price of a product', 'ProductEligibilityAdminFormBuilder'),
                   '<b>',
                   '</b>'
               )
           ),
           $this->inputTextForm(
               self::ALMA_PRODUCT_ATTR_SELECTOR,
               $this->module->l('Product attribute dropdown query selector', 'ProductEligibilityAdminFormBuilder'),
               sprintf(
                // PrestaShop won't detect the string if the call to `l` is multiline
                // phpcs:ignore
                $this->module->l('%1$sAdvanced%2$s Query selector for our scripts to correctly find the selected attributes of a product combination', 'ProductEligibilityAdminFormBuilder'),
                   '<b>',
                   '</b>'
               )
           ),
           $this->inputTextForm(
               self::ALMA_PRODUCT_ATTR_RADIO_SELECTOR,
               $this->module->l('Product attribute radio button query selector', 'ProductEligibilityAdminFormBuilder'),
               sprintf(
                // PrestaShop won't detect the string if the call to `l` is multiline
                // phpcs:ignore
                $this->module->l('%1$sAdvanced%2$s Query selector for our scripts to correctly find the selected attributes of a product combination', 'ProductEligibilityAdminFormBuilder'),
                   '<b>',
                   '</b>'
               )
           ),
           $this->inputTextForm(
               self::ALMA_PRODUCT_COLOR_PICK_SELECTOR,
               $this->module->l('Product color picker query selector', 'ProductEligibilityAdminFormBuilder'),
               sprintf(
                // PrestaShop won't detect the string if the call to `l` is multiline
                // phpcs:ignore
                $this->module->l('%1$sAdvanced%2$s Query selector for our scripts to correctly find the chosen color option of a product', 'ProductEligibilityAdminFormBuilder'),
                   '<b>',
                   '</b>'
               )
           ),
           $this->inputTextForm(
               self::ALMA_PRODUCT_QUANTITY_SELECTOR,
               $this->module->l('Product quantity query selector', 'ProductEligibilityAdminFormBuilder'),
               sprintf(
                // PrestaShop won't detect the string if the call to `l` is multiline
                // phpcs:ignore
                $this->module->l('%1$sAdvanced%2$s Query selector for our scripts to correctly find the wanted quantity of a product', 'ProductEligibilityAdminFormBuilder'),
                   '<b>',
                   '</b>'
               )
           ),
        ];
    }

    protected function getTitle()
    {
        return $this->module->l('Display widget on product page', 'ProductEligibilityAdminFormBuilder');
    }
}
