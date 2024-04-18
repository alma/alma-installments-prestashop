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

use Alma\PrestaShop\Helpers\AddressHelper;
use Alma\PrestaShop\Helpers\ApiHelper;
use Alma\PrestaShop\Helpers\CarrierHelper;
use Alma\PrestaShop\Helpers\CartHelper;
use Alma\PrestaShop\Helpers\ClientHelper;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\ContextHelper;
use Alma\PrestaShop\Helpers\CountryHelper;
use Alma\PrestaShop\Helpers\CurrencyHelper;
use Alma\PrestaShop\Helpers\CustomerHelper;
use Alma\PrestaShop\Helpers\CustomFieldsHelper;
use Alma\PrestaShop\Helpers\DateHelper;
use Alma\PrestaShop\Helpers\EligibilityHelper;
use Alma\PrestaShop\Helpers\LanguageHelper;
use Alma\PrestaShop\Helpers\LocaleHelper;
use Alma\PrestaShop\Helpers\MediaHelper;
use Alma\PrestaShop\Helpers\OrderHelper;
use Alma\PrestaShop\Helpers\OrderStateHelper;
use Alma\PrestaShop\Helpers\PaymentOptionHelper;
use Alma\PrestaShop\Helpers\PaymentOptionTemplateHelper;
use Alma\PrestaShop\Helpers\PlanHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\ProductHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ShopHelper;
use Alma\PrestaShop\Helpers\StateHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use Alma\PrestaShop\Helpers\TranslationHelper;
use Alma\PrestaShop\Helpers\ValidateHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Model\CarrierData;
use Alma\PrestaShop\Model\CartData;
use Alma\PrestaShop\Repositories\OrderRepository;
use Alma\PrestaShop\Model\PaymentData;
use Alma\PrestaShop\Model\ShippingData;
use Alma\PrestaShop\Repositories\ProductRepository;
use Alma\PrestaShop\Services\PaymentService;
use PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException;

class PaymentOptionsHookController extends FrontendHookController
{
    /**
     * @var PaymentService
     */
    protected $paymentService;

    /**
     * @codeCoverageIgnore
     *
     * @param $module
     */
    public function __construct($module)
    {
        parent::__construct($module);

        $context = $this->context;
        $languageHelper = new LanguageHelper();
        $configurationHelper = new ConfigurationHelper();
        $settingsHelper = new SettingsHelper(new ShopHelper(), $configurationHelper);
        $localeHelper = new LocaleHelper($languageHelper);
        $toolsHelper = new ToolsHelper();
        $priceHelper = new PriceHelper($toolsHelper, new CurrencyHelper());
        $clientHelper = new ClientHelper();

        $dateHelper = new DateHelper();
        $customFieldsHelper = new CustomFieldsHelper($languageHelper, $localeHelper, $settingsHelper);
        $cartData = new CartData(
            new ProductHelper(),
            $settingsHelper,
            $priceHelper,
            new ProductRepository()
        );
        $contextHelper = new ContextHelper();
        $mediaHelper = new MediaHelper();
        $translationHelper = new TranslationHelper($module);
        $planHelper = new PlanHelper(
            $module,
            $context,
            $dateHelper,
            $settingsHelper,
            $customFieldsHelper,
            $translationHelper
        );

        $carrierHelper = new CarrierHelper($this->context, new CarrierData());

        $cartHelper = new CartHelper(
            $context,
            $toolsHelper,
            $priceHelper,
            $cartData,
            new OrderRepository(),
            new OrderStateHelper($context),
            $carrierHelper
        );

        $eligibilityHelper = new EligibilityHelper(
            new PaymentData(
                $toolsHelper,
                $settingsHelper,
                $priceHelper,
                $customFieldsHelper,
                $cartData,
                new ShippingData($priceHelper, $carrierHelper),
                $this->context,
                new AddressHelper($toolsHelper),
                new CountryHelper(),
                $localeHelper,
                new StateHelper(),
                new CustomerHelper($context, new OrderHelper(), new ValidateHelper()),
                $cartHelper,
                $carrierHelper
            ),
            $priceHelper,
            $clientHelper,
            $settingsHelper,
            new ApiHelper($this->module, $clientHelper),
            $context
        );

        $paymentOptionTemplateHelper = new PaymentOptionTemplateHelper(
            $context,
            $module,
            $settingsHelper,
            $configurationHelper,
            $translationHelper,
            $priceHelper,
            $dateHelper
        );

        $paymentOptionHelper = new PaymentOptionHelper(
            $context,
            $module,
            $settingsHelper,
            $customFieldsHelper,
            $mediaHelper,
            $configurationHelper,
            $paymentOptionTemplateHelper
        );

        $this->paymentService = new PaymentService(
            $context,
            $module,
            $settingsHelper,
            $localeHelper,
            $toolsHelper,
            $eligibilityHelper,
            $priceHelper,
            $dateHelper,
            $customFieldsHelper,
            $cartData,
            $contextHelper,
            $mediaHelper,
            $planHelper,
            $configurationHelper,
            $translationHelper,
            $cartHelper,
            $paymentOptionTemplateHelper,
            $paymentOptionHelper
        );
    }

    /**
     * Payment option for Hook PaymentOption (Prestashop 1.7).
     *
     * @param array $params
     *
     * @return array
     *
     * @throws LocalizationException
     * @throws \SmartyException
     */
    public function run($params)
    {
        return $this->paymentService->createPaymentOptions($params);
    }
}
