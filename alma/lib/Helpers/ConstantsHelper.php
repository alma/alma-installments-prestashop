<?php
/**
 * 2018-2023 Alma SAS.
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

namespace Alma\PrestaShop\Helpers;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ConstantsHelper
{
    const ALMA_MODULE_NAME = 'alma';
    const INPAGE_SCRIPT_PATH = 'views/js/alma-inpage.js';
    const INPAGE_JS_URL = 'https://cdn.jsdelivr.net/npm/@alma/in-page@2.x.x/dist/index.umd.js';
    const WIDGETS_CSS_URL = 'https://cdn.jsdelivr.net/npm/@alma/widgets@3.x.x/dist/widgets.min.css';
    const WIDGETS_JS_URL = 'https://cdn.jsdelivr.net/npm/@alma/widgets@3.x.x/dist/widgets.umd.js';
    const PRODUCT_SCRIPT_PATH = 'views/js/alma-product.js';
    const PRODUCT_CSS_PATH = 'views/css/alma-product.css';
    const CART_SCRIPT_PATH = 'views/js/alma-cart.js';
    const INPAGE_JS_ID = 'alma-remote-inpage-js';
    const INPAGE_SCRIPT_ID = 'alma-inpage-script';
    const WIDGETS_JS_ID = 'alma-remote-widgets-js';
    const WIDGETS_CSS_ID = 'alma-remote-widgets-css';
    const PRODUCT_CSS_ID = 'alma-product-css';
    const PRODUCT_SCRIPT_ID = 'alma-product-script';
    const CART_SCRIPT_ID = 'alma-cart-script';
    const PRESTASHOP_VERSION_1_7_0_2 = '1.7.0.2';

    const OBSCURE_VALUE = '********************************';
    const BEGIN_LIVE_API_KEY = 'sk_live_';
    const BEGIN_TEST_API_KEY = 'sk_test_';

    const SOURCE_CUSTOM_FIELDS = 'CustomFieldsHelper';

    const ALMA_KEY_PAYNOW = 'general_1_0_0';

    const ALMA_ALLOW_INPAGE = 'ALMA_ALLOW_INPAGE';

    const ALMA_ALLOW_INSURANCE = 'ALMA_ALLOW_INSURANCE';

    const ALMA_ACTIVATE_INSURANCE = 'ALMA_ACTIVATE_INSURANCE';
    const ALMA_SHOW_INSURANCE_WIDGET_PRODUCT = 'ALMA_SHOW_INSURANCE_WIDGET_PRODUCT';
    const ALMA_SHOW_INSURANCE_WIDGET_CART = 'ALMA_SHOW_INSURANCE_WIDGET_CART';
    const ALMA_SHOW_INSURANCE_POPUP_CART = 'ALMA_SHOW_INSURANCE_POPUP_CART';

    const BO_CONTROLLER_INSURANCE_CLASSNAME = 'AdminAlmaInsurance';
    const BO_URL_IFRAME_CONFIGURATION_INSURANCE = 'https://poc-iframe.dev.almapay.com/almaBackOfficeConfiguration.html';

    /**
     * Insurance form fields
     *
     * @var string[]
     */
    public static $fieldsBoInsurance = [
        self::ALMA_ACTIVATE_INSURANCE,
        self::ALMA_SHOW_INSURANCE_WIDGET_PRODUCT,
        self::ALMA_SHOW_INSURANCE_WIDGET_CART,
        self::ALMA_SHOW_INSURANCE_POPUP_CART,
    ];
}
