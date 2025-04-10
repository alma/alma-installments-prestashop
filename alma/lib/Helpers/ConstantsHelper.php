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

namespace Alma\PrestaShop\Helpers;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ConstantsHelper
{
    const ALMA_MODULE_NAME = 'alma';
    const INPAGE_SCRIPT_PATH = 'views/js/alma-inpage.js';
    const INPAGE_JS_URL = 'https://cdn.jsdelivr.net/npm/@alma/in-page@2.x.x/dist/index.umd.js';
    const WIDGETS_CSS_URL = 'https://cdn.jsdelivr.net/npm/@alma/widgets@4.x.x/dist/widgets.min.css';
    const WIDGETS_JS_URL = 'https://cdn.jsdelivr.net/npm/@alma/widgets@4.x.x/dist/widgets.umd.js';
    const PRODUCT_SCRIPT_PATH = 'views/js/alma-product.js';
    const PRODUCT_INSURANCE_SCRIPT_PATH = 'views/js/alma-product-insurance.js';
    const ORDER_INSURANCE_SCRIPT_PATH = 'views/js/alma-order-insurance.js';
    const INSURANCE_SCRIPT_PATH = 'views/js/alma-insurance.js';
    const PRODUCT_INSURANCE_16_SCRIPT_PATH = 'views/js/alma-product-insurance16.js';
    const INSURANCE_16_SCRIPT_PATH = 'views/js/alma-insurance16.js';
    const ORDER_INSURANCE_16_SCRIPT_PATH = 'views/js/order-alma-insurance16.js';
    const PRODUCT_CSS_PATH = 'views/css/alma-product.css';
    const INSURANCE_PRODUCT_CSS_PATH = 'views/css/alma-insurance.css';
    const CART_SCRIPT_PATH = 'views/js/alma-cart.js';

    const CART_INSURANCE_SCRIPT_PATH = 'views/js/alma-cart-insurance.js';
    const CART_INSURANCE_16_SCRIPT_PATH = 'views/js/alma-cart-insurance16.js';
    const MINI_CART_INSURANCE_16_SCRIPT_PATH = 'views/js/alma-mini-cart-insurance16.js';
    const INPAGE_JS_ID = 'alma-remote-inpage-js';
    const INPAGE_SCRIPT_ID = 'alma-inpage-script';
    const WIDGETS_JS_ID = 'alma-remote-widgets-js';
    const WIDGETS_CSS_ID = 'alma-remote-widgets-css';
    const PRODUCT_CSS_ID = 'alma-product-css';
    const INSURANCE_PRODUCT_CSS_ID = 'alma-product-insurance-css';
    const PRODUCT_SCRIPT_ID = 'alma-product-script';
    const CART_SCRIPT_ID = 'alma-cart-script';
    const PRESTASHOP_VERSION_1_7_0_2 = '1.7.0.2';

    const OBSCURE_VALUE = '********************************';
    const BEGIN_LIVE_API_KEY = 'sk_live_';
    const BEGIN_TEST_API_KEY = 'sk_test_';

    const SOURCE_CUSTOM_FIELDS = 'CustomFieldsHelper';

    const ALMA_KEY_PAYNOW = 'general_1_0_0';

    const ALMA_ALLOW_INSURANCE = 'ALMA_ALLOW_INSURANCE';

    const ALMA_ACTIVATE_INSURANCE = 'ALMA_ACTIVATE_INSURANCE';
    const ALMA_SHOW_INSURANCE_WIDGET_PRODUCT = 'ALMA_SHOW_INSURANCE_WIDGET_PRODUCT';
    const ALMA_SHOW_INSURANCE_WIDGET_CART = 'ALMA_SHOW_INSURANCE_WIDGET_CART';
    const ALMA_SHOW_INSURANCE_POPUP_CART = 'ALMA_SHOW_INSURANCE_POPUP_CART';

    const BO_CONTROLLER_INSURANCE_CLASSNAME = 'AdminAlmaInsurance';
    const BO_CONTROLLER_INSURANCE_CONFIGURATION_CLASSNAME = 'AdminAlmaInsuranceConfiguration';
    const BO_CONTROLLER_INSURANCE_ORDERS_DETAILS_CLASSNAME = 'AdminAlmaInsuranceOrdersDetails';
    const BO_CONTROLLER_INSURANCE_ORDERS_CLASSNAME = 'AdminAlmaInsuranceOrders';
    const BO_CONTROLLER_INSURANCE_ORDERS_LIST_CLASSNAME = 'AdminAlmaInsuranceOrdersList';
    const DOMAIN_URL_INSURANCE_TEST = 'https://protect.sandbox.almapay.com';
    const DOMAIN_URL_INSURANCE_LIVE = 'https://protect.almapay.com';
    const BO_IFRAME_CONFIGURATION_INSURANCE_PATH = '/almaBackOfficeConfiguration.html';
    const BO_IFRAME_SUBSCRIPTION_INSURANCE_PATH = '/almaBackOfficeSubscriptions.html';
    const FO_IFRAME_WIDGET_INSURANCE_PATH = '/almaProductInPageWidget.html';
    const SCRIPT_MODAL_WIDGET_INSURANCE_PATH = '/displayModal.js';

    const ALMA_INSURANCE_PRODUCT_REFERENCE = 'alma-insurance';
    const ALMA_INSURANCE_ATTRIBUTE_NAME = 'Alma Insurance (DO NOT REMOVE)';
    const ALMA_INSURANCE_ATTRIBUTE_PUBLIC_NAME = 'My Insurance';

    const ALMA_INSURANCE_PRODUCT_IMAGE_URL = _PS_MODULE_DIR_ . self::ALMA_MODULE_NAME . '/views/img/alma-insurance.png';

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
