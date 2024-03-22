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

namespace Alma\PrestaShop\Controllers\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\Forms\ExcludedCategoryAdminFormBuilder;
use Alma\PrestaShop\Helpers\AddressHelper;
use Alma\PrestaShop\Helpers\ApiHelper;
use Alma\PrestaShop\Helpers\CarrierHelper;
use Alma\PrestaShop\Helpers\CartHelper;
use Alma\PrestaShop\Helpers\ClientHelper;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\CountryHelper;
use Alma\PrestaShop\Helpers\CurrencyHelper;
use Alma\PrestaShop\Helpers\CustomerHelper;
use Alma\PrestaShop\Helpers\CustomFieldsHelper;
use Alma\PrestaShop\Helpers\EligibilityHelper;
use Alma\PrestaShop\Helpers\LanguageHelper;
use Alma\PrestaShop\Helpers\LocaleHelper;
use Alma\PrestaShop\Helpers\OrderHelper;
use Alma\PrestaShop\Helpers\OrderStateHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\ProductHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ShopHelper;
use Alma\PrestaShop\Helpers\StateHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use Alma\PrestaShop\Helpers\ValidateHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Model\CarrierData;
use Alma\PrestaShop\Model\CartData;
use Alma\PrestaShop\Model\OrderData;
use Alma\PrestaShop\Model\PaymentData;
use Alma\PrestaShop\Model\ShippingData;
use Alma\PrestaShop\Repositories\ProductRepository;

class DisplayShoppingCartFooterHookController extends FrontendHookController
{
    /**
     * @var LocaleHelper
     */
    protected $localeHelper;

    /**
     * @var EligibilityHelper
     */
    protected $eligibilityHelper;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var CartData
     */
    protected $cartData;

    /**
     * @var CustomFieldsHelper
     */
    protected $customFieldsHelper;

    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    /**
     * HookController constructor.
     *
     * @param $module Alma
     */
    public function __construct($module)
    {
        parent::__construct($module);

        $this->localeHelper = new LocaleHelper(new LanguageHelper());
        $toolsHelper = new ToolsHelper();
        $this->priceHelper = new PriceHelper($toolsHelper, new CurrencyHelper());
        $this->settingsHelper = new SettingsHelper(new ShopHelper(), new ConfigurationHelper());
        $clientHelper = new ClientHelper();
        $this->customFieldsHelper = new CustomFieldsHelper(
            new LanguageHelper(),
            $this->localeHelper,
            $this->settingsHelper
        );
        $this->cartData = new CartData(
            new ProductHelper(),
            $this->settingsHelper,
            $this->priceHelper,
            new ProductRepository()
        );
        $carrierHelper = new CarrierHelper($this->context, new CarrierData());

        $this->eligibilityHelper = new EligibilityHelper(
            new PaymentData(
                $toolsHelper,
                $this->settingsHelper,
                $this->priceHelper,
                $this->customFieldsHelper,
                $this->cartData,
                new ShippingData($this->priceHelper, $carrierHelper),
                $this->context,
                new AddressHelper($toolsHelper),
                new CountryHelper(),
                $this->localeHelper,
                new StateHelper(),
                new CustomerHelper($this->context, new OrderHelper(), new ValidateHelper()),
                new CartHelper(
                    $this->context,
                    $toolsHelper,
                    $this->priceHelper,
                    $this->cartData,
                    new OrderData(),
                    new OrderStateHelper($this->context),
                    $carrierHelper
                ),
                $carrierHelper
            ),
            $this->priceHelper,
            $clientHelper,
            $this->settingsHelper,
            new ApiHelper($clientHelper),
            $this->context
        );
    }

    public function canRun()
    {
        return parent::canRun() && SettingsHelper::showEligibilityMessage();
    }

    public function run($params)
    {
        $eligibilityMsg = null;

        $activePlans = $this->settingsHelper->activePlans();

        $locale = $this->localeHelper->getLocaleByIdLangForWidget($this->context->language->id);

        if (!$activePlans) {
            return;
        }

        $cart = $this->context->cart;
        $cartTotal = $this->priceHelper->convertPriceToCents((float) $cart->getOrderTotal(true, \Cart::BOTH));

        $isEligible = true;
        if (!SettingsHelper::showCartWidgetIfNotEligible()) {
            $installmentPlans = $this->eligibilityHelper->eligibilityCheck();
            $isEligible = false;
            foreach ($installmentPlans as $plan) {
                if ($plan->installmentsCount !== 1 && $plan->isEligible) {
                    $isEligible = true;
                    break;
                }
            }
        }

        // Check if some products in cart are in the excludes listing
        $isExcluded = false;
        $diff = $this->cartData->getCartExclusion($params['cart']);
        if (!empty($diff)) {
            $eligibilityMsg = $this->customFieldsHelper->getBtnValueByLang(
                $this->context->language->id,
                ExcludedCategoryAdminFormBuilder::ALMA_NOT_ELIGIBLE_CATEGORIES
            );
            $isExcluded = true;
            if (!SettingsHelper::showCategoriesWidgetIfNotEligible()) {
                $isEligible = false;
            }
        }

        if (is_callable('\Media::getMediaPath')) {
            $logo = \Media::getMediaPath(_PS_MODULE_DIR_ . $this->module->name . '/views/img/logos/logo_alma.svg');
        } else {
            $logo = $this->module->getPathUri() . '/views/img/logos/logo_alma.svg';
        }

        // need ps verions && refresh price
        $psVersion = '1.7';
        $refreshPrice = true;
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $psVersion = '1.6';
            $refreshPrice = false;
            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                $psVersion = '1.5';
            }
        }

        if ($isEligible) {
            $this->context->smarty->assign([
                'eligibility_msg' => $eligibilityMsg,
                'logo' => $logo,
                'isExcluded' => $isExcluded,
                'settings' => [
                    'merchantId' => SettingsHelper::getMerchantId(),
                    'apiMode' => SettingsHelper::getActiveMode(),
                    'amount' => $cartTotal,
                    'plans' => $activePlans,
                    'refreshPrice' => $refreshPrice,
                    'decimalSeparator' => LocaleHelper::decimalSeparator(),
                    'thousandSeparator' => LocaleHelper::thousandSeparator(),
                    'psVersion' => $psVersion,
                    'showIfNotEligible' => SettingsHelper::showCartWidgetIfNotEligible(),
                    'locale' => $locale,
                ],
                'widgetQuerySelectors' => json_encode([
                    'price' => SettingsHelper::getProductPriceQuerySelector(),
                    'attrSelect' => SettingsHelper::getProductAttrQuerySelector(),
                    'attrRadio' => SettingsHelper::getProductAttrRadioQuerySelector(),
                    'colorPick' => SettingsHelper::getProductColorPickQuerySelector(),
                    'quantity' => SettingsHelper::getProductQuantityQuerySelector(),
                    'isCartCustom' => SettingsHelper::isCartWidgetCustomPosition(),
                    'cartPosition' => SettingsHelper::getCartWidgetPositionQuerySelector(),
                    ]),
            ]);

            return $this->module->display($this->module->file, 'displayShoppingCartFooter.tpl');
        }
    }
}
